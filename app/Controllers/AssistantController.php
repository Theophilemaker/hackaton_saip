<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\FarmerProfile;
use App\Models\FarmAssistant;

class AssistantController extends Controller {

    private $profileModel;
    private $assistant;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }

        try {
            $this->profileModel = new FarmerProfile();
        } catch (\Throwable $e) {
            error_log("ProfileModel error: " . $e->getMessage());
            $this->profileModel = null;
        }

        $this->assistant = new FarmAssistant();
    }

    /* ═══════════ CHAT PAGE ═══════════ */
    public function index() {
        $userId = (int)$_SESSION['user_id'];

        $profile = $this->profileModel
            ? $this->profileModel->get($userId)
            : ['first_name' => 'Farmer'];

        $sessionId = $this->assistant->getOrCreateSession($userId);
        $history   = $this->assistant->getHistory($sessionId);

        // Seed with a greeting if empty
        if (empty($history)) {
            $this->assistant->saveMessage(
                $sessionId, 'assistant',
                "👋 **Muraho!** I'm your AI farm assistant.\n\n"
                . "Ask me about crops, prices, or farming — in **English** or **Kinyarwanda**.",
                'greeting'
            );
            $history = $this->assistant->getHistory($sessionId);
        }

        // Quick replies
        $quickReplies = [];
        try {
            $db = new \App\Core\Database();
            $db->query("SELECT * FROM assistant_quick_replies ORDER BY sort_order LIMIT 8");
            $quickReplies = $db->resultSet();
        } catch (\Throwable $e) {}

        $this->view('assistant/index', [
            'title'         => 'Farm Assistant',
            'name'          => $_SESSION['name'] ?? 'User',
            'profile'       => $profile,
            'session_id'    => $sessionId,
            'history'       => $history,
            'quick_replies' => $quickReplies,
        ]);
    }

    /* ═══════════ SEND MESSAGE ═══════════ */
    public function send() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $userId  = (int)$_SESSION['user_id'];
            $message = trim($_POST['message'] ?? '');

            if ($message === '') {
                echo json_encode(['success' => false, 'error' => 'Empty message']);
                exit;
            }

            $sessionId = $this->assistant->getOrCreateSession($userId);
            $profile   = $this->profileModel
                ? $this->profileModel->get($userId)
                : [];

            $this->assistant->saveMessage($sessionId, 'user', $message);

            $response = $this->assistant->respond($userId, $message, $profile ?: []);

            $this->assistant->saveMessage($sessionId, 'assistant',
                $response['reply'], $response['intent']);

            echo json_encode([
                'success' => true,
                'reply'   => $response['reply'],
                'intent'  => $response['intent'],
                'actions' => $response['actions'] ?? [],
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            error_log("AssistantController::send error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error'   => 'Server error: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /* ═══════════ RESET ═══════════ */
    public function reset() {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $userId = (int)$_SESSION['user_id'];
            $sessionId = $this->assistant->getOrCreateSession($userId);
            $this->assistant->resetSession($sessionId);
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}