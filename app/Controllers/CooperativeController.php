<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cooperative;
use App\Models\Notification;

class CooperativeController extends Controller {
    private $coop;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->coop = new Cooperative();
    }

    /* ============ LIST ============ */
    public function index() {
        $myCoops = $this->coop->getByUser($_SESSION['user_id']);
        $data = [
            'title'      => 'Cooperative Management',
            'name'       => $_SESSION['name'],
            'my_coops'   => $myCoops,
            'all_coops'  => $this->coop->all(),
        ];
        $this->view('cooperative/index', $data);
    }

    /* ============ CREATE ============ */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $this->coop->create([
                'name'            => trim($_POST['name']),
                'registration_no' => $_POST['registration_no'] ?? null,
                'district_id'     => $_POST['district_id'] ?? null,
                'sector'          => $_POST['sector'] ?? null,
                'cell'            => $_POST['cell'] ?? null,
                'village'         => $_POST['village'] ?? null,
                'chairperson_id'  => $_SESSION['user_id'],
                'founded_year'    => $_POST['founded_year'] ?? date('Y'),
                'description'     => $_POST['description'] ?? '',
            ]);
            // Auto-enroll creator
            $this->coop->addMember($id, $_SESSION['user_id'], 'chair');
            header("Location: " . BASE_URL . "/cooperative/dashboard/" . $id);
            exit;
        }
        $this->view('cooperative/create', ['title'=>'New Cooperative','name'=>$_SESSION['name']]);
    }

    /* ============ DASHBOARD ============ */
    public function dashboard($id) {
        $coop = $this->coop->getById($id);
        if (!$coop) { header("Location: " . BASE_URL . "/cooperative"); exit; }

        $data = [
            'title'     => $coop['name'],
            'name'      => $_SESSION['name'],
            'coop'      => $coop,
            'members'   => $this->coop->getMembers($id),
            'harvests'  => $this->coop->getHarvests($id),
            'stats'     => $this->coop->getStats($id),
            'meetings'  => $this->coop->getMeetings($id),
            'equipment' => $this->coop->getEquipment($id),
            'contributions' => $this->coop->getContributions($id),
        ];
        $this->view('cooperative/dashboard', $data);
    }

    /* ============ MEMBERS ============ */
    public function members($id) {
        $coop = $this->coop->getById($id);
        $data = [
            'title'   => 'Members — ' . $coop['name'],
            'name'    => $_SESSION['name'],
            'coop'    => $coop,
            'members' => $this->coop->getMembers($id),
        ];
        $this->view('cooperative/members', $data);
    }

    public function addMember($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->coop->addMember($id, $_POST['user_id'], $_POST['role'] ?? 'member', $_POST['member_no'] ?? null);
            $notif = new Notification();
            $notif->notify(
                $_POST['user_id'], 'cooperative',
                'Added to Cooperative',
                'You were added to a cooperative as ' . ($_POST['role'] ?? 'member'),
                'green', BASE_URL . '/cooperative/dashboard/' . $id, '👥'
            );
            header("Location: " . BASE_URL . "/cooperative/members/" . $id);
            exit;
        }
    }

    /* ============ HARVESTS ============ */
    public function harvests($id) {
        $coop = $this->coop->getById($id);
        $data = [
            'title'    => 'Harvests — ' . $coop['name'],
            'name'     => $_SESSION['name'],
            'coop'     => $coop,
            'harvests' => $this->coop->getHarvests($id),
        ];
        $this->view('cooperative/harvests', $data);
    }

    public function addHarvest($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->coop->addHarvest([
                'cooperative_id' => $id,
                'crop_id'        => $_POST['crop_id'],
                'season'         => $_POST['season'] ?? 'A',
                'year'           => $_POST['year'] ?? date('Y'),
                'total_yield_kg' => $_POST['total_yield_kg'],
                'total_area_ha'  => $_POST['total_area_ha'] ?? null,
                'quality_grade'  => $_POST['quality_grade'] ?? 'Standard',
                'harvest_date'   => $_POST['harvest_date'] ?? date('Y-m-d'),
                'notes'          => $_POST['notes'] ?? '',
                'created_by'     => $_SESSION['user_id'],
            ]);
            header("Location: " . BASE_URL . "/cooperative/harvests/" . $id);
            exit;
        }
    }

    /* ============ CONTRIBUTIONS ============ */
    public function contributions($id) {
        $coop = $this->coop->getById($id);
        $data = [
            'title'         => 'Contributions — ' . $coop['name'],
            'name'          => $_SESSION['name'],
            'coop'          => $coop,
            'contributions' => $this->coop->getContributions($id),
        ];
        $this->view('cooperative/contributions', $data);
    }

    public function addContribution($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->coop->addContribution([
                'cooperative_id' => $id,
                'user_id'        => $_POST['user_id'],
                'amount_rwf'     => $_POST['amount_rwf'],
                'purpose'        => $_POST['purpose'] ?? 'Monthly contribution',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'status'         => $_POST['status'] ?? 'paid',
                'recorded_by'    => $_SESSION['user_id'],
            ]);
            header("Location: " . BASE_URL . "/cooperative/contributions/" . $id);
            exit;
        }
    }

    /* ============ EQUIPMENT ============ */
    public function equipment($id) {
        $coop = $this->coop->getById($id);
        $data = [
            'title'     => 'Equipment — ' . $coop['name'],
            'name'      => $_SESSION['name'],
            'coop'      => $coop,
            'equipment' => $this->coop->getEquipment($id),
        ];
        $this->view('cooperative/equipment', $data);
    }

    public function addEquipment($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->coop->addEquipment([
                'cooperative_id' => $id,
                'name'           => $_POST['name'],
                'category'       => $_POST['category'] ?? 'other',
                'quantity'       => $_POST['quantity'] ?? 1,
                'condition_status' => $_POST['condition_status'] ?? 'good',
                'purchase_date'  => $_POST['purchase_date'] ?? null,
                'purchase_value_rwf' => $_POST['purchase_value_rwf'] ?? null,
                'location'       => $_POST['location'] ?? null,
                'notes'          => $_POST['notes'] ?? '',
            ]);
            header("Location: " . BASE_URL . "/cooperative/equipment/" . $id);
            exit;
        }
    }

    /* ============ MEETINGS ============ */
    public function meetings($id) {
        $coop = $this->coop->getById($id);
        $data = [
            'title'    => 'Meetings — ' . $coop['name'],
            'name'     => $_SESSION['name'],
            'coop'     => $coop,
            'meetings' => $this->coop->getMeetings($id),
            'members'  => $this->coop->getMembers($id),
        ];
        $this->view('cooperative/meetings', $data);
    }

    public function createMeeting($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $meetingId = $this->coop->createMeeting([
                'cooperative_id' => $id,
                'title'          => trim($_POST['title']),
                'agenda'         => $_POST['agenda'] ?? '',
                'meeting_date'   => $_POST['meeting_date'],
                'location'       => $_POST['location'] ?? '',
                'created_by'     => $_SESSION['user_id'],
            ]);

            // Notify all members
            $notif = new Notification();
            foreach ($this->coop->getMembers($id) as $m) {
                $notif->notify($m['user_id'], 'cooperative',
                    'Meeting Scheduled',
                    $_POST['title'] . ' on ' . $_POST['meeting_date'],
                    'yellow', BASE_URL . '/cooperative/meetings/' . $id, '📅');
            }
            header("Location: " . BASE_URL . "/cooperative/meetings/" . $id);
            exit;
        }
    }
}
?>