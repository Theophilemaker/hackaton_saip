<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card chat-room" data-conv-id="<?= $data['conversation']['id'] ?>" data-my-id="<?= $_SESSION['user_id'] ?>">
            <header class="chat-room-header">
                <a href="<?= BASE_URL ?>/chat" class="back-btn">←</a>
                <div class="chat-title">
                    <strong><?= e($data['conversation']['title'] ?? 'Direct Chat') ?></strong>
                    <small id="typingIndicator"></small>
                </div>
                <div class="chat-actions">
                    <button onclick="alert('Voice call — coming in Phase 5')">📞</button>
                    <button onclick="alert('Video call — coming in Phase 5')">📹</button>
                </div>
            </header>

            <div class="chat-messages" id="chatMessages">
                <?php foreach ($data['messages'] as $m): ?>
                    <div class="msg <?= $m['sender_id'] == $_SESSION['user_id'] ? 'out' : 'in' ?>" data-id="<?= $m['id'] ?>">
                        <?php if ($m['sender_id'] != $_SESSION['user_id']): ?>
                            <div class="msg-avatar"><?= strtoupper(substr($m['first_name'],0,1)) ?></div>
                        <?php endif; ?>
                        <div class="msg-body">
                            <?php if ($m['sender_id'] != $_SESSION['user_id']): ?>
                                <span class="msg-sender"><?= e($m['first_name']) ?>
                                    <?php if (!empty($m['badge_type'])): ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <div class="msg-content">
                                <?php if ($m['message_type'] === 'text'): ?>
                                    <?= nl2br(e($m['content'])) ?>
                                <?php elseif ($m['message_type'] === 'image'): ?>
                                    <img src="<?= e($m['media_url']) ?>" class="msg-media" onclick="window.open(this.src)">
                                <?php elseif ($m['message_type'] === 'video'): ?>
                                    <video src="<?= e($m['media_url']) ?>" controls class="msg-media"></video>
                                <?php elseif ($m['message_type'] === 'audio'): ?>
                                    <audio src="<?= e($m['media_url']) ?>" controls></audio>
                                <?php elseif ($m['message_type'] === 'file'): ?>
                                    <a href="<?= e($m['media_url']) ?>" target="_blank">📎 Download file</a>
                                <?php elseif ($m['message_type'] === 'location'): ?>
                                    <a href="https://maps.google.com/?q=<?= $m['location_lat'] ?>,<?= $m['location_lng'] ?>" target="_blank">📍 View location</a>
                                <?php endif; ?>
                            </div>
                            <div class="msg-footer">
                                <span class="msg-time"><?= date('H:i', strtotime($m['sent_at'])) ?></span>
                                <?php if ($m['sender_id'] == $_SESSION['user_id']): ?>
                                    <span class="msg-status <?= $m['status'] ?>"><?= $m['status'] === 'read' ? '✓✓' : '✓' ?></span>
                                <?php endif; ?>
                                <button class="msg-react-btn" onclick="showEmojiPicker(<?= $m['id'] ?>, event)">😊</button>
                                <button class="msg-translate-btn" onclick="translateMessage(<?= $m['id'] ?>, this)">🌐</button>
                            </div>
                            <div class="msg-reactions" id="reactions-<?= $m['id'] ?>">
                                <?php if (!empty($m['reactions'])): ?>
                                    <?php foreach (explode(',', $m['reactions']) as $r): 
                                        [$em, $uid] = explode('|', $r); ?>
                                        <span class="reaction-chip"><?= $em ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="chat-input" id="chatForm" enctype="multipart/form-data">
                <input type="hidden" name="conversation_id" value="<?= $data['conversation']['id'] ?>">
                <input type="hidden" name="type" id="msgType" value="text">
                <label class="attach-btn" title="Attach">
                    📎 <input type="file" name="media" id="mediaInput" hidden>
                </label>
                <button type="button" class="attach-btn" onclick="recordVoice(this)" title="Voice note">🎤</button>
                <button type="button" class="attach-btn" onclick="shareLocation()" title="Location">📍</button>
                <input type="text" name="content" id="msgInput" placeholder="Type a message..." autocomplete="off">
                <button type="submit" class="send-btn">➤</button>
            </form>
        </section>
    </main>

    <!-- Emoji picker (hidden) -->
    <div id="emojiPicker" class="emoji-picker" style="display:none">
        <?php foreach (['👍','❤️','😂','😮','😢','🌱','🌽','☕','🔥','🎉'] as $e): ?>
            <button onclick="sendReaction('<?= $e ?>')"><?= $e ?></button>
        <?php endforeach; ?>
    </div>
</div>