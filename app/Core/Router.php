<?php

namespace App\Core;

/**
 * موجّه الطلبات (Router)
 * يحوّل الروابط النظيفة إلى وحدات تحكم
 */
class Router
{
    private static array $routes = [];

    public static function get(string $path, string $controller, string $action): void
    {
        self::add('GET', $path, $controller, $action);
    }

    public static function post(string $path, string $controller, string $action): void
    {
        self::add('POST', $path, $controller, $action);
    }

    private static function add(string $method, string $path, string $controller, string $action): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
    }

    /** تنفيذ التوجيه للطلب الحالي */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // إزالة مجلد المشروع من المسار (عند التشغيل في مجلد فرعي)
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($baseDir !== '' && $baseDir !== '/') {
            if (strncmp($uri, $baseDir, strlen($baseDir)) === 0) {
                $uri = substr($uri, strlen($baseDir));
            }
        }
        $uri = '/' . ltrim($uri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . str_replace('#', '\#', $pattern) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $controllerClass = 'App\\Controllers\\' . $route['controller'];
                if (!class_exists($controllerClass)) {
                    self::notFound();
                }
                /** @var Controller $controller */
                $controller = new $controllerClass();
                $controller->run($route['action'], array_values($params));
                return;
            }
        }

        self::notFound();
    }

    private static function notFound(): void
    {
        http_response_code(404);
        // عرض صفحة 404 عربية (دون الاعتماد على قاعدة البيانات)
        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents(BASE_PATH . '/views/errors/404.php');
        exit;
    }
}
