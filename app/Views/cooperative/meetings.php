<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $data['coop']['id'] ?>" class="btn-secondary" style="display:inline-block; margin-bottom:12px;">← Back</a>
            <div class="card-header">
                <h3>📅 Meetings</h3>
                <button onclick="document.getElementById('addMeetingModal').style.display='flex'" class="btn-sm">+ Schedule</button>
            </div>
            <div class="meeting-list">
                <?php foreach ($data['meetings'] as $m): ?>
                    <div class="meeting-card">
                        <div class="meeting-date-badge"><?= date('d', strtotime($m['meeting_date'])) ?><small><?= date('M', strtotime($m['meeting_date'])) ?></small></div>
                        <div class="meeting-info">
                            <strong><?= e($m['title']) ?></strong>
                            <p><?= e($m['agenda']) ?></p>
                            <small>📍 <?= e($m['location']) ?> · 👥 <?= $m['attended_count'] ?> attended</small>
                        </div>
                        <span class="status-pill <?= $m['status'] === 'completed' ? 'paid' : 'pending' ?>"><?= ucfirst($m['status']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

<div id="addMeetingModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>Schedule Meeting</h3>
        <form method="POST" action="<?= BASE_URL ?>/cooperative/createMeeting/<?= $data['coop']['id'] ?>">
            <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Agenda</label><textarea name="agenda" rows="3"></textarea></div>
            <div class="form-group"><label>Date & Time</label><input type="datetime-local" name="meeting_date" required></div>
            <div class="form-group"><label>Location</label><input type="text" name="location"></div>
            <div class="action-buttons">
                <button class="btn-primary">Schedule</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addMeetingModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>