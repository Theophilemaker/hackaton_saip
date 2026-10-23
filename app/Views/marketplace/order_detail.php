<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card order-header">
            <div>
                <h2>Order <?= e($data['order']['order_number']) ?></h2>
                <p><?= date('F d, Y', strtotime($data['order']['created_at'])) ?></p>
            </div>
            <span class="status-pill <?= $data['order']['status'] ?>"><?= ucfirst($data['order']['status']) ?></span>
        </section>

        <section class="order-detail-grid">
            <div class="glass-card">
                <div class="card-header"><h3>📦 Product</h3></div>
                <div class="order-product">
                    <img src="<?= e($data['order']['main_image'] ?? '') ?>" alt="">
                    <div>
                        <strong><?= e($data['order']['title']) ?></strong>
                        <p><?= number_format($data['order']['quantity']) ?> <?= $data['order']['unit'] ?> × <?= number_format($data['order']['unit_price']) ?> RWF</p>
                    </div>
                </div>
                <div class="order-summary">
                    <div><span>Subtotal</span><strong><?= number_format($data['order']['total_amount']) ?> RWF</strong></div>
                    <div><span>Delivery</span><strong><?= number_format($data['order']['delivery_fee'] ?? 0) ?> RWF</strong></div>
                    <div class="grand"><span>Total</span><strong><?= number_format($data['order']['total_amount'] + ($data['order']['delivery_fee'] ?? 0)) ?> RWF</strong></div>
                </div>
            </div>

            <div class="glass-card">
                <div class="card-header"><h3>🚚 Delivery Tracking</h3></div>
                <?php if ($data['order']['delivery_id']): ?>
                    <div class="tracking-timeline">
                        <?php foreach (['pending','assigned','picked_up','in_transit','delivered'] as $step): ?>
                            <div class="timeline-item <?= array_search($step, array_column($data['events'], 'event_type')) !== false ? 'done' : '' ?>">
                                <span class="dot"></span>
                                <span><?= ucwords(str_replace('_',' ', $step)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="miniMap" style="height:200px; border-radius:12px; margin-top:12px;"></div>
                <?php else: ?>
                    <p class="empty-state">Pickup order — no delivery tracking.</p>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <div class="card-header"><h3>💳 Payment</h3></div>
                <?php if ($data['order']['payment_status'] === 'paid'): ?>
                    <div class="paid-badge">✅ Paid</div>
                <?php else: ?>
                    <button class="btn-primary" onclick="openPayModal(<?= $data['order']['id'] ?>, <?= $data['order']['total_amount'] ?>)">Pay Now</button>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<!-- Payment Modal -->
<div id="payModal" class="modal-overlay" style="display:none;">
    <div class="glass-card modal">
        <h3>💳 Choose Payment Method</h3>
        <div class="pay-methods">
            <button onclick="payWith('mtn_momo')" class="pay-method">
                <span>📱</span><strong>MTN MoMo</strong>
            </button>
            <button onclick="payWith('airtel_money')" class="pay-method">
                <span>📱</span><strong>Airtel Money</strong>
            </button>
            <button onclick="payWith('bank_transfer')" class="pay-method">
                <span>🏦</span><strong>Bank Transfer</strong>
            </button>
        </div>
        <div id="phoneField" style="display:none; margin-top:15px;">
            <label>Phone Number</label>
            <input type="tel" id="payPhone" placeholder="07XXXXXXXX">
        </div>
        <button class="btn-primary" id="payConfirm" onclick="confirmPayment()" style="display:none; margin-top:15px;">Confirm Payment</button>
    </div>
</div>