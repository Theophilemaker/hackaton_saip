<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\FarmerProfile;
use App\Models\Recommendation;
use App\Models\NisrDataset;

class AdviceController extends Controller {

    private $profileModel;
    private $recommender;
    private $datasetModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        $this->profileModel = new FarmerProfile();
        $this->recommender  = new Recommendation();
        $this->datasetModel = new NisrDataset();
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN DASHBOARD
       ═══════════════════════════════════════════════════════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];

        $profile = $this->profileModel->get($userId);

        // ⭐ Override district with current global location
        $profile['district'] = current_district();

        $complete = $this->profileModel->isComplete($profile);

        $recommendations = [];
        if ($complete) {
            try {
                $recommendations = $this->recommender->recommendCrops($profile, 3);
            } catch (\Throwable $e) {
                error_log("AdviceController::index recommendations error: " . $e->getMessage());
            }
        }

        $alerts = [];
        try {
            $db = new \App\Core\Database();
            $db->query("SELECT * FROM farmer_alerts
                        WHERE user_id = :u AND is_read = 0
                        ORDER BY created_at DESC LIMIT 5");
            $db->bind(':u', $userId);
            $alerts = $db->resultSet();
        } catch (\Throwable $e) {}

        $this->view('advice/dashboard', [
            'title'            => 'My Advice',
            'name'             => $_SESSION['name'],
            'profile'          => $profile,
            'profile_complete' => $complete,
            'recommendations'  => $recommendations,
            'alerts'           => $alerts,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       CROP ADVISOR
       ═══════════════════════════════════════════════════════════ */
    public function crops() {
        $userId  = (int)$_SESSION['user_id'];
        $profile = $this->profileModel->get($userId);
        $profile['district'] = current_district();

        $complete = $this->profileModel->isComplete($profile);

        $recommendations = [];
        if ($complete) {
            $limit = (int)($_GET['limit'] ?? 5);
            try {
                $recommendations = $this->recommender->recommendCrops($profile, $limit);
            } catch (\Throwable $e) {
                error_log("AdviceController::crops error: " . $e->getMessage());
            }
        }

        $this->view('advice/crops', [
            'title'            => 'What Should I Plant?',
            'name'             => $_SESSION['name'],
            'profile'          => $profile,
            'profile_complete' => $complete,
            'recommendations'  => $recommendations,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       PROFILE EDIT
       ═══════════════════════════════════════════════════════════ */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->profileModel->save($_SESSION['user_id'], [
                'district'                 => $_POST['district'] ?? current_district(),
                'sector'                   => $_POST['sector'] ?? null,
                'cell'                     => $_POST['cell'] ?? null,
                'province'                 => $_POST['province'] ?? null,
                'land_size_ha'             => (float)($_POST['land_size_ha'] ?? 1),
                'primary_crops'            => array_filter(explode(',', $_POST['primary_crops'] ?? '')),
                'soil_type'                => $_POST['soil_type'] ?? 'loam',
                'has_irrigation'           => !empty($_POST['has_irrigation']),
                'farming_experience_years' => (int)($_POST['farming_experience_years'] ?? 0),
            ]);

            // Update global location if district changed
            if (!empty($_POST['district'])) {
                $_SESSION['current_district'] = trim($_POST['district']);

                try {
                    $db = new \App\Core\Database();
                    $db->query("UPDATE users SET current_district = :d, location_updated_at = NOW() WHERE id = :id");
                    $db->bind(':d', trim($_POST['district']));
                    $db->bind(':id', (int)$_SESSION['user_id']);
                    $db->execute();
                } catch (\Throwable $e) {}
            }

            $_SESSION['flash_success'] = 'Profile saved! Loading your recommendations…';
            header("Location: " . BASE_URL . "/advice");
            exit;
        }

        $profile = $this->profileModel->get($_SESSION['user_id']);
        $profile['district'] = current_district();

        $this->view('advice/profile', [
            'title'   => 'My Farmer Profile',
            'name'    => $_SESSION['name'],
            'profile' => $profile,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       BENCHMARK
       ═══════════════════════════════════════════════════════════ */
    public function benchmark() {
        $profile = $this->profileModel->get($_SESSION['user_id']);
        $profile['district'] = current_district();

        $crop = $_GET['crop'] ?? null;

        $data = null;
        if ($crop && !empty($profile['district'])) {
            try {
                $data = $this->recommender->benchmark($profile, $crop);
            } catch (\Throwable $e) {
                error_log("benchmark error: " . $e->getMessage());
            }
        }

        $this->view('advice/benchmark', [
            'title'   => 'Benchmark',
            'name'    => $_SESSION['name'],
            'profile' => $profile,
            'crops'   => $this->availableCrops(),
            'crop'    => $crop,
            'data'    => $data,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       LEAGUE TABLE
       ═══════════════════════════════════════════════════════════ */
    public function league() {
        $crop    = $_GET['crop'] ?? 'Maize';
        $profile = $this->profileModel->get($_SESSION['user_id']);
        $profile['district'] = current_district();

        $table = [];
        try {
            $table = $this->recommender->leagueTable($crop);
        } catch (\Throwable $e) {
            error_log("league error: " . $e->getMessage());
        }

        $this->view('advice/league', [
            'title'   => 'District League',
            'name'    => $_SESSION['name'],
            'crop'    => $crop,
            'table'   => $table,
            'profile' => $profile,
            'crops'   => $this->availableCrops(),
        ]);
    }

    /* ═══════════════════════════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════════════════════════ */
    private function availableCrops(): array {
        try {
            $db = new \App\Core\Database();
            $db->query("SELECT DISTINCT crop FROM market_prices ORDER BY crop");
            $rows = $db->resultSet();
            if (!empty($rows)) return array_column($rows, 'crop');
        } catch (\Throwable $e) {}

        return ['Maize', 'Potatoes', 'Beans', 'Rice', 'Tomatoes', 'Coffee', 'Tea', 'Bananas', 'Cassava'];
    }
}