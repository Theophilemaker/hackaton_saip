<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller {
    private $notif;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
        $this->notif = new Notification();
    }

    public function index() {
        $data = [
            'title'  => 'Notifications',
            'name'   => $_SESSION['name'],
            'notifs' => $this->notif->getAll($_SESSION['user_id']),
        ];
        $this->view('notifications/index', $data);
    }

    /**
     * AJAX — bell dropdown feed.
     */
    public function feed() {
        header('Content-Type: application/json');
        $unread = $this->notif->getUnread($_SESSION['user_id'], 10);
        $count  = $this->notif->countUnread($_SESSION['user_id']);
        echo json_encode(['unread'=>$unread, 'count'=>$count]);
        exit;
    }

    public function markRead($id) {
        header('Content-Type: application/json');
        $this->notif->markRead($id, $_SESSION['user_id']);
        echo json_encode(['ok'=>true]);
        exit;
    }

    public function markAllRead() {
        header('Content-Type: application/json');
        $this->notif->markAllRead($_SESSION['user_id']);
        echo json_encode(['ok'=>true]);
        exit;
    }
}
?>