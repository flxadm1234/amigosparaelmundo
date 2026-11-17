<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;

class Auth
{
    public const USER_SESSION = '_auth_user_id';
    public const ROLE_SESSION = '_auth_role';

    public static function check(): bool
    {
        return Session::has(self::USER_SESSION) && is_int(Session::get(self::USER_SESSION));
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $id = (int) Session::get(self::USER_SESSION);
        $u = User::find($id);
        if (!$u || (int)$u['status'] !== 1) {
            self::logout();
            return null;
        }
        return $u;
    }

    public static function role(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $u = self::user();
        if ($u) {
            return Role::find((int)$u['role_id']);
        }
        return null;
    }

    public static function is(string $role_slug): bool
    {
        $r = self::role();
        return $r && is_array($r) && ($r['slug'] ?? '') === $role_slug;
    }

    public static function can(string $action): bool
    {
        if (!self::check()) {
            return false;
        }
        if (self::is('admin')) {
            return true;
        }
        $editorBase = [
            'dashboard', 'audit_view',
            'news', 'sections', 'tags', 'flyers', 'videos', 'podcast', 'radio', 'media', 'pages', 'ads',
            'news.create', 'news.edit', 'news.delete', 'news.publish',
            'sections.create', 'sections.edit', 'sections.delete',
            'tags.create', 'tags.edit', 'tags.delete',
            'flyers.create', 'flyers.edit', 'flyers.delete',
            'videos.create', 'videos.edit', 'videos.delete', 'videos.publish',
            'podcast.create', 'podcast.edit', 'podcast.delete', 'podcast.publish',
            'radio.create', 'radio.edit', 'radio.delete',
            'pages.create', 'pages.edit', 'pages.delete', 'pages.publish',
            'ads.create', 'ads.edit', 'ads.delete',
        ];
        $editorPrefixes = [
            'news', 'sections', 'tags', 'flyers', 'videos', 'podcast',
            'radio', 'media', 'pages', 'ads',
        ];
        if (self::is('editor')) {
            if (in_array($action, $editorBase, true)) {
                return true;
            }
            foreach ($editorPrefixes as $prefix) {
                if (str_starts_with($action, $prefix . '.')) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function attempt(string $email, string $password, ?string &$error = null): bool
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        $ip  = $cfg['security'];
        $ipAddr = self::ip();

        // Rate limit
        $attempts = self::getLoginAttempts($ipAddr, $email);
        if ($attempts['locked'] !== null && $attempts['locked'] > date('Y-m-d H:i:s')) {
            $error = 'Demasiados intentos fallidos. Intenta nuevamente en unos minutos.';
            return false;
        }

        $user = User::findByEmail($email);
        if (!$user || (int)$user['status'] !== 1) {
            self::registerFailedAttempt($ipAddr, $email, (int)$attempts['attempts']);
            $error = 'Credenciales inválidas.';
            return false;
        }
        if (!password_verify($password, (string)$user['password_hash'])) {
            self::registerFailedAttempt($ipAddr, $email, (int)$attempts['attempts']);
            $error = 'Credenciales inválidas.';
            return false;
        }
        // Rehash si es necesario
        if (password_needs_rehash((string)$user['password_hash'], PASSWORD_DEFAULT)) {
            User::update((int)$user['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
        }
        // Exito
        self::login($user);
        self::clearLoginAttempts($ipAddr, $email);
        AuditLog::record('login', 'users', (string)$user['id'], json_encode(['email' => $email]));
        return true;
    }

    public static function login(array $user): void
    {
        Session::set(self::USER_SESSION, (int)$user['id']);
        Session::set(self::ROLE_SESSION, (int)$user['role_id']);
        CSRF::regenerate();
    }

    public static function logout(): void
    {
        $userId = Session::get(self::USER_SESSION);
        AuditLog::record('logout', 'users', (string)$userId, null);
        Session::destroy();
    }

    public static function requireAuth(string $redirect = '/admin/login'): void
    {
        if (!self::check()) {
            Session::flash('_old_input', ['redirect_after' => $_SERVER['REQUEST_URI'] ?? '/admin']);
            redirect($redirect);
        }
    }

    public static function requireRole(string $role_slug, string $redirect = '/admin'): void
    {
        self::requireAuth($redirect);
        if (!self::is($role_slug)) {
            Session::flash('error', 'No tienes permiso para acceder a esta sección.');
            redirect($redirect);
        }
    }

    public static function requireCan(string $action, string $redirect = '/admin'): void
    {
        self::requireAuth($redirect);
        if (!self::can($action)) {
            Session::flash('error', 'No tienes permiso para realizar esta acción.');
            redirect($redirect);
        }
    }

    public static function ip(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $k) {
            if (empty($_SERVER[$k])) {
                continue;
            }
            $v = explode(',', $_SERVER[$k])[0];
            $v = trim($v);
            if ($v) {
                return $v;
            }
        }
        return '0.0.0.0';
    }

    // ---- Rate limit
    private static function getLoginAttempts(string $ip, string $email): array
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('SELECT * FROM login_attempts WHERE ip_address = ? LIMIT 1');
        $sth->execute([$ip]);
        $row = $sth->fetch();
        return [
            'attempts' => (int)($row['attempts'] ?? 0),
            'locked'   => $row['locked_until'] ?? null,
        ];
    }

    private static function registerFailedAttempt(string $ip, string $email, int $current): void
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        $sec = $cfg['security'];
        $max = (int)($sec['login_max_attempts'] ?? 5);
        $lockSecs = (int)($sec['login_lock_seconds'] ?? 300);
        $pdo = DB::connection();
        $newCount = $current + 1;
        $lockedUntil = null;
        if ($newCount >= $max) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $lockSecs);
        }
        $sth = $pdo->prepare('INSERT INTO login_attempts (ip_address, email, attempts, locked_until) VALUES (?,?,?,?)
                              ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), locked_until = VALUES(locked_until), email = VALUES(email), updated_at = CURRENT_TIMESTAMP');
        $sth->execute([$ip, $email, $newCount, $lockedUntil]);
    }

    private static function clearLoginAttempts(string $ip, string $email): void
    {
        $pdo = DB::connection();
        $sth = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
        $sth->execute([$ip]);
    }
}
