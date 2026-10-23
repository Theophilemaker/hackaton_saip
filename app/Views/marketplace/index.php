<div class="dashboard-layout marketplace-page">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <!-- ============ HERO ============ -->
        <section class="glass-card mkt-hero">
            <div class="hero-content">
                <span class="hero-tag">Fresh From Rwandan Farms</span>
                <h1>🌾 <?= __('mkt.hero_title') ?: 'Produce Marketplace' ?></h1>
                <p><?= __('mkt.hero_sub') ?: 'Buy directly from farmers and get the freshest produce at the best prices.' ?></p>

                <div class="mkt-stats">
                    <div class="stat-item"><strong>49+</strong><small>Products</small></div>
                    <div class="stat-item"><strong>500+</strong><small>Sellers</small></div>
                    <div class="stat-item"><strong>30</strong><small>Districts</small></div>
                    <div class="stat-item"><strong>4.8★</strong><small>Avg Rating</small></div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/marketplace/sell" class="btn-hero-sell">
                <span>+</span> <?= __('mkt.sell') ?: 'Sell Product' ?>
            </a>
        </section>

        <!-- ============ FILTERS ============ -->
        <section class="glass-card mkt-filters">
            <form method="GET" action="<?= BASE_URL ?>/marketplace">
                <div class="filter-row">
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="q" placeholder="<?= __('mkt.search') ?: 'Search products, sellers, or districts...' ?>" value="<?= e($data['filters']['q'] ?? '') ?>">
                    </div>

                    <select name="category" class="filter-select">
                        <option value="">📂 <?= __('mkt.all_categories') ?: 'All Categories' ?></option>
                        <?php foreach ($data['categories'] ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($data['filters']['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= $c['icon'] ?? '📦' ?> <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="sort" class="filter-select">
                        <option value="popular"    <?= ($data['filters']['sort'] ?? '') === 'popular'    ? 'selected' : '' ?>>🔥 Most Popular</option>
                        <option value="price_asc"  <?= ($data['filters']['sort'] ?? '') === 'price_asc'  ? 'selected' : '' ?>>💰 Price ↑</option>
                        <option value="price_desc" <?= ($data['filters']['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>💰 Price ↓</option>
                        <option value="rating"     <?= ($data['filters']['sort'] ?? '') === 'rating'     ? 'selected' : '' ?>>⭐ Top Rated</option>
                    </select>

                    <button type="submit" class="btn-filter"><?= __('mkt.filter') ?: 'Filter' ?></button>
                </div>

                <div class="filter-chips">
                    <label class="chip">
                        <input type="checkbox" name="organic" value="1" <?= !empty($data['filters']['organic']) ? 'checked' : '' ?>>
                        <span>🌱 Organic</span>
                    </label>
                    <label class="chip">
                        <input type="checkbox" name="export" value="1" <?= !empty($data['filters']['export_ready']) ? 'checked' : '' ?>>
                        <span>🚢 Export Ready</span>
                    </label>
                </div>
            </form>
        </section>

        <!-- ============ PRODUCT GRID ============ -->
        <section class="product-grid">
            <?php if (empty($data['products'])): ?>
                <div class="glass-card empty-state-card">
                    <div class="empty-icon">📦</div>
                    <h3>No Products Found</h3>
                    <p><?= __('mkt.no_products') ?: 'Try adjusting your filters or search terms.' ?></p>
                    <a href="<?= BASE_URL ?>/marketplace" class="btn-hero-secondary">Clear Filters</a>
                </div>
            <?php else: ?>
                <?php foreach ($data['products'] as $p): ?>
                    <article class="glass-card product-card">
                        <a href="<?= BASE_URL ?>/marketplace/product/<?= $p['id'] ?>" class="product-image">
                            <img src="<?= e($p['main_image'] ?? BASE_URL . '/assets/products/default.jpg') ?>" alt="<?= e($p['title']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/products/default.jpg'">
                            <div class="product-badges">
                                <?php if (!empty($p['is_organic'])): ?><span class="badge org">🌱 Organic</span><?php endif; ?>
                                <?php if (!empty($p['quality_grade'])): ?><span class="badge grade">Grade <?= e($p['quality_grade']) ?></span><?php endif; ?>
                            </div>
                        </a>
                        <div class="product-body">
                            <div class="product-header">
                                <h4><?= e($p['title']) ?></h4>
                                <button type="button" onclick="toggleWishlist(<?= (int)$p['id'] ?>, this)" class="wish-btn" title="Add to wishlist">🤍</button>
                            </div>
                            <div class="seller-info">
                                <span class="seller-name"><?= e($p['business_name'] ?? $p['first_name'] ?? 'Farmer') ?></span>
                                <?php if (!empty($p['is_verified'])): ?><span class="verified-badge" title="Verified Seller">✓</span><?php endif; ?>
                                <span class="rating">⭐ <?= number_format((float)($p['seller_rating'] ?? 0), 1) ?></span>
                            </div>
                            <div class="product-price">
                                <strong><?= number_format((float)$p['price_per_unit']) ?> RWF</strong>
                                <small>/ <?= e($p['unit'] ?? 'kg') ?></small>
                            </div>
                            <div class="product-meta">
                                <span class="meta-pill">📦 <?= number_format((float)$p['quantity_available'], 0) ?> <?= e($p['unit'] ?? 'kg') ?> available</span>
                            </div>
                            <a href="<?= BASE_URL ?>/marketplace/product/<?= $p['id'] ?>" class="btn-view">View Details →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
function toggleWishlist(productId, btn) {
    const isActive = btn.textContent === '❤️';
    btn.textContent = isActive ? '🤍' : '❤️';
    fetch('<?= BASE_URL ?>/marketplace/wishlist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    }).catch(() => { btn.textContent = isActive ? '🤍' : '❤️'; });
}
</script>

<style>
/* [Full CSS block from your marketplace-page.txt goes here] */
.marketplace-page.dashboard-layout { display: flex !important; width: 100% !important; min-height: 100vh !important; background-color: #0b1120 !important; overflow-x: hidden !important; }
.marketplace-page .main-content { flex: 1 !important; width: auto !important; max-width: none !important; min-width: 0 !important; padding: 32px !important; box-sizing: border-box !important; margin-left: 260px !important; }
.marketplace-page .glass-card { width: 100% !important; box-sizing: border-box !important; background: rgba(30, 41, 59, 0.65) !important; backdrop-filter: blur(16px) !important; -webkit-backdrop-filter: blur(16px) !important; border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 20px !important; margin-bottom: 24px !important; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important; }
.mkt-hero { display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 24px !important; flex-wrap: wrap !important; padding: 32px !important; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.08)) !important; position: relative !important; overflow: hidden !important; }
.mkt-hero::before { content: '' !important; position: absolute !important; top: -50% !important; right: -10% !important; width: 400px !important; height: 400px !important; background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent 70%) !important; pointer-events: none !important; }
.hero-content { position: relative !important; z-index: 1 !important; flex: 1 !important; min-width: 280px !important; }
.hero-tag { display: inline-block !important; padding: 5px 14px !important; background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; border-radius: 999px !important; font-size: 11px !important; font-weight: 700 !important; letter-spacing: 1px !important; text-transform: uppercase !important; margin-bottom: 14px !important; }
.mkt-hero h1 { font-size: 30px !important; font-weight: 800 !important; color: #f1f5f9 !important; margin: 0 0 10px 0 !important; line-height: 1.2 !important; }
.mkt-hero p { color: #94a3b8 !important; font-size: 14.5px !important; line-height: 1.6 !important; margin: 0 0 22px 0 !important; max-width: 620px !important; }
.mkt-stats { display: flex !important; gap: 24px !important; flex-wrap: wrap !important; margin-top: 20px !important; }
.stat-item { display: flex !important; flex-direction: column !important; gap: 2px !important; }
.stat-item strong { font-size: 22px !important; font-weight: 800 !important; color: #f1f5f9 !important; line-height: 1.1 !important; }
.stat-item small { font-size: 11px !important; color: #94a3b8 !important; text-transform: uppercase !important; letter-spacing: 0.8px !important; font-weight: 600 !important; }
.btn-hero-sell { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 15px 30px !important; background: linear-gradient(135deg, #10b981, #059669) !important; color: #ffffff !important; border: none !important; border-radius: 12px !important; font-size: 14.5px !important; font-weight: 700 !important; text-decoration: none !important; transition: all 0.25s ease !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important; white-space: nowrap !important; position: relative !important; z-index: 1 !important; }
.btn-hero-sell:hover { transform: translateY(-3px) !important; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.55) !important; }
.btn-hero-sell span { font-size: 18px !important; line-height: 1 !important; }
.btn-hero-secondary { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 13px 24px !important; background: rgba(255, 255, 255, 0.05) !important; color: #f1f5f9 !important; border: 1px solid rgba(255, 255, 255, 0.12) !important; border-radius: 10px !important; font-size: 13.5px !important; font-weight: 700 !important; text-decoration: none !important; transition: all 0.2s !important; }
.btn-hero-secondary:hover { background: rgba(255, 255, 255, 0.1) !important; transform: translateY(-2px) !important; }
.mkt-filters { padding: 22px 24px !important; }
.filter-row { display: grid !important; grid-template-columns: 2fr 1fr 1fr auto !important; gap: 12px !important; margin-bottom: 16px !important; align-items: stretch !important; }
.search-wrap { position: relative !important; }
.search-icon { position: absolute !important; left: 16px !important; top: 50% !important; transform: translateY(-50%) !important; font-size: 15px !important; pointer-events: none !important; opacity: 0.7 !important; }
.filter-row input[type="text"], .filter-select { width: 100% !important; padding: 13px 16px !important; background: rgba(0, 0, 0, 0.35) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important; border-radius: 10px !important; color: #f1f5f9 !important; font-size: 13.5px !important; font-family: inherit !important; outline: none !important; transition: border-color 0.2s, box-shadow 0.2s !important; box-sizing: border-box !important; }
.search-wrap input[type="text"] { padding-left: 46px !important; }
.filter-row input[type="text"]::placeholder { color: #64748b !important; }
.filter-row input[type="text"]:focus, .filter-select:focus { border-color: #38bdf8 !important; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important; }
.filter-select { cursor: pointer !important; -webkit-appearance: none !important; appearance: none !important; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e") !important; background-repeat: no-repeat !important; background-position: right 12px center !important; background-size: 16px !important; padding-right: 40px !important; }
.filter-select option { background: #1e293b !important; color: #f1f5f9 !important; }
.btn-filter { padding: 13px 28px !important; background: linear-gradient(135deg, #0ea5e9, #0284c7) !important; color: #ffffff !important; border: none !important; border-radius: 10px !important; font-size: 13.5px !important; font-weight: 700 !important; cursor: pointer !important; transition: all 0.2s ease !important; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important; white-space: nowrap !important; font-family: inherit !important; }
.btn-filter:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5) !important; }
.filter-chips { display: flex !important; gap: 10px !important; flex-wrap: wrap !important; }
.chip { display: inline-flex !important; align-items: center !important; gap: 8px !important; padding: 8px 16px !important; background: rgba(255, 255, 255, 0.03) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 999px !important; font-size: 12.5px !important; color: #cbd5e1 !important; font-weight: 600 !important; cursor: pointer !important; transition: all 0.2s !important; user-select: none !important; }
.chip:hover { background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.3) !important; }
.chip input[type="checkbox"] { accent-color: #10b981 !important; cursor: pointer !important; margin: 0 !important; }
.product-grid { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important; gap: 20px !important; margin-bottom: 24px !important; }
.product-card { display: flex !important; flex-direction: column !important; overflow: hidden !important; padding: 0 !important; margin-bottom: 0 !important; transition: all 0.3s ease !important; position: relative !important; }
.product-card:hover { transform: translateY(-6px) !important; border-color: rgba(56, 189, 248, 0.4) !important; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5) !important; }
.product-image { display: block !important; position: relative !important; width: 100% !important; aspect-ratio: 4 / 3 !important; overflow: hidden !important; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(56, 189, 248, 0.1)) !important; border-radius: 20px 20px 0 0 !important; }
.product-image img { width: 100% !important; height: 100% !important; object-fit: cover !important; transition: transform 0.5s ease !important; display: block !important; }
.product-card:hover .product-image img { transform: scale(1.08) !important; }
.product-badges { position: absolute !important; top: 12px !important; left: 12px !important; right: 12px !important; display: flex !important; gap: 6px !important; flex-wrap: wrap !important; z-index: 2 !important; }
.badge { display: inline-flex !important; align-items: center !important; padding: 4px 10px !important; border-radius: 999px !important; font-size: 10px !important; font-weight: 800 !important; letter-spacing: 0.5px !important; text-transform: uppercase !important; backdrop-filter: blur(8px) !important; -webkit-backdrop-filter: blur(8px) !important; white-space: nowrap !important; }
.badge.org { background: rgba(16, 185, 129, 0.9) !important; color: #ffffff !important; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4) !important; }
.badge.grade { background: rgba(251, 191, 36, 0.9) !important; color: #1e293b !important; box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4) !important; }
.product-body { display: flex !important; flex-direction: column !important; gap: 10px !important; padding: 18px 20px 20px !important; flex: 1 !important; }
.product-header { display: flex !important; justify-content: space-between !important; align-items: flex-start !important; gap: 10px !important; }
.product-header h4 { font-size: 15px !important; font-weight: 700 !important; color: #f1f5f9 !important; margin: 0 !important; line-height: 1.35 !important; display: -webkit-box !important; -webkit-line-clamp: 2 !important; -webkit-box-orient: vertical !important; overflow: hidden !important; flex: 1 !important; }
.wish-btn { width: 32px !important; height: 32px !important; border-radius: 8px !important; background: rgba(255, 255, 255, 0.05) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; cursor: pointer !important; font-size: 14px !important; display: flex !important; align-items: center !important; justify-content: center !important; transition: all 0.2s !important; flex-shrink: 0 !important; padding: 0 !important; line-height: 1 !important; }
.wish-btn:hover { background: rgba(239, 68, 68, 0.15) !important; border-color: rgba(239, 68, 68, 0.4) !important; transform: scale(1.1) !important; }
.seller-info { display: flex !important; align-items: center !important; gap: 6px !important; font-size: 12px !important; color: #94a3b8 !important; flex-wrap: wrap !important; }
.seller-name { font-weight: 600 !important; color: #cbd5e1 !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; max-width: 130px !important; }
.verified-badge { display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 16px !important; height: 16px !important; background: linear-gradient(135deg, #38bdf8, #0284c7) !important; color: #ffffff !important; border-radius: 50% !important; font-size: 10px !important; font-weight: 800 !important; line-height: 1 !important; flex-shrink: 0 !important; box-shadow: 0 2px 6px rgba(56, 189, 248, 0.4) !important; }
.rating { margin-left: auto !important; font-size: 11.5px !important; font-weight: 700 !important; color: #fbbf24 !important; white-space: nowrap !important; }
.product-price { display: flex !important; align-items: baseline !important; gap: 4px !important; margin-top: 4px !important; }
.product-price strong { font-size: 20px !important; font-weight: 800 !important; color: #10b981 !important; letter-spacing: -0.5px !important; line-height: 1.1 !important; }
.product-price small { font-size: 12px !important; color: #64748b !important; font-weight: 600 !important; }
.product-meta { display: flex !important; flex-wrap: wrap !important; gap: 6px !important; }
.meta-pill { display: inline-flex !important; align-items: center !important; padding: 5px 10px !important; background: rgba(56, 189, 248, 0.08) !important; border: 1px solid rgba(56, 189, 248, 0.2) !important; border-radius: 8px !important; font-size: 11.5px !important; color: #7dd3fc !important; font-weight: 600 !important; }
.btn-view { display: flex !important; align-items: center !important; justify-content: center !important; gap: 6px !important; width: 100% !important; padding: 11px 16px !important; background: rgba(56, 189, 248, 0.1) !important; color: #38bdf8 !important; border: 1px solid rgba(56, 189, 248, 0.3) !important; border-radius: 10px !important; font-size: 13px !important; font-weight: 700 !important; text-decoration: none !important; transition: all 0.2s ease !important; margin-top: auto !important; font-family: inherit !important; }
.btn-view:hover { background: rgba(56, 189, 248, 0.25) !important; color: #ffffff !important; border-color: #38bdf8 !important; transform: translateY(-2px) !important; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3) !important; }
.empty-state-card { grid-column: 1 / -1 !important; padding: 60px 24px !important; text-align: center !important; }
.empty-state-card .empty-icon { font-size: 60px !important; margin-bottom: 16px !important; opacity: 0.6 !important; }
.empty-state-card h3 { font-size: 20px !important; color: #f1f5f9 !important; margin: 0 0 8px 0 !important; font-weight: 700 !important; }
.empty-state-card p { font-size: 14px !important; color: #94a3b8 !important; margin: 0 0 24px 0 !important; max-width: 480px !important; margin-left: auto !important; margin-right: auto !important; line-height: 1.6 !important; }
@media (max-width: 1024px) { .filter-row { grid-template-columns: 1fr 1fr !important; } .search-wrap { grid-column: 1 / -1 !important; } .btn-filter { grid-column: 1 / -1 !important; width: 100% !important; } }
@media (max-width: 768px) { .marketplace-page .main-content { margin-left: 0 !important; padding: 16px !important; padding-top: 72px !important; } .marketplace-page .glass-card { padding: 20px !important; } .mkt-hero { flex-direction: column !important; align-items: flex-start !important; padding: 22px !important; } .mkt-hero h1 { font-size: 22px !important; } .btn-hero-sell { width: 100% !important; justify-content: center !important; } .mkt-stats { gap: 16px !important; } .stat-item strong { font-size: 18px !important; } .filter-row { grid-template-columns: 1fr !important; } .product-grid { grid-template-columns: 1fr !important; } }
@media (max-width: 480px) { .mkt-stats { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important; } }
</style>