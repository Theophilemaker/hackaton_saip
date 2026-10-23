<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card coop-hero">
            <div>
                <h1>👥 <?= __('coop.title') ?></h1>
                <p><?= __('coop.subtitle') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/cooperative/create" class="btn-primary" style="width:auto; padding:12px 24px;">+ <?= __('coop.create') ?></a>
        </section>

        <?php if (!empty($data['my_coops'])): ?>
            <section class="glass-card">
                <div class="card-header"><h3>🌾 <?= __('coop.my_coops') ?></h3></div>
                <div class="coop-grid">
                    <?php foreach ($data['my_coops'] as $c): ?>
                        <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $c['id'] ?>" class="coop-card">
                            <div class="coop-avatar">👥</div>
                            <div>
                                <strong><?= e($c['name']) ?></strong>
                                <small><?= e($c['district_name'] ?? '') ?> · <?= ucfirst($c['my_role']) ?></small>
                                <span class="member-count"><?= $c['member_count'] ?> members</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🌍 <?= __('coop.all_coops') ?></h3></div>
            <div class="coop-grid">
                <?php foreach ($data['all_coops'] as $c): ?>
                    <a href="<?= BASE_URL ?>/cooperative/dashboard/<?= $c['id'] ?>" class="coop-card">
                        <div class="coop-avatar">👥</div>
                        <div>
                            <strong><?= e($c['name']) ?></strong>
                            <small><?= e($c['district_name'] ?? '') ?></small>
                            <span class="member-count"><?= $c['members'] ?> members</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>