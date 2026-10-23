<?php
namespace App\Models;

use App\Core\Database;

class SmsQueue {
    private $db;

    public function __construct() { $this->db = new Database(); }

    public function send($phone, $message, $userId = null, $provider = 'mtn') {
        $this->db->query("INSERT INTO sms_queue (phone, message, user_id, provider)
                          VALUES (:p, :m, :u, :pr)");
        $this->db->bind(':p', $phone);
        $this->db->bind(':m', $message);
        $this->db->bind(':u', $userId);
        $this->db->bind(':pr', $provider);
        return $this->db->execute();
    }

    /** Process pending SMS via Africa's Talking (or a mock). */
    public function process($limit = 50) {
        $this->db->query("SELECT * FROM sms_queue WHERE status='pending' ORDER BY id LIMIT :n");
        $this->db->bind(':n', $limit);
        $rows = $this->db->resultSet();

        foreach ($rows as $sms) {
            // Replace with real provider call
            $ok = $this->deliverViaProvider($sms['phone'], $sms['message'], $sms['provider']);

            $this->db->query("UPDATE sms_queue SET status=:s, attempts=attempts+1, last_attempt_at=NOW() WHERE id=:id");
            $this->db->bind(':s', $ok ? 'sent' : 'failed');
            $this->db->bind(':id', $sms['id']);
            $this->db->execute();
        }
        return count($rows);
    }

    private function deliverViaProvider($phone, $message, $provider) {
        // Example: Africa's Talking API (uncomment + configure in production)
        /*
        $apiKey = getenv('AT_API_KEY');
        $username = getenv('AT_USERNAME');
        $url = 'https://api.africastalking.com/version1/messaging';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['apiKey: ' . $apiKey, 'Accept: application/json'],
            CURLOPT_POSTFIELDS => http_build_query([
                'username' => $username,
                'to' => $phone,
                'message' => $message,
                'from' => 'SAIP',
            ]),
        ]);
        $res = curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_HTTP_CODE) === 201;
        */

        // Mock: log to error_log
        error_log("[SAIP SMS → $phone] $message");
        return true;
    }
}
?>