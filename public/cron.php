<?php
/**
 * SAIP Background Worker
 * Add to crontab: * * * * * php /path/to/public/cron.php?key=YOUR_SECRET_KEY
 */

require_once '../config/config.php';

// Secret key from env (set in .env or config)
$secret = getenv('CRON_SECRET') ?: 'saip_cron_secret_change_me';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

require_once '../app/Core/Database.php';
require_once '../app/Models/EmailQueue.php';
require_once '../app/Models/SmsQueue.php';
require_once '../app/Models/Notification.php';

$result = [
    'time'    => date('Y-m-d H:i:s'),
    'emails'  => 0,
    'sms'     => 0,
    'reports' => 0,
];

try {
    // 1. Process email queue
    $emailQueue = new \App\Models\EmailQueue();
    $result['emails'] = $emailQueue->process(100);

    // 2. Process SMS queue
    $sms = new \App\Models\SmsQueue();
    $result['sms'] = $sms->process(100);

    // 3. Run scheduled reports
    $db = new \App\Core\Database();
    $db->query("SELECT sr.*, rt.name AS template_name FROM scheduled_reports sr
                JOIN report_templates rt ON rt.id = sr.template_id
                WHERE sr.is_active = 1 
                AND (sr.next_run_at IS NULL OR sr.next_run_at <= NOW())");
    $schedules = $db->resultSet();

    foreach ($schedules as $s) {
        // Mark run started
        $db->query("INSERT INTO scheduled_report_runs (schedule_id, run_at, status, recipients)
                    VALUES (:s, NOW(), 'running', :r)");
        $db->bind(':s', $s['id']);
        $db->bind(':r', json_encode($s['recipients']));
        $db->execute();

        // ... generate report here (reuse ReportGenerator)
        // ... email to recipients

        // Compute next run
        $freq = $s['frequency'];
        $next = match($freq) {
            'daily'     => date('Y-m-d H:i:s', strtotime('+1 day')),
            'weekly'    => date('Y-m-d H:i:s', strtotime('+1 week')),
            'monthly'   => date('Y-m-d H:i:s', strtotime('+1 month')),
            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
            default     => date('Y-m-d H:i:s', strtotime('+1 week')),
        };

        $db->query("UPDATE scheduled_reports SET last_run_at = NOW(), next_run_at = :n WHERE id = :id");
        $db->bind(':n', $next);
        $db->bind(':id', $s['id']);
        $db->execute();

        // Mark run complete
        $db->query("UPDATE scheduled_report_runs SET status='success', completed_at=NOW() WHERE schedule_id = :s ORDER BY id DESC LIMIT 1");
        $db->bind(':s', $s['id']);
        $db->execute();

        $result['reports']++;
    }
} catch (\Throwable $e) {
    $result['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($result);