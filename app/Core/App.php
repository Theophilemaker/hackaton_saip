<?php
namespace App\Core;

class App {
    protected $controller = 'HomeController';
    protected $method     = 'index';
    protected $params     = [];

    public function __construct() {
        $url = $this->parseUrl();

        // ---- Resolve controller ----
        if (isset($url[0]) && !empty($url[0])) {
            $candidate = ucfirst($url[0]) . 'Controller';
            $file = __DIR__ . '/../Controllers/' . $candidate . '.php';

            if (file_exists($file)) {
                $this->controller = $candidate;
                unset($url[0]);
            }
        }

        // ---- Load controller ----
        $controllerFile = __DIR__ . '/../Controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            http_response_code(404);
            die('Controller not found: ' . htmlspecialchars($this->controller));
        }

        require_once $controllerFile;
        $class = 'App\\Controllers\\' . $this->controller;
        $this->controller = new $class;

        // ---- Resolve method ----
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // ---- Remaining params ----
        $this->params = $url ? array_values($url) : [];

        // ---- Call it ----
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
?>