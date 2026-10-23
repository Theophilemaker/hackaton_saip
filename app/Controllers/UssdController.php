<?php
namespace App\Controllers;

use App\Core\Controller;

class UssdController extends Controller {
    public function index() {
        // No login required for this demo page (makes it easy for judges to test)
        $this->view('ussd/simulator', [
            'title' => 'USSD Simulator',
            'name' => 'Guest'
        ]);
    }

    public function handle() {
        header('Content-Type: application/json; charset=utf-8');
        
        $input = trim($_POST['input'] ?? '');
        $step = (int)($_POST['step'] ?? 0);

        $response = "";
        $nextStep = $step + 1;

        if ($step === 0 || $input === '') {
            $response = "Welcome to SAIP Agri-Hub\n1. Check Market Prices\n2. Get Weather Forecast\n3. Yield Tips\n4. Exit";
            $nextStep = 1;
        } 
        elseif ($step === 1) {
            if ($input === '1') {
                $response = "Select Crop:\n1. Maize\n2. Beans\n3. Irish Potato";
                $nextStep = 2; // Market prices
            } elseif ($input === '2') {
                $response = "Weather for Kigali: 24C, Partly Cloudy. Rain expected Thursday. Good time to plant.";
                $nextStep = 99; // End
            } elseif ($input === '3') {
                $response = "Tip: Apply lime to acidic soils (pH < 5.5) to increase maize yield by up to 35%.";
                $nextStep = 99; // End
            } else {
                $response = "Invalid choice.\nReply 1, 2, or 3.";
                $nextStep = 1;
            }
        }
        elseif ($step === 2) {
            if ($input === '1') $response = "Maize price: 350 RWF/kg. Trend: Rising. Hold your stock for 2 weeks.";
            elseif ($input === '2') $response = "Beans price: 700 RWF/kg. Trend: Stable.";
            else $response = "Irish Potato price: 320 RWF/kg. Trend: Falling. Sell soon.";
            $nextStep = 99;
        }

        // USSD responses must be under 160 characters
        if (strlen($response) > 160) {
            $response = substr($response, 0, 157) . "...";
        }

        echo json_encode([
            'response' => $response,
            'next_step' => $nextStep,
            'is_end' => $nextStep === 99
        ]);
        exit;
    }
}