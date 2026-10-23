<?php
namespace App\Models;

use App\Core\Database;

class EmailQueue {
    private $db;

    public function __construct() { $this->db = new Database(); }

    public function send($to, $subject, $body, $userId = null, $html = 1) {
        $this->db->query("INSERT INTO email_queue (to_email, subject, body, html, user_id)
                          VALUES (:t, :s, :b, :h, :u)");
        $this->db->bind(':t', $to);
        $this->db->bind(':s', $subject);
        $this->db->bind(':b', $body);
        $this->db->bind(':h', $html);
        $this->db->bind(':u', $userId);
        return $this->db->execute();
    }

    /** Process pending emails (cron). */
    public function process($limit = 50) {
        $this->db->query("SELECT * FROM email_queue 
                          WHERE status='pending' AND scheduled_at <= NOW()
                          ORDER BY id LIMIT :n");
        $this->db->bind(':n', $limit);
        $emails = $this->db->resultSet();

        foreach ($emails as $e) {
            $ok = $this->deliver($e['to_email'], $e['subject'], $e['body'], (bool)$e['html']);
            $this->db->query("UPDATE email_queue SET status=:s, attempts=attempts+1, sent_at=" . 
                ($ok ? "NOW()" : "sent_at") . " WHERE id=:id");
            $this->db->bind(':s', $ok ? 'sent' : 'failed');
            $this->db->bind(':id', $e['id']);
            $this->db->execute();
        }
        return count($emails);
    }

    private function deliver($to, $subject, $body, $html) {
        // Use PHP's mail() by default; swap for SMTP in production
        $headers = "From: SAIP <noreply@saip.rw>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . ($html ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";

        return @mail($to, $subject, $body, $headers);
    }
}
?>