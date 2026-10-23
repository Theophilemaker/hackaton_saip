<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar glass-panel">
        <div class="logo">
            <h3>SAIP</h3>
        </div>
        <nav>
            <a href="#" class="active">Dashboard</a>
            <a href="#">Weather</a>
            <a href="#">Crops</a>
            <a href="#">Marketplace</a>
            <a href="#">AI Assistant</a>
        </nav>
        <div class="user-profile">
            <span><?= $data['name'] ?></span>
            <a href="<?= BASE_URL ?>/auth/logout" class="logout-btn">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar glass-panel">
            <h2>Farmer Dashboard</h2>
            <div class="user-actions">
                <span>🌤️ 24°C Kigali</span>
            </div>
        </header>

        <div class="content-grid">
            <div class="glass-card widget">
                <h3>Welcome, <?= $data['name'] ?>!</h3>
                <p>Your smart farming journey starts here.</p>
            </div>
            <div class="glass-card widget">
                <h3>Weather Intelligence</h3>
                <p>Today is partly sunny. Good for planting.</p>
            </div>
            <div class="glass-card widget">
                <h3>AI Recommendation</h3>
                <p>Soil moisture is optimal. No irrigation needed today.</p>
            </div>
        </div>
    </main>
</div>