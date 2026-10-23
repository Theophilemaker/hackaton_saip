<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="product-detail" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <div class="glass-card product-gallery" style="flex: 1; min-width: 300px; padding: 20px; background: rgba(30,41,59,0.65); border-radius: 16px; border: 1px solid var(--border);">
                <img src="<?= e($data['product']['main_image'] ?? '') ?>" alt="" style="width: 100%; border-radius: 12px; object-fit: cover;">
            </div>

            <div class="glass-card product-info" style="flex: 1; min-width: 300px; padding: 25px; background: rgba(30,41,59,0.65); border-radius: 16px; border: 1px solid var(--border);">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
                    <h1 style="margin:0; font-size: 24px;"><?= e($data['product']['title']) ?></h1>
                    <?php if (!empty($data['product']['is_organic'])): ?><span class="badge org" style="background:rgba(34,197,94,0.2); color:#22c55e; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold;">🌱 Organic</span><?php endif; ?>
                </div>

                <div class="seller-strip" style="display: flex; align-items: center; gap: 15px; margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px;">
                    <div class="seller-avatar" style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                        <?= strtoupper(substr($data['product']['first_name'] ?? 'S', 0, 1)) ?>
                    </div>
                    <div>
                        <strong><?= e($data['product']['business_name'] ?? $data['product']['first_name'] ?? 'Seller') ?></strong>
                        <?php if (!empty($data['product']['is_verified'])): ?>
                            <span class="verified-badge" style="background:#0EA5E9; padding:2px 6px; border-radius:10px; font-size:10px; margin-left:5px;">✓ Verified</span>
                        <?php endif; ?>
                        <br>
                        <small style="color: var(--text-sub);">⭐ <?= number_format($data['product']['seller_rating'] ?? 0, 1) ?> · <?= $data['product']['total_sales'] ?? 0 ?> sales</small>
                    </div>
                </div>

                <div class="price-block" style="margin-bottom: 20px;">
                    <h2 style="color: var(--accent); font-size: 28px; margin: 0;"><?= number_format($data['product']['price_per_unit']) ?> <small style="font-size: 16px; color: var(--text-sub);">RWF / <?= e($data['product']['unit']) ?></small></h2>
                    <span class="stock" style="color: var(--text-sub); font-size: 13px;"><?= number_format($data['product']['quantity_available'] ?? 0, 0) ?> <?= e($data['product']['unit']) ?> available</span>
                </div>

                <div class="ai-price-hint" style="background: rgba(132,204,22,0.1); border: 1px solid var(--accent); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <span style="color: var(--accent); font-weight: bold; display: block; margin-bottom: 5px;">🤖 AI Price Advisor</span>
                    <strong style="font-size: 18px;"><?= number_format($data['ai_price']['suggested_price'] ?? 0) ?> RWF</strong>
                    <br>
                    <small style="color: var(--text-sub);">Range: <?= number_format($data['ai_price']['min_price'] ?? 0) ?> – <?= number_format($data['ai_price']['max_price'] ?? 0) ?> · Confidence <?= round(($data['ai_price']['confidence'] ?? 0) * 100) ?>%</small>
                </div>

                <p class="product-desc" style="color: var(--text-sub); line-height: 1.6; margin-bottom: 20px;"><?= nl2br(e($data['product']['description'] ?? '')) ?></p>

                <form action="<?= BASE_URL ?>/marketplace/order/<?= $data['product']['id'] ?>" method="POST" class="order-form">
                    <div class="form-row" style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:13px; color:var(--text-sub);">Quantity</label>
                        <input type="number" name="quantity" min="<?= $data['product']['min_order_qty'] ?? 1 ?>" max="<?= $data['product']['quantity_available'] ?? 100 ?>" value="<?= $data['product']['min_order_qty'] ?? 1 ?>" step="0.5" required style="width:100%; padding:10px; border-radius:8px; background:rgba(0,0,0,0.2); border:1px solid var(--border); color:white;">
                    </div>
                    <div class="form-row" style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:13px; color:var(--text-sub);">Delivery Method</label>
                        <select name="delivery_method" style="width:100%; padding:10px; border-radius:8px; background:rgba(0,0,0,0.2); border:1px solid var(--border); color:white;">
                            <option value="delivery">🚚 Delivery</option>
                            <option value="pickup">📍 Pickup</option>
                        </select>
                    </div>
                    <div class="form-row" style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:13px; color:var(--text-sub);">Address</label>
                        <input type="text" name="delivery_address" placeholder="Sector, Cell, Village" style="width:100%; padding:10px; border-radius:8px; background:rgba(0,0,0,0.2); border:1px solid var(--border); color:white;">
                    </div>

                    <div class="action-buttons" style="display:flex; gap:10px; margin-top:20px;">
                        <button type="submit" class="btn-primary" style="flex:1; padding:12px; border-radius:8px; background:var(--primary); color:white; border:none; cursor:pointer; font-weight:bold;"><?= __('mkt.order_now') ?: 'Order Now' ?></button>
                        <button type="button" class="btn-secondary" onclick="openNegotiation(<?= $data['product']['id'] ?>)" style="flex:1; padding:12px; border-radius:8px; background:transparent; border:1px solid var(--border); color:white; cursor:pointer;">
                            💬 <?= __('mkt.negotiate') ?: 'Negotiate' ?>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Reviews -->
        <section class="glass-card reviews-section" style="padding: 20px; background: rgba(30,41,59,0.65); border-radius: 16px; border: 1px solid var(--border);">
            <div class="card-header" style="border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 15px;"><h3 style="margin:0;">⭐ <?= __('mkt.reviews') ?: 'Reviews' ?></h3></div>
            <?php if (empty($data['reviews'])): ?>
                <p class="empty-state" style="color: var(--text-sub);"><?= __('mkt.no_reviews') ?: 'No reviews yet.' ?></p>
            <?php else: ?>
                <?php foreach ($data['reviews'] as $r): ?>
                    <div class="review-item" style="border-bottom: 1px solid var(--border); padding: 15px 0;">
                        <div class="review-header" style="display:flex; justify-content:space-between; margin-bottom:5px;">
                            <strong><?= e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></strong>
                            <span class="stars"><?= str_repeat('⭐', $r['rating'] ?? 0) ?></span>
                        </div>
                        <p style="margin:0 0 5px; color:var(--text-sub);"><?= e($r['comment'] ?? '') ?></p>
                        <small style="color: var(--text-sub); font-size:11px;"><?= date('M d, Y', strtotime($r['created_at'] ?? 'now')) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- Negotiation Modal -->
<div id="negoModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(5px); z-index:1000; align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card modal" style="max-width:400px; width:100%; padding:25px; background:#0f172a; border:1px solid var(--border); border-radius:16px;">
        <h3 style="margin-top:0;">💬 <?= __('mkt.negotiate') ?: 'Negotiate' ?></h3>
        <p style="color:var(--text-sub); font-size:13px;"><?= __('mkt.negotiate_hint') ?: 'Suggest a price to the seller.' ?></p>
        <div class="form-group" style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:5px; font-size:13px; color:var(--text-sub);">Your offer (RWF / unit)</label>
            <input type="number" id="negoPrice" required style="width:100%; padding:10px; border-radius:8px; background:rgba(0,0,0,0.2); border:1px solid var(--border); color:white;">
        </div>
        <div class="form-group" style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:5px; font-size:13px; color:var(--text-sub);">Quantity</label>
            <input type="number" id="negoQty" value="1" required style="width:100%; padding:10px; border-radius:8px; background:rgba(0,0,0,0.2); border:1px solid var(--border); color:white;">
        </div>
        <div class="action-buttons" style="display:flex; gap:10px;">
            <button class="btn-primary" onclick="submitNegotiation(<?= $data['product']['id'] ?>)" style="flex:1; padding:12px; border-radius:8px; background:var(--primary); color:white; border:none; cursor:pointer;">Send Offer</button>
            <button class="btn-secondary" onclick="document.getElementById('negoModal').style.display='none'" style="flex:1; padding:12px; border-radius:8px; background:transparent; border:1px solid var(--border); color:white; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<script>
function openNegotiation(productId) {
    document.getElementById('negoModal').style.display = 'flex';
}
function submitNegotiation(productId) {
    const price = document.getElementById('negoPrice').value;
    const qty = document.getElementById('negoQty').value;
    if(!price) return alert('Please enter a price');
    
    // You would typically fetch() to the controller here
    alert(`Offer sent for ${qty} units at ${price} RWF.`);
    document.getElementById('negoModal').style.display = 'none';
}
</script>