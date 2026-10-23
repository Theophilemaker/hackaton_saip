<?php
namespace App\Models;

use App\Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /* ============================================================
     *  LOOKUP METHODS
     * ============================================================ */

    /**
     * Find a user by email — CASE-INSENSITIVE.
     * This is critical: MySQL's default `email = :email` compare can
     * miss `Admin@saip.rw` when the row is stored as `admin@saip.rw`.
     */
    public function findUserByEmail($email) {
        $email = strtolower(trim($email));
        $this->db->query("SELECT * FROM users WHERE LOWER(email) = :email LIMIT 1");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    /**
     * Find a user by primary key.
     */
    public function findUserById($id) {
        $this->db->query("SELECT * FROM users WHERE id = :id LIMIT 1");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Find a user by phone number.
     */
    public function findUserByPhone($phone) {
        $this->db->query("SELECT * FROM users WHERE phone = :phone LIMIT 1");
        $this->db->bind(':phone', $phone);
        return $this->db->single();
    }

    /**
     * Find a user by UUID.
     */
    public function findUserByUuid($uuid) {
        $this->db->query("SELECT * FROM users WHERE uuid = :uuid LIMIT 1");
        $this->db->bind(':uuid', $uuid);
        return $this->db->single();
    }

    /* ============================================================
     *  CREATE / UPDATE
     * ============================================================ */

    /**
     * Register a new user. Returns the new user ID or false.
     */
    public function register($data) {
        try {
            $this->db->query("INSERT INTO users
                (uuid, first_name, last_name, email, phone, password_hash, role_id, preferred_language, status, verification_status)
                VALUES (:uuid, :first_name, :last_name, :email, :phone, :password_hash, :role_id, :preferred_language, 'active', 'unverified')");

            $this->db->bind(':uuid', $data['uuid']);
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', strtolower($data['email']));
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':password_hash', $data['password_hash']);
            $this->db->bind(':role_id', $data['role_id']);
            $this->db->bind(':preferred_language', $data['preferred_language']);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\Throwable $e) {
            error_log('User::register failed — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query("UPDATE users SET password_hash = :h WHERE id = :id");
        $this->db->bind(':h', $hash);
        $this->db->bind(':id', (int)$userId);
        return $this->db->execute();
    }

    /**
     * Update basic profile fields.
     */
    public function updateProfile($userId, $data) {
        $fields = [];
        $binds  = [':id' => (int)$userId];

        foreach (['first_name', 'last_name', 'phone', 'preferred_language', 'gender', 'date_of_birth', 'district_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $binds[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $this->db->query("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id");
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->execute();
    }

    /* ============================================================
     *  VERIFICATION HELPERS
     * ============================================================ */

    /**
     * Check if an email is already registered (case-insensitive).
     */
    public function emailExists($email) {
        $email = strtolower(trim($email));
        $this->db->query("SELECT id FROM users WHERE LOWER(email) = :email LIMIT 1");
        $this->db->bind(':email', $email);
        return (bool)$this->db->single();
    }

    /**
     * Check if a phone number is already registered.
     */
    public function phoneExists($phone) {
        if (empty($phone)) return false;
        $this->db->query("SELECT id FROM users WHERE phone = :phone LIMIT 1");
        $this->db->bind(':phone', $phone);
        return (bool)$this->db->single();
    }

    /* ============================================================
     *  LIST HELPERS (Admin)
     * ============================================================ */

    /**
     * Get all users with role name and district name joined.
     */
    public function getAllWithRoles($limit = 200, $offset = 0) {
        $this->db->query("
            SELECT u.*, r.name AS role_name, d.name AS district_name
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN districts d ON d.id = u.district_id
            ORDER BY u.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $this->db->bind(':limit', (int)$limit);
        $this->db->bind(':offset', (int)$offset);
        return $this->db->resultSet();
    }

    /**
     * Get total user count.
     */
    public function count() {
        $this->db->query("SELECT COUNT(*) AS c FROM users");
        return (int)($this->db->single()['c'] ?? 0);
    }

    /**
     * Get total user count by role.
     */
    public function countByRole($roleId) {
        $this->db->query("SELECT COUNT(*) AS c FROM users WHERE role_id = :r");
        $this->db->bind(':r', (int)$roleId);
        return (int)($this->db->single()['c'] ?? 0);
    }

    /* ============================================================
     *  STATUS
     * ============================================================ */

    public function activate($userId) {
        $this->db->query("UPDATE users SET status = 'active' WHERE id = :id");
        $this->db->bind(':id', (int)$userId);
        return $this->db->execute();
    }

    public function suspend($userId) {
        $this->db->query("UPDATE users SET status = 'suspended' WHERE id = :id");
        $this->db->bind(':id', (int)$userId);
        return $this->db->execute();
    }
}
?>