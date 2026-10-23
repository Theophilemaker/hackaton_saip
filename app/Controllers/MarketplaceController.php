<?php
namespace App\Controllers;

use App\Core\Controller;

class MarketplaceController extends Controller {

    private $db = null;

    public function __construct() {
        try {
            $this->db = new \App\Core\Database();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    /* ============================================================
     *  BROWSE PRODUCTS
     * ============================================================ */
    public function index() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $products = [];
        $categories = [];

        if ($this->db) {
            try {
                $this->db->query("
                    SELECT p.*, s.business_name, s.is_verified, s.rating AS seller_rating,
                           u.first_name, u.last_name
                    FROM products p
                    JOIN sellers s ON s.id = p.seller_id
                    JOIN users u ON u.id = s.user_id
                    WHERE p.status = 'active'
                    ORDER BY p.created_at DESC
                    LIMIT 60
                ");
                $products = $this->db->resultSet() ?: [];

                $this->db->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name");
                $categories = $this->db->resultSet() ?: [];
            } catch (\Throwable $e) {
                $products = [];
                $categories = [];
            }
        }

        $data = [
            'title'      => 'Marketplace',
            'name'       => $_SESSION['name'] ?? 'User',
            'products'   => $products,
            'categories' => $categories,
            'filters'    => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/index.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/index', $data);
        } else {
            $this->fallback('Marketplace', $data);
        }
    }

    /* ============================================================
     *  VIEW SINGLE PRODUCT
     * ============================================================ */
    public function product($id = null) {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $product = null;
        if ($this->db && $id) {
            try {
                $this->db->query("
                    SELECT p.*, s.business_name, s.is_verified, s.rating AS seller_rating
                    FROM products p
                    JOIN sellers s ON s.id = p.seller_id
                    WHERE p.id = :id LIMIT 1
                ");
                $this->db->bind(':id', (int)$id);
                $product = $this->db->single();
            } catch (\Throwable $e) {
                $product = null;
            }
        }

        if (!$product) {
            header("Location: " . BASE_URL . "/index.php?url=marketplace");
            exit;
        }

        $data = [
            'title'    => $product['title'] ?? 'Product',
            'name'     => $_SESSION['name'] ?? 'User',
            'product'  => $product,
            'reviews'  => [],
            'ai_price' => [
                'suggested_price' => (float)($product['price_per_unit'] ?? 0),
                'min_price'       => (float)($product['price_per_unit'] ?? 0) * 0.9,
                'max_price'       => (float)($product['price_per_unit'] ?? 0) * 1.1,
                'confidence'      => 0.75,
                'basis'           => 'Based on current listings',
            ],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/product.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/product', $data);
        } else {
            $this->fallback('Product: ' . ($product['title'] ?? ''), $data);
        }
    }

    /* ============================================================
     *  SELLER DASHBOARD
     * ============================================================ */
    public function sell() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $data = [
            'title'      => 'Seller Dashboard',
            'name'       => $_SESSION['name'] ?? 'User',
            'seller'     => null,
            'products'   => [],
            'orders'     => [],
            'stats'      => ['active_products' => 0, 'total_orders' => 0, 'revenue' => 0, 'units_sold' => 0],
            'categories' => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/seller.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/seller', $data);
        } else {
            $this->fallback('Seller Dashboard', $data);
        }
    }

    /* ============================================================
     *  ADD PRODUCT
     * ============================================================ */
    public function newProduct() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle insert here if desired — for now redirect
            header("Location: " . BASE_URL . "/index.php?url=marketplace/sell");
            exit;
        }

        $data = [
            'title'      => 'Add Product',
            'name'       => $_SESSION['name'] ?? 'User',
            'categories' => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/new_product.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/new_product', $data);
        } else {
            $this->fallback('Add Product', $data);
        }
    }

    /* ============================================================
     *  MY ORDERS
     * ============================================================ */
    public function myOrders() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $data = [
            'title'  => 'My Orders',
            'name'   => $_SESSION['name'] ?? 'User',
            'orders' => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/my_orders.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/my_orders', $data);
        } else {
            $this->fallback('My Orders', $data);
        }
    }

    /* ============================================================
     *  WISHLIST
     * ============================================================ */
    public function wishlist() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $data = [
            'title'    => 'Wishlist',
            'name'     => $_SESSION['name'] ?? 'User',
            'products' => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/wishlist.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/wishlist', $data);
        } else {
            $this->fallback('Wishlist', $data);
        }
    }

    public function toggleWishlist($productId = null) {
        header('Content-Type: application/json');
        echo json_encode(['result' => 'added']);
        exit;
    }

    /* ============================================================
     *  EXPORT REQUESTS
     * ============================================================ */
    public function exports() {
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/index.php?url=auth/login");
            exit;
        }

        $data = [
            'title'      => 'Export Requests',
            'name'       => $_SESSION['name'] ?? 'User',
            'requests'   => [],
            'categories' => [],
        ];

        $viewFile = __DIR__ . '/../Views/marketplace/exports.php';
        if (file_exists($viewFile)) {
            $this->view('marketplace/exports', $data);
        } else {
            $this->fallback('Export Requests', $data);
        }
    }

    public function newExport() {
        header("Location: " . BASE_URL . "/index.php?url=marketplace/exports");
        exit;
    }

    /* ============================================================
     *  ORDERS — action stubs
     * ============================================================ */
    public function order($productId = null) {
        header("Location: " . BASE_URL . "/index.php?url=marketplace");
        exit;
    }

    public function myOrder($id = null) {
        header("Location: " . BASE_URL . "/index.php?url=marketplace/myOrders");
        exit;
    }

    public function pay($orderId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'ref' => 'MOCK-' . time()]);
        exit;
    }

    public function updateDelivery($deliveryId = null) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    public function review($orderId = null) {
        header("Location: " . BASE_URL . "/index.php?url=marketplace/myOrders");
        exit;
    }

    public function negotiate($productId = null) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Negotiation coming soon']);
        exit;
    }

    /* ============================================================
     *  FALLBACK PAGE
     * ============================================================ */
    private function fallback($title, $data) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title>';
        echo '<style>body{background:#081C15;color:#fff;font-family:Arial;padding:40px;line-height:1.8;}';
        echo 'h2{color:#84CC16;}a{display:inline-block;padding:12px 24px;background:#16A34A;';
        echo 'color:white;text-decoration:none;border-radius:10px;margin:8px 4px;}</style></head><body>';
        echo '<h2>✅ ' . htmlspecialchars($title) . '</h2>';
        echo '<p>The controller works. Create the view file to customize this page.</p>';
        echo '<a href="' . BASE_URL . '/index.php?url=dashboard">→ Dashboard</a>';
        echo '<a href="' . BASE_URL . '/index.php?url=marketplace">🛒 Marketplace</a>';
        echo '<a href="' . BASE_URL . '/index.php?url=weather">🌤️ Weather</a>';
        echo '</body></html>';
        exit;
    }
}
?>