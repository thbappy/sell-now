<?php

namespace SellNow\Foundation;

/**
 * Router: Simple but extensible request router
 * Responsibility: Map HTTP paths to controller methods, handle parameters
 */
class Router
{
    private array $routes = [];
    private Request $request;
    private Container $container;

    public function __construct(Request $request, Container $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, string|callable $handler): void
    {
        $this->registerRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string|callable $handler): void
    {
        $this->registerRoute('POST', $path, $handler);
    }

    /**
     * Register any HTTP method
     */
    public function any(string $path, string|callable $handler): void
    {
        $this->registerRoute('ANY', $path, $handler);
    }

    private function registerRoute(string $method, string $path, string|callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatch the current request to a handler
     */
    public function dispatch(): void
    {
        $currentMethod = $this->request->getMethod();
        $currentPath = $this->request->getPath();

        foreach ($this->routes as $route) {
            if (($route['method'] !== 'ANY' && $route['method'] !== $currentMethod)) {
                continue;
            }

            if ($this->matchPath($route['path'], $currentPath, $params)) {
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // Not found
        http_response_code(404);
        echo "404 - Page Not Found";
        exit;
    }

    /**
     * Match a route pattern against the actual path
     * Supports dynamic segments like {id} or {username}
     */
    private function matchPath(string $pattern, string $path, &$params = []): bool
    {
        $params = [];

        // Convert pattern to regex
        // Replace {param} with regex pattern for capturing
        $regexPattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        // Escape forward slashes for use in preg_match delimiter
        $regexPattern = str_replace('/', '\/', $regexPattern);
        
        if (preg_match('/^' . $regexPattern . '$/', $path, $matches)) {
            // Extract named groups only
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Call a handler (controller method or callable)
     */
    private function callHandler(string|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            // Direct callable
            call_user_func_array($handler, [$this->request, ...$params]);
            return;
        }

        // String format: "ControllerClass@methodName"
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerClass, $methodName] = explode('@', $handler);

            // Try to get controller from container (short name: 'AuthController')
            try {
                $controller = $this->container->make($controllerClass);
                call_user_func_array([$controller, $methodName], [$this->request, ...$params]);
                return;
            } catch (\Exception $e) {
                // If container fails, try full namespace
                $fullClass = "\\SellNow\\Controllers\\" . $controllerClass;
                if (class_exists($fullClass)) {
                    $controller = new $fullClass();
                    call_user_func_array([$controller, $methodName], [$this->request, ...$params]);
                    return;
                }
            }
        }

        throw new \Exception("Invalid handler: " . json_encode($handler));
    }
}
