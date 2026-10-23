<?php
namespace App\Models;

use App\Core\Database;

class Marketplace {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getCategories() {
        try {
            $this->db->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function searchProducts($filters = []) {
        try {
            $this->db->query("SELECT p.*, s.business_name, s.is_verified, s.rating AS seller_rating,
                                     u.first_name, u.last_name
                              FROM products p
                              JOIN sellers s ON s.id = p.seller_id
                              JOIN users u ON u.id = s.user_id
                              WHERE p.status = 'active'
                              ORDER BY p.created_at DESC
                              LIMIT 60");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProduct($id) {
        try {
            $this->db->query("SELECT p.*, s.business_name, s.is_verified, s.rating AS seller_rating,
                                     u.first_name, u.last_name
                              FROM products p
                              JOIN sellers s ON s.id = p.seller_id
                              JOIN users u ON u.id = s.user_id
                              WHERE p.id = :id");
            $this->db->bind(':id', (int)$id);
            return $this->db->single();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getWishlist($userId) {
        try {
            $this->db->query("SELECT p.* FROM wishlists w
                              JOIN products p ON p.id = w.product_id
                              WHERE w.user_id = :u
                              ORDER BY w.created_at DESC");
            $this->db->bind(':u', (int)$userId);
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function toggleWishlist($userId, $productId) {
        return 'added';
    }

    public function getExportRequests() {
        try {
            $this->db->query("SELECT er.*, u.first_name, u.last_name,
                                     pc.name AS category_name, pc.icon AS category_icon
                              FROM export_requests er
                              JOIN users u ON u.id = er.buyer_id
                              LEFT JOIN product_categories pc ON pc.id = er.category_id
                              WHERE er.status = 'open'
                              ORDER BY er.created_at DESC");
            $rows = $this->db->resultSet();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getSellerByUser($userId) { return null; }
    public function getSellerProducts($sellerId) { return []; }
    public function getSellerOrders($sellerId) { return []; }
    public function getBuyerOrders($userId) { return []; }
    public function getOrder($id) { return null; }
    public function getSellerStats($sellerId) {
        return ['active_products' => 0, 'total_orders' => 0, 'revenue' => 0, 'units_sold' => 0];
    }
    public function getPriceRecommendation($cropId, $districtId = null) {
        return ['suggested_price' => 0, 'min_price' => 0, 'max_price' => 0, 'confidence' => 0, 'basis' => 'No data'];
    }
    public function getProductReviews($productId) { return []; }
    public function createProduct($data) { return false; }
    public function createOrder($data) { return false; }
    public function recordPayment($orderId, $method, $amount, $phone = null) { return false; }
    public function updateOrderStatus($id, $status) { return false; }
    public function updateDeliveryStatus($id, $status, $userId) { return false; }
    public function getDeliveryEvents($id) { return []; }
    public function addReview($data) { return false; }
    public function startNegotiation($p, $b, $s, $price, $qty) { return false; }
    public function createExportRequest($data) { return false; }
}
?>