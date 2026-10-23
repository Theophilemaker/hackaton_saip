<?php
namespace App\Models;

use App\Core\Database;

class Notification {
    private $db;

    public function __construct() { $this->db = new Database(); }

    /**
     * Create a notification for a user.
     */
    public function notify($userId, $type, $title, $body, $priority = 'green', $link = null, $icon = '🔔') {
        $this->db->query("INSERT INTO notifications
            (user_id, type, priority, title, body, link, icon)
            VALUES (:u,:t,:p,:ti,:b,:l,:i)");
        $this->db->bind(':u', $userId);
        $this->db->bind(':t', $type);
        $this->db->bind(':p', $priority);
        $this->db->bind(':ti', $title);
        $this->db->bind(':b', $body);
        $this->db->bind(':l', $link);
        $this->db->bind(':i', $icon);
        return $this->db->lastInsertId();
    }

    /**
     * Broadcast to all users by role.
     */
    public function broadcast($roleId, $type, $title, $body, $priority = 'green', $link = null, $icon = '🔔') {
        $this->db->query("INSERT INTO notifications (user_id, type, priority, title, body, link, icon)
                          SELECT id, :t, :p, :ti, :b, :l, :i FROM users WHERE role_id=:r AND status='active'");
        $this->db->bind(':t', $type);
        $this->db->bind(':p', $priority);
        $this->db->bind(':ti', $title);
        $this->db->bind(':b', $body);
        $this->db->bind(':l', $link);
        $this->db->bind(':i', $icon);
        $this->db->bind(':r', $roleId);
        return $this->db->execute();
    }

    public function getUnread($userId, $limit = 20) {
        $this->db->query("SELECT * FROM notifications
                          WHERE user_id=:u AND is_read=0
                          ORDER BY FIELD(priority,'red','yellow','green'), created_at DESC
                          LIMIT :n");
        $this->db->bind(':u', $userId);
        $this->db->bind(':n', $limit);
        return $this->db->resultSet();
    }

    public function getAll($userId, $limit = 50) {
        $this->db->query("SELECT * FROM notifications WHERE user_id=:u
                          ORDER BY created_at DESC LIMIT :n");
        $this->db->bind(':u', $userId);
        $this->db->bind(':n', $limit);
        return $this->db->resultSet();
    }

    public function countUnread($userId) {
        $this->db->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id=:u AND is_read=0");
        $this->db->bind(':u', $userId);
        return (int)$this->db->single()['c'];
    }

    public function markRead($id, $userId) {
        $this->db->query("UPDATE notifications SET is_read=1, read_at=NOW()
                          WHERE id=:id AND user_id=:u");
        $this->db->bind(':id', $id);
        $this->db->bind(':u', $userId);
        return $this->db->execute();
    }

    public function markAllRead($userId) {
        $this->db->query("UPDATE notifications SET is_read=1, read_at=NOW()
                          WHERE user_id=:u AND is_read=0");
        $this->db->bind(':u', $userId);
        return $this->db->execute();
    }

    /**
     * Queue an SMS (asynchronous — a cron will send it).
     */
    public function queueSMS($phone, $message, $userId = null, $provider = 'mtn') {
        $this->db->query("INSERT INTO sms_queue (phone, message, user_id, provider)
                          VALUES (:p,:m,:u,:pr)");
        $this->db->bind(':p', $phone);
        $this->db->bind(':m', $message);
        $this->db->bind(':u', $userId);
        $this->db->bind(':pr', $provider);
        return $this->db->execute();
    }

    /**
     * Send notification + optional SMS.
     */
    public function sendFull($userId, $phone, $type, $title, $body, $priority = 'green', $link = null, $icon = '🔔', $sendSms = false) {
        $id = $this->notify($userId, $type, $title, $body, $priority, $link, $icon);
        if ($sendSms && $phone) {
            $this->queueSMS($phone, $title . ': ' . $body, $userId);
        }
        return $id;
    }
}
?>