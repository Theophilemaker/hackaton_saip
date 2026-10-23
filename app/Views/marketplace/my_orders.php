<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>🛍️ My Orders</h3></div>
            <?php if (empty($data['orders'])): ?>
                <p class="empty-state">No orders yet. <a href="<?= BASE_URL ?>/marketplace">Browse products</a></p>
            <?php else: ?>
                <?php foreach ($data['orders'] as $o): ?>
                    <div class="order-card" onclick="window.location='<?= BASE_URL ?>/marketplace/myOrder/<?= $o['id'] ?>'">
                        <img src="<?= e($o['main_image'] ?? '') ?>" alt="">
                        <div class="order-info">
                            <strong><?= e($o['title']) ?></strong>
                            <small>Order <?= e($o['order_number']) ?> · <?= date('M d, Y', strtotime($o['created_at'])) ?></small>
                            <span class="status-pill <?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
                        </div>
                        <div class="order-total">
                            <strong><?= number_format($o['total_amount']) ?> RWF</strong>
                            <small><?= e($o['seller_name']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>