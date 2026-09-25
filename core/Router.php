<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $this->compilePattern($path),
            'handler' => $handler,
        ];
        return $this;
    }

    public function get(string $path, array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function compilePattern(string $routePath): string
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z][a-zA-Z0-9_]*)(?::([^}]+))?\}/',
            static function (array $placeholder): string {
                $constraint = $placeholder[2] ?? '';
                return '(?P<' . $placeholder[1] . '>' . ($constraint !== '' ? $constraint : '[^/]+') . ')';
            },
            $routePath
        );

        return '#^' . $pattern . '$#u';
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->getMethod();
        $requestPath = $request->getPath();
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $requestPath, $matches)) {
                continue;
            }
            if ($route['method'] !== $requestMethod) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            $this->invoke($route['handler'], $this->extractParameters($matches), $request);
            return;
        }

        $errorController = new ErrorController($request);
        if ($allowedMethods !== []) {
            header('Allow: ' . implode(', ', array_unique($allowedMethods)));
            $errorController->show(405, 'This action does not support the ' . $requestMethod . ' method.');
            return;
        }
        $errorController->show(404, 'The page you are looking for does not exist or was moved.');
    }

    private function extractParameters(array $matches): array
    {
        $parameters = [];
        foreach ($matches as $groupName => $value) {
            if (is_string($groupName)) {
                $parameters[$groupName] = ctype_digit($value) ? (int) $value : $value;
            }
        }
        return $parameters;
    }

    private function invoke(array $handler, array $parameters, Request $request): void
    {
        [$controllerName, $actionName] = $handler;

        if (!class_exists($controllerName) || !method_exists($controllerName, $actionName)) {
            throw new RuntimeException("Route handler {$controllerName}::{$actionName} does not exist.");
        }

        $controller = new $controllerName($request);
        $controller->$actionName(...array_values($parameters));
    }
}
