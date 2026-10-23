<?php
namespace App\Controllers;

use App\Core\Controller;

class MarketForecastController extends Controller {
    public function __construct() {
        if (empty($_SESSION['user_id'])) { header("Location: " . BASE_URL . "/auth/login"); exit(); }
    }

    public function index() {
        $this->view('market/forecast', [
            'title' => 'Market Price Forecaster',
            'name'  => $_SESSION['name'] ?? 'User',
            'crops' => ['Maize', 'Beans', 'Rice', 'Irish Potato', 'Coffee']
        ]);
    }

    public function predict() {
        header('Content-Type: application/json; charset=utf-8');
        
        $crop = $_POST['crop'] ?? 'Maize';
        
        // Simulated NISR historical CPI data (Last 6 months) + Base prices
        $base_prices = [
            'Maize' => [320, 340, 350, 380, 390, 370],
            'Beans' => [650, 680, 700, 720, 780, 750],
            'Rice'  => [800, 820, 850, 860, 880, 900],
            'Irish Potato' => [280, 300, 320, 350, 330, 310],
            'Coffee'=> [1100, 1150, 1200, 1180, 1220, 1250]
        ];
        
        $history = $base_prices[$crop] ?? $base_prices['Maize'];
        $current_price = end($history);
        
        // Simple Moving Average + Trend line for 3-month forecast
        $trend = ($history[5] - $history[2]) / 3;
        $forecast = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $predicted = $current_price + ($trend * $i) + (rand(-20, 20)); // small seasonal variance
            $forecast[] = round(max(100, $predicted));
        }
        
        $action = $trend > 0 ? "HOLD: Prices are rising. Wait to sell." : "SELL: Prices are falling. Sell now.";
        
        echo json_encode([
            'success' => true,
            'crop' => $crop,
            'history' => $history,
            'forecast' => $forecast,
            'action' => $action,
            'trend' => $trend > 0 ? 'up' : 'down'
        ]);
        exit;
    }
}