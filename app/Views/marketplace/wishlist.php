<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- Hero -->
        <section class="glass-card wishlist-hero">
            <div>
                <h1>❤️ <?= __('mkt.wishlist') ?></h1>
                <p>Products you're interested in — saved for later</p>
            </div>
            <a href="<?= BASE_URL ?>/marketplace" class="btn-secondary">← <?= __('nav.marketplace') ?></a>
        </section>

        <!-- Stats -->
        <section class="kpi-grid">
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#EC4899,#F43F5E)">❤️</div>
                <div>
                    <p class="kpi-label">Saved Products</p>
                    <h3 class="kpi-value"><?= count($data['products']) ?></h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#16A34A,#22C55E)">💰</div>
                <div>
                    <p class="kpi-label">Total Value</p>
                    <h3 class="kpi-value">
                        <?php
                        $total = 0;
                        foreach ($data['products'] as $p) {
                            $total += (float)$p['price_per_unit'] * (float)$p['quantity_available'];
                        }
                        echo number_format($total / 1e6, 1) . 'M';
                        ?>
                        <small>RWF</small>
                    </h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#0EA5E9,#6366F1)">🏪</div>
                <div>
                    <p class="kpi-label">Sellers</p>
                    <h3 class="kpi-value">
                        <?= count(array_unique(array_column($data['products'], 'seller_id'))) ?>
                    </h3>
                </div>
            </div>
            <div class="glass-card kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg,#F59E0B,#EF4444)">📦</div>
                <div>
                    <p class="kpi-label">In Stock</p>
                    <h3 class="kpi-value">
                        <?= count(array_filter($data['products'], fn($p) => $p['status'] === 'active')) ?>
                    </h3>
                </div>
            </div>
        </section>

        <!-- Wishlist Grid -->
        <section class="glass-card">
            <div class="card-header">
                <h3>❤️ Your Saved Products</h3>
                <?php if (!empty($data['products'])): ?>
                    <span class="badge green"><?= count($data['products']) ?> items</span>
                <?php endif; ?>
            </div>

            <?php if (empty($data['products'])): ?>
                <div class="empty-state-card">
                    <span style="font-size:56px;">❤️</span>
                    <h3>Your wishlist is empty</h3>
                    <p>Save products by clicking the heart icon on any listing.</p>
                    <a href="<?= BASE_URL ?>/marketplace" class="btn-primary" style="width:auto; padding:12px 28px;">
                        🔍 Browse Marketplace
                    </a>
                </div>
            <?php else: ?>
                <div class="wishlist-grid">
                    <?php foreach ($data['products'] as $p): ?>
                        <div class="wish-card" id="wish-<?= $p['id'] ?>">
                            <div class="wish-image">
                                <a href="<?= BASE_URL ?>/marketplace/product/<?= $p['id'] ?>">
                                    <img src="<?= e($p['main_image'] ?? '') ?>" 
                                         alt="<?= e($p['title']) ?>"
                                         onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22><rect fill=%22%23123%22 width=%22400%22 height=%22300%22/><text x=%22200%22 y=%22150%22 fill=%22%23888%22 font-size=%2248%22 text-anchor=%22middle%22>📦</text></svg>'">
                                </a>
                                <button class="wish-remove" onclick="removeWish(<?= $p['id'] ?>)" title="Remove from wishlist">
                                    ❤️
                                </button>
                                <?php if (!empty($p['is_organic'])): ?>
                                    <span class="wish-badge organic">🌱 Organic</span>
                                <?php endif; ?>
                                <?php if (!empty($p['is_export_ready'])): ?>
                                    <span class="wish-badge export">🚢 Export</span>
                                <?php endif; ?>
                            </div>

                            <div class="wish-body">
                                <a href="<?= BASE_URL ?>/marketplace/product/<?= $p['id'] ?>" class="wish-title">
                                    <?= e($p['title']) ?>
                                </a>

                                <div class="wish-seller">
                                    <span><?= e($p['first_name'] ?? 'Seller') ?></span>
                                    <?php if (!empty($p['is_verified'])): ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                </div>

                                <div class="wish-price">
                                    <strong><?= number_format($p['price_per_unit']) ?> RWF</strong>
                                    <small>/ <?= e($p['unit']) ?></small>
                                </div>

                                <div class="wish-stock">
                                    <small><?= number_format($p['quantity_available']) ?> <?= e($p['unit']) ?> available</small>
                                </div>

                                <div class="wish-actions">
                                    <a href="<?= BASE_URL ?>/marketplace/product/<?= $p['id'] ?>" class="btn-sm primary">
                                        🛒 View & Order
                                    </a>
                                    <a href="<?= BASE_URL ?>/marketplace/toggleWishlist/<?= $p['id'] ?>" 
                                       class="btn-sm ghost"
                                       onclick="event.preventDefault(); removeWish(<?= $p['id'] ?>);">
                                        ✕ Remove
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- CTA -->
        <?php if (!empty($data['products'])): ?>
            <section class="glass-card wishlist-cta">
                <div>
                    <h3>💡 Looking for something specific?</h3>
                    <p>Post a buying request and let sellers come to you.</p>
                </div>
                <a href="<?= BASE_URL ?>/marketplace/exports" class="btn-primary">
                    📋 Post Buy Request
                </a>
            </section>
        <?php endif; ?>
    </main>
</div>

<script>
async function removeWish(productId) {
    if (!confirm('Remove this product from your wishlist?')) return;

    const res = await fetch(`<?= BASE_URL ?>/marketplace/toggleWishlist/${productId}`, {
        method: 'POST'
    });
    const data = await res.json();

    if (data.result === 'removed') {
        const el = document.getElementById('wish-' + productId);
        if (el) {
            el.style.transition = '300ms';
            el.style.opacity = '0';
            el.style.transform = 'scale(0.9)';
            setTimeout(() => {
                el.remove();
                // If no more cards, reload to show empty state
                if (document.querySelectorAll('.wish-card').length === 0) {
                    window.location.reload();
                }
            }, 300);
        }
    }
}
</script>

<style>
.wishlist-hero {
    padding: 30px !important;
    max-width: none;
    background: linear-gradient(135deg, rgba(236,72,153,0.15), rgba(244,63,94,0.05));
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.wishlist-hero h1 { margin: 0 0 4px; font-size: 24px; }
.wishlist-hero p { color: var(--text-sub); margin: 0; font-size: 13px; }

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
    margin-top: 16px;
}
.wish-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: 250ms;
}
.wish-card:hover {
    transform: translateY(-4px);
    border-color: #EC4899;
    box-shadow: 0 10px 30px rgba(236,72,153,0.15);
}
.wish-image {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: rgba(0,0,0,0.3);
}
.wish-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 400ms;
}
.wish-card:hover .wish-image img { transform: scale(1.06); }

.wish-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 200ms;
    backdrop-filter: blur(6px);
}
.wish-remove:hover {
    background: #EC4899;
    transform: scale(1.15);
}
.wish-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.wish-badge.organic {
    background: rgba(34,197,94,0.9);
    color: white;
}
.wish-badge.export {
    background: rgba(14,165,233,0.9);
    color: white;
    left: auto;
    right: 10px;
}

.wish-body { padding: 16px; }
.wish-title {
    display: block;
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    transition: 200ms;
}
.wish-title:hover { color: var(--accent); }

.wish-seller {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--text-sub);
    margin-bottom: 12px;
}
.verified-badge {
    background: #0EA5E9;
    color: white;
    padding: 1px 6px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 700;
}

.wish-price {
    display: flex;
    align-items: baseline;
    gap: 4px;
    margin-bottom: 6px;
}
.wish-price strong {
    font-size: 18px;
    color: var(--accent);
}
.wish-price small {
    font-size: 11px;
    color: var(--text-sub);
}

.wish-stock {
    font-size: 11px;
    color: var(--text-sub);
    margin-bottom: 14px;
}

.wish-actions {
    display: flex;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
}
.wish-actions .btn-sm {
    flex: 1;
    text-align: center;
    text-decoration: none;
    padding: 10px 12px;
    font-size: 12px;
}
.btn-sm.primary {
    background: var(--primary);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    transition: 200ms;
}
.btn-sm.primary:hover { background: var(--secondary); }
.btn-sm.ghost {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-sub);
    border-radius: 10px;
    transition: 200ms;
}
.btn-sm.ghost:hover {
    border-color: #EF4444;
    color: #EF4444;
    background: rgba(239,68,68,0.08);
}

.wishlist-cta {
    padding: 25px !important;
    max-width: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(236,72,153,0.08), rgba(34,197,94,0.05));
    margin-top: 20px;
}
.wishlist-cta h3 { margin: 0 0 4px; font-size: 18px; }
.wishlist-cta p { color: var(--text-sub); margin: 0; font-size: 13px; }

.empty-state-card {
    text-align: center;
    padding: 70px 20px;
}
.empty-state-card h3 { margin: 14px 0 8px; font-size: 18px; }
.empty-state-card p { color: var(--text-sub); margin: 0 0 20px; font-size: 13px; }
</style>