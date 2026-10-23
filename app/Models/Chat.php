<?php
namespace App\Models;

use App\Core\Database;

class Chat {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /* ═══════════════════════════════════════════════════════════
       CONVERSATIONS
       ═══════════════════════════════════════════════════════════ */

    public function listForUser($userId) {
        try {
            $this->db->query("
                SELECT c.*,
                       (SELECT content FROM messages m
                          WHERE m.conversation_id = c.id AND m.deleted_at IS NULL
                          ORDER BY m.id DESC LIMIT 1) AS last_msg,
                       (SELECT message_type FROM messages m
                          WHERE m.conversation_id = c.id
                          ORDER BY m.id DESC LIMIT 1) AS last_type,
                       (SELECT sent_at FROM messages m
                          WHERE m.conversation_id = c.id
                          ORDER BY m.id DESC LIMIT 1) AS last_at,
                       (SELECT COUNT(*) FROM messages m
                          WHERE m.conversation_id = c.id
                            AND m.sent_at > COALESCE(cm.last_read_at, '1970-01-01')) AS unread_count,
                       cm.is_muted
                FROM conversations c
                JOIN conversation_members cm ON cm.conversation_id = c.id
                WHERE cm.user_id = :uid
                ORDER BY c.last_activity DESC
            ");
            $this->db->bind(':uid', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getConversation($id, $userId) {
        try {
            $this->db->query("SELECT c.* FROM conversations c
                              JOIN conversation_members cm ON cm.conversation_id = c.id
                              WHERE c.id = :id AND cm.user_id = :uid");
            $this->db->bind(':id',  (int)$id);
            $this->db->bind(':uid', (int)$userId);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findOrCreateDirect($userA, $userB) {
        try {
            $this->db->query("
                SELECT c.id FROM conversations c
                JOIN conversation_members m1 ON m1.conversation_id = c.id AND m1.user_id = :a
                JOIN conversation_members m2 ON m2.conversation_id = c.id AND m2.user_id = :b
                WHERE c.type = 'direct' LIMIT 1
            ");
            $this->db->bind(':a', (int)$userA);
            $this->db->bind(':b', (int)$userB);
            $existing = $this->db->single();
            if ($existing && !empty($existing['id'])) return (int)$existing['id'];

            $uuid = uniqid('', true);
            $this->db->query("INSERT INTO conversations (uuid, type, created_by) VALUES (:u, 'direct', :cb)");
            $this->db->bind(':u',  $uuid);
            $this->db->bind(':cb', (int)$userA);
            $this->db->execute();
            $convId = (int)$this->db->lastInsertId();

            $this->addMember($convId, $userA);
            $this->addMember($convId, $userB);
            return $convId;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function createGroup($title, $creatorId, $memberIds = []) {
        try {
            $uuid = uniqid('', true);
            $this->db->query("INSERT INTO conversations (uuid, type, title, created_by) VALUES (:u, 'group', :t, :c)");
            $this->db->bind(':u', $uuid);
            $this->db->bind(':t', $title);
            $this->db->bind(':c', (int)$creatorId);
            $this->db->execute();
            $convId = (int)$this->db->lastInsertId();

            $this->addMember($convId, $creatorId, 'owner');
            foreach ((array)$memberIds as $mid) $this->addMember($convId, $mid);
            return $convId;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function addMember($convId, $userId, $role = 'member') {
        try {
            $this->db->query("INSERT IGNORE INTO conversation_members (conversation_id, user_id, role)
                              VALUES (:c, :u, :r)");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $this->db->bind(':r', $role);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isMember($convId, $userId) {
        try {
            $this->db->query("SELECT 1 FROM conversation_members
                              WHERE conversation_id = :c AND user_id = :u LIMIT 1");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            return (bool)$this->db->single();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ★ UPDATED: includes profile_photo + district for the avatar UI */
    public function getMembers($convId) {
        try {
            $this->db->query("
                SELECT u.id, u.first_name, u.last_name, u.uuid,
                       u.profile_photo, u.district,
                       cm.role, cm.last_read_at,
                       COALESCE(p.status, 'offline') AS presence,
                       COALESCE(v.badge_type, '') AS badge
                FROM conversation_members cm
                JOIN users u ON u.id = cm.user_id
                LEFT JOIN user_presence p  ON p.user_id = u.id
                LEFT JOIN verified_users v ON v.user_id = u.id
                WHERE cm.conversation_id = :c
            ");
            $this->db->bind(':c', (int)$convId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ═══════════════════════════════════════════════════════════
       MESSAGES
       ═══════════════════════════════════════════════════════════ */

    public function getMessages($convId, $userId, $beforeId = null, $limit = 50) {
        try {
            $sql = "SELECT m.*, u.first_name, u.last_name, u.uuid AS sender_uuid,
                           u.profile_photo,
                           v.badge_type,
                           (SELECT GROUP_CONCAT(CONCAT(mr.emoji, '|', mr.user_id))
                              FROM message_reactions mr
                              WHERE mr.message_id = m.id) AS reactions
                    FROM messages m
                    JOIN users u ON u.id = m.sender_id
                    LEFT JOIN verified_users v ON v.user_id = u.id
                    WHERE m.conversation_id = :c AND m.deleted_at IS NULL";
            if ($beforeId) $sql .= " AND m.id < :before";
            $sql .= " ORDER BY m.id DESC LIMIT " . (int)$limit;

            $this->db->query($sql);
            $this->db->bind(':c', (int)$convId);
            if ($beforeId) $this->db->bind(':before', (int)$beforeId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? array_reverse($rows) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ★ NEW: poll for new messages after a given ID */
    public function getMessagesAfter($convId, $afterId, $limit = 50) {
        try {
            $this->db->query("
                SELECT m.*, u.first_name, u.last_name, u.uuid AS sender_uuid,
                       u.profile_photo,
                       v.badge_type
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                LEFT JOIN verified_users v ON v.user_id = u.id
                WHERE m.conversation_id = :c
                  AND m.id > :a
                  AND m.deleted_at IS NULL
                ORDER BY m.id ASC
                LIMIT " . (int)$limit
            );
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':a', (int)$afterId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function sendMessage($data) {
        try {
            $this->db->query("INSERT INTO messages
                (uuid, conversation_id, sender_id, message_type, content, media_url, media_meta,
                 location_lat, location_lng, reply_to_id, original_language)
                VALUES (:u, :c, :s, :t, :content, :media, :meta, :lat, :lng, :reply, :lang)");
            $this->db->bind(':u',       uniqid('', true));
            $this->db->bind(':c',       (int)$data['conversation_id']);
            $this->db->bind(':s',       (int)$data['sender_id']);
            $this->db->bind(':t',       $data['type']    ?? $data['message_type'] ?? 'text');
            $this->db->bind(':content', $data['content'] ?? null);
            $this->db->bind(':media',   $data['media_url'] ?? null);
            $this->db->bind(':meta',    isset($data['media_meta']) ? json_encode($data['media_meta']) : null);
            $this->db->bind(':lat',     $data['lat']     ?? $data['location_lat'] ?? null);
            $this->db->bind(':lng',     $data['lng']     ?? $data['location_lng'] ?? null);
            $this->db->bind(':reply',   $data['reply_to'] ?? null);
            $this->db->bind(':lang',    $data['lang']     ?? null);
            $this->db->execute();
            $id = (int)$this->db->lastInsertId();

            $this->db->query("UPDATE conversations
                              SET last_activity = NOW(), last_message_id = :m
                              WHERE id = :c");
            $this->db->bind(':m', $id);
            $this->db->bind(':c', (int)$data['conversation_id']);
            $this->db->execute();

            return $id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function markRead($convId, $userId, $lastMessageId = null) {
        try {
            $this->db->query("UPDATE conversation_members SET last_read_at = NOW()
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $this->db->execute();

            if ($lastMessageId) {
                $this->db->query("INSERT IGNORE INTO message_reads (message_id, user_id) VALUES (:m, :u)");
                $this->db->bind(':m', (int)$lastMessageId);
                $this->db->bind(':u', (int)$userId);
                $this->db->execute();
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function addReaction($messageId, $userId, $emoji) {
        try {
            $this->db->query("INSERT IGNORE INTO message_reactions (message_id, user_id, emoji)
                              VALUES (:m, :u, :e)");
            $this->db->bind(':m', (int)$messageId);
            $this->db->bind(':u', (int)$userId);
            $this->db->bind(':e', $emoji);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function removeReaction($messageId, $userId, $emoji) {
        try {
            $this->db->query("DELETE FROM message_reactions
                              WHERE message_id = :m AND user_id = :u AND emoji = :e");
            $this->db->bind(':m', (int)$messageId);
            $this->db->bind(':u', (int)$userId);
            $this->db->bind(':e', $emoji);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function setTyping($convId, $userId) {
        try {
            $this->db->query("INSERT INTO typing_indicators (conversation_id, user_id, started_at)
                              VALUES (:c, :u, NOW())
                              ON DUPLICATE KEY UPDATE started_at = NOW()");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getTypingUsers($convId, $excludeUser) {
        try {
            $this->db->query("
                SELECT u.first_name FROM typing_indicators t
                JOIN users u ON u.id = t.user_id
                WHERE t.conversation_id = :c
                  AND t.user_id != :u
                  AND t.started_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
            ");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$excludeUser);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ═══════════════════════════════════════════════════════════
       PRESENCE
       ═══════════════════════════════════════════════════════════ */

    public function setPresence($userId, $status = 'online') {
        try {
            $this->db->query("INSERT INTO user_presence (user_id, status, last_seen)
                              VALUES (:u, :s, NOW())
                              ON DUPLICATE KEY UPDATE status = :s2, last_seen = NOW()");
            $this->db->bind(':u',  (int)$userId);
            $this->db->bind(':s',  $status);
            $this->db->bind(':s2', $status);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       TRANSLATION
       ═══════════════════════════════════════════════════════════ */

    public function getCachedTranslation($messageId, $lang) {
        try {
            $this->db->query("SELECT translated_text FROM message_translations
                              WHERE message_id = :m AND target_lang = :l");
            $this->db->bind(':m', (int)$messageId);
            $this->db->bind(':l', $lang);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function cacheTranslation($messageId, $lang, $text, $provider = 'local') {
        try {
            $this->db->query("INSERT INTO message_translations
                              (message_id, target_lang, translated_text, provider)
                              VALUES (:m, :l, :t, :p)
                              ON DUPLICATE KEY UPDATE translated_text = :t2");
            $this->db->bind(':m',  (int)$messageId);
            $this->db->bind(':l',  $lang);
            $this->db->bind(':t',  $text);
            $this->db->bind(':t2', $text);
            $this->db->bind(':p',  $provider);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       FEED
       ═══════════════════════════════════════════════════════════ */

    public function getFeed($limit = 20, $districtId = null) {
        try {
            $sql = "SELECT p.*, u.first_name, u.last_name, u.uuid AS user_uuid,
                           u.profile_photo, v.badge_type
                    FROM feed_posts p
                    JOIN users u ON u.id = p.user_id
                    LEFT JOIN verified_users v ON v.user_id = u.id
                    WHERE 1 = 1";
            if ($districtId) $sql .= " AND (p.district_id = :d OR p.district_id IS NULL)";
            $sql .= " ORDER BY p.created_at DESC LIMIT " . (int)$limit;

            $this->db->query($sql);
            if ($districtId) $this->db->bind(':d', (int)$districtId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function createPost($data) {
        try {
            $this->db->query("INSERT INTO feed_posts
                (uuid, user_id, post_type, title, content, media_url, district_id, crop_id)
                VALUES (:u, :uid, :t, :ti, :c, :m, :d, :cr)");
            $this->db->bind(':u',   uniqid('', true));
            $this->db->bind(':uid', (int)$data['user_id']);
            $this->db->bind(':t',   $data['type']    ?? 'text');
            $this->db->bind(':ti',  $data['title']   ?? null);
            $this->db->bind(':c',   $data['content'] ?? null);
            $this->db->bind(':m',   $data['media_url'] ?? null);
            $this->db->bind(':d',   $data['district_id'] ?? null);
            $this->db->bind(':cr',  $data['crop_id'] ?? null);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function toggleLike($postId, $userId) {
        try {
            $this->db->query("SELECT id FROM feed_likes WHERE post_id = :p AND user_id = :u");
            $this->db->bind(':p', (int)$postId);
            $this->db->bind(':u', (int)$userId);
            $exists = $this->db->single();

            if ($exists) {
                $this->db->query("DELETE FROM feed_likes WHERE id = :id");
                $this->db->bind(':id', (int)$exists['id']);
                $this->db->execute();

                $this->db->query("UPDATE feed_posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = :p");
                $this->db->bind(':p', (int)$postId);
                $this->db->execute();
                return 'unliked';
            }

            $this->db->query("INSERT INTO feed_likes (post_id, user_id) VALUES (:p, :u)");
            $this->db->bind(':p', (int)$postId);
            $this->db->bind(':u', (int)$userId);
            $this->db->execute();

            $this->db->query("UPDATE feed_posts SET likes_count = likes_count + 1 WHERE id = :p");
            $this->db->bind(':p', (int)$postId);
            $this->db->execute();
            return 'liked';
        } catch (\Throwable $e) {
            return 'error';
        }
    }

    /* ═══════════════════════════════════════════════════════════
       FORUMS
       ═══════════════════════════════════════════════════════════ */

    public function getForums() {
        try {
            $this->db->query("SELECT * FROM forum_channels WHERE is_active = 1 ORDER BY channel_type, name");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function joinForum($channelId, $userId) {
        try {
            $this->db->query("SELECT conversation_id FROM forum_channels WHERE id = :id");
            $this->db->bind(':id', (int)$channelId);
            $ch = $this->db->single();
            if (!$ch || empty($ch['conversation_id'])) return false;
            return $this->addMember((int)$ch['conversation_id'], $userId);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       USER SEARCH  ★ UPDATED for messenger dropdown
       ═══════════════════════════════════════════════════════════ */
/* ═══════════════════════════════════════════════════════════
   SEARCH / LIST USERS — bulletproof, no joins
   Empty $query → returns ALL active users (limit 30)
   ═══════════════════════════════════════════════════════════ */
public function searchUsers($query, $excludeUserId)
{
    try {
        $sql = "SELECT id, first_name, last_name, email, profile_photo, district, role_id
                FROM users
                WHERE id != :me";
        $bind = [':me' => (int)$excludeUserId];

        $q = trim((string)$query);
        if ($q !== '') {
            $sql .= " AND (
                        first_name LIKE :q
                     OR last_name  LIKE :q
                     OR CONCAT(first_name, ' ', COALESCE(last_name,'')) LIKE :q
                     OR email      LIKE :q
                     )";
            $bind[':q'] = '%' . $q . '%';
        }

        $sql .= " ORDER BY first_name ASC LIMIT 30";

        $this->db->query($sql);
        foreach ($bind as $k => $v) $this->db->bind($k, $v);
        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    } catch (\Throwable $e) {
        error_log('[Chat::searchUsers] ' . $e->getMessage());
        return [];
    }
}
        /* ═══════════════ PIN / MUTE / LEAVE ═══════════════ */
    public function togglePin($convId, $userId) {
        try {
            $this->db->query("SELECT is_pinned FROM conversation_members
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $row = $this->db->single();
            $new = empty($row['is_pinned']) ? 1 : 0;

            $this->db->query("UPDATE conversation_members SET is_pinned = :p
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':p', $new);
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $this->db->execute();
            return (bool)$new;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function toggleMute($convId, $userId) {
        try {
            $this->db->query("SELECT is_muted FROM conversation_members
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $row = $this->db->single();
            $new = empty($row['is_muted']) ? 1 : 0;

            $this->db->query("UPDATE conversation_members SET is_muted = :m
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':m', $new);
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            $this->db->execute();
            return (bool)$new;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function leaveConversation($convId, $userId) {
        try {
            $this->db->query("DELETE FROM conversation_members
                              WHERE conversation_id = :c AND user_id = :u");
            $this->db->bind(':c', (int)$convId);
            $this->db->bind(':u', (int)$userId);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ═══════════════ EDIT / DELETE MESSAGES ═══════════════ */
    public function editMessage($msgId, $userId, $newContent) {
        try {
            /* Only owner + only within 5 minutes */
            $this->db->query("SELECT sender_id, sent_at FROM messages WHERE id = :id");
            $this->db->bind(':id', (int)$msgId);
            $m = $this->db->single();
            if (!$m || (int)$m['sender_id'] !== (int)$userId) return false;
            if (time() - strtotime($m['sent_at']) > 300) return false;

            $this->db->query("UPDATE messages SET content = :c, edited_at = NOW() WHERE id = :id");
            $this->db->bind(':c', $newContent);
            $this->db->bind(':id', (int)$msgId);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function deleteMessage($msgId, $userId) {
        try {
            $this->db->query("SELECT sender_id FROM messages WHERE id = :id");
            $this->db->bind(':id', (int)$msgId);
            $m = $this->db->single();
            if (!$m || (int)$m['sender_id'] !== (int)$userId) return false;

            /* Soft delete */
            $this->db->query("UPDATE messages SET deleted_at = NOW(), content = NULL WHERE id = :id");
            $this->db->bind(':id', (int)$msgId);
            return $this->db->execute();
        } catch (\Throwable $e) {
            return false;
        }
    }
}