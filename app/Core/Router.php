<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groups = [];
    private string $currentPrefix = '';
    private array $currentMiddleware = [];
    private string $currentNamespace = '';

    public function __construct()
    {
    }

    public function group(array $options, callable $cb): void
    {
        $prevPrefix      = $this->currentPrefix;
        $prevMiddleware  = $this->currentMiddleware;
        $prevNamespace   = $this->currentNamespace;

        if (isset($options['prefix'])) {
            $this->currentPrefix = rtrim($prevPrefix, '/') . '/' . trim($options['prefix'], '/');
        }
        if (isset($options['middleware'])) {
            $this->currentMiddleware = array_merge($prevMiddleware, self::flattenMiddleware($options['middleware']));
        }
        if (isset($options['namespace'])) {
            $this->currentNamespace = rtrim($prevNamespace, '\\') . '\\' . trim($options['namespace'], '\\');
        }
        $cb($this);
        $this->currentPrefix     = $prevPrefix;
        $this->currentMiddleware = $prevMiddleware;
        $this->currentNamespace  = $prevNamespace;
    }

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function any(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET',  $path, $handler, $middleware);
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middleware): void
    {
        $fullPath = $this->currentPrefix === '' ? '/' . ltrim($path, '/')
            : rtrim($this->currentPrefix, '/') . '/' . ltrim($path, '/');
        $fullPath = $fullPath === '' ? '/' : $fullPath;
        $mergedMiddleware = array_merge($this->currentMiddleware, self::flattenMiddleware($middleware));
        $mergedMiddleware = array_values(array_filter(array_unique($mergedMiddleware), 'is_string'));
        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'handler'    => $handler,
            'middleware' => $mergedMiddleware,
            'namespace'  => $this->currentNamespace,
        ];
    }

    private static function flattenMiddleware(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_string($value)) {
            return [$value];
        }
        if (!is_array($value)) {
            return [];
        }
        $out = [];
        if (isset($value['middleware']) && (is_string($value['middleware']) || is_array($value['middleware']))) {
            $out = array_merge($out, self::flattenMiddleware($value['middleware']));
            unset($value['middleware']);
            if (count($value) === 0) {
                return array_values(array_filter($out, 'is_string'));
            }
        }
        foreach ($value as $k => $v) {
            if (is_int($k)) {
                $out = array_merge($out, self::flattenMiddleware($v));
            }
        }
        return array_values(array_filter($out, 'is_string'));
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $uri = '/' . ltrim($uri, '/');

        // Normalizar: quitar /public/ si viene
        if (str_starts_with($uri, '/public/')) {
            $uri = substr($uri, strlen('/public'));
            if ($uri === '') {
                $uri = '/';
            }
        }

        foreach ($this->routes as $r) {
            if (strtoupper($r['method']) !== strtoupper($method)) {
                continue;
            }
            if ($this->matchRoute($r['path'], $uri, $params)) {
                foreach ($r['middleware'] as $mw) {
                    $this->callMiddleware($mw);
                }
                $this->callHandler($r['handler'], $params, $r['namespace']);
                return;
            }
        }
        Response::notFound();
    }

    private function matchRoute(string $pattern, string $uri, ?array &$params): bool
    {
        $params = [];
        $patternParts = array_values(array_filter(explode('/', $pattern)));
        $uriParts     = array_values(array_filter(explode('/', $uri)));
        if (count($patternParts) !== count($uriParts)) {
            return false;
        }
        foreach ($patternParts as $i => $p) {
            if (str_starts_with($p, '{') && str_ends_with($p, '}')) {
                $name = trim($p, '{}');
                $optional = false;
                if (str_ends_with($name, '?')) {
                    $name = rtrim($name, '?');
                    $optional = true;
                }
                $v = $uriParts[$i] ?? null;
                if ($v === null && !$optional) {
                    return false;
                }
                $params[$name] = $v;
            } else {
                if (($uriParts[$i] ?? '') !== $p) {
                    return false;
                }
            }
        }
        return true;
    }

    private function callMiddleware(string $mw): void
    {
        switch ($mw) {
            case 'auth':
                Auth::requireAuth();
                break;
            case 'admin':
                Auth::requireRole('admin');
                break;
            case 'editor_or_admin':
                Auth::requireAuth();
                if (!Auth::is('admin') && !Auth::is('editor')) {
                    Session::flash('error', 'Permisos insuficientes.');
                    redirect('/admin');
                }
                break;
            case 'csrf':
                $name = config_val('security.csrf_token_name', '_csrf_token');
                $token = $_POST[$name] ?? (getallheaders()['X-Csrf-Token'] ?? null);
                if (!is_string($token) || !CSRF::check($token)) {
                    Session::flash('error', 'Token de seguridad inválido o expirado.');
                    redirect_back('/');
                }
                break;
            default:
                if (str_starts_with($mw, 'can:')) {
                    $action = substr($mw, 4);
                    Auth::requireCan($action);
                }
        }
    }

    private function callHandler(mixed $handler, array $params, string $namespace): void
    {
        // Flashear datos previos
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $action] = explode('@', $handler, 2);
            if ($namespace !== '' && !str_contains($controller, '\\')) {
                $controller = $namespace . '\\' . $controller;
            }
            if (!str_contains($controller, '\\')) {
                $controller = 'App\\Controllers\\' . $controller;
            }
            if (!class_exists($controller)) {
                http_response_code(500);
                die('Controlador no existe: ' . $controller);
            }
            $inst = new $controller();
            if (!method_exists($inst, $action)) {
                http_response_code(500);
                die('Método no existe: ' . $controller . '::' . $action);
            }
            call_user_func_array([$inst, $action], [$params]);
            return;
        }
        if (is_callable($handler)) {
            call_user_func_array($handler, [$params]);
            return;
        }
        http_response_code(500);
        die('Handler no válido.');
    }
}
