<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

        <section class="glass-card">
            <div class="card-header"><h3>👥 Create Cooperative</h3></div>
            <form method="POST" action="<?= BASE_URL ?>/cooperative/create" class="alert-form">
                <div class="form-group"><label>Name</label><input type="text" name="name" required placeholder="Kigali Farmers Cooperative"></div>
                <div class="form-group"><label>Registration Number</label><input type="text" name="registration_no"></div>
                <div class="form-group"><label>District ID</label><input type="number" name="district_id"></div>
                <div class="form-group"><label>Sector</label><input type="text" name="sector"></div>
                <div class="form-group"><label>Founded Year</label><input type="number" name="founded_year" value="<?= date('Y') ?>"></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
                <button class="btn-primary">Create Cooperative</button>
            </form>
        </section>
    </main>
</div>