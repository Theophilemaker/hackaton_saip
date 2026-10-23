<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">📦</div>
                <div>
                    <p class="kpi-label">Active Products</p>
                    <h3 class="kpi-value"><?= $data['stats']['active_products'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🛒</div>
                <div>
                    <p class="kpi-label">Total Orders</p>
                    <h3 class="kpi-value"><?= $data['stats']['total_orders'] ?? 0 ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">💰</div>
                <div>
                    <p class="kpi-label">Revenue</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['revenue'] ?? 0) ?> <small>RWF</small></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#84CC16,#16A34A)">⚖️</div>
                <div>
                    <p class="kpi-label">Units Sold</p>
                    <h3 class="kpi-value"><?= number_format($data['stats']['units_sold'] ?? 0) ?></h3>
                </div>
            </div>
        </section>

        <section class="glass-card">
            <div class="card-header">
                <h3>📦 My Products</h3>
                <a href="<?= BASE_URL ?>/marketplace/newProduct" class="btn-sm">+ Add Product</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Views</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data['products'])): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-sub);">No products found. Start selling today!</td></tr>
                <?php else: ?>
                    <?php foreach ($data['products'] as $p): ?>
                        <tr>
                            <td><?= e($p['title'] ?? '') ?></td>
                            <td><?= number_format($p['price_per_unit'] ?? 0) ?> RWF</td>
                            <td><?= number_format($p['quantity_available'] ?? 0, 0) ?> <?= e($p['unit'] ?? 'kg') ?></td>
                            <td><span class="status-pill <?= $p['status'] ?? '' ?>"><?= ucfirst($p['status'] ?? 'Unknown') ?></span></td>
                            <td><?= $p['views_count'] ?? 0 ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="glass-card">
            <div class="card-header">
                <h3>🛍️ Recent Orders</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Buyer</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data['orders'])): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-sub);">No recent orders.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['orders'] as $o): ?>
                        <tr>
                            <td><?= e($o['order_number'] ?? '') ?></td>
                            <td><?= e($o['buyer_name'] ?? '') ?></td>
                            <td><?= e($o['title'] ?? '') ?></td>
                            <td><?= number_format($o['total_amount'] ?? 0) ?> RWF</td>
                            <td><span class="status-pill <?= $o['status'] ?? '' ?>"><?= ucfirst($o['status'] ?? '') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>