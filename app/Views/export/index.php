<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card" style="padding: 30px; background: rgba(30,41,59,0.65); border-radius: 16px; border: 1px solid var(--border);">
            <div class="card-header" style="border-bottom: 1px solid var(--border); padding-bottom: 15px; margin-bottom: 20px;">
                <h1 style="margin: 0; font-size: 24px; color: var(--accent);">🌍 Export Dashboard</h1>
                <p style="color: var(--text-sub); margin-top: 5px;">Manage your international export requests and shipments.</p>
            </div>

            <div style="text-align: center; padding: 50px 20px;">
                <span style="font-size: 60px;">🚢</span>
                <h3 style="margin: 15px 0;">Welcome to the Export Module</h3>
                <p style="color: var(--text-sub); margin-bottom: 25px;">This is the main index page for exports. You can customize this page to show export statistics, active shipments, or a list of buyers.</p>
                
                <a href="<?= BASE_URL ?>/marketplace/exports" class="btn-primary" style="display: inline-block; padding: 12px 24px; border-radius: 8px; background: var(--primary); color: white; text-decoration: none;">
                    View Marketplace Export Requests →
                </a>
            </div>
        </section>
    </main>
</div>