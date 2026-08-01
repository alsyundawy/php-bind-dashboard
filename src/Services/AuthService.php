<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Config;
use App\Helpers\Database;
use PDO;

/**
 * Authentication & Authorization service
 */
final class AuthService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function attempt(string $username, string $password): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, password_hash, role, is_active, login_attempts, locked_until
             FROM users WHERE username = ? COLLATE NOCASE LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // Check lockout
        if (!empty($user['locked_until'])) {
            $lockedUntil = strtotime($user['locked_until']);
            if ($lockedUntil !== false && $lockedUntil > time()) {
                return false;
            }
            // Unlock if expired
            $this->resetLoginAttempts((int) $user['id']);
        }

        if (!(int) $user['is_active']) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementLoginAttempts((int) $user['id'], (int) $user['login_attempts']);
            return false;
        }

        // Success
        $this->resetLoginAttempts((int) $user['id']);
        $this->updateLastLogin((int) $user['id']);

        $_SESSION['user_id']   = (int) $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['logged_in'] = true;

        // Regenerate session ID
        session_regenerate_id(true);

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }

    public function check(): bool
    {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        return [
            'id'       => (int) $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'email'    => $_SESSION['email'] ?? '',
            'role'     => $_SESSION['role'] ?? 'viewer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->check() && ($_SESSION['role'] ?? '') === 'admin';
    }

    public function isOperatorOrHigher(): bool
    {
        if (!$this->check()) {
            return false;
        }
        $role = $_SESSION['role'] ?? '';
        return in_array($role, ['admin', 'operator'], true);
    }

    public function requireLogin(): void
    {
        if (!$this->check()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    private function incrementLoginAttempts(int $userId, int $current): void
    {
        $max = (int) Config::get('security.max_login_attempts', 5);
        $lockout = (int) Config::get('security.lockout_time', 900);

        $newAttempts = $current + 1;
        $lockedUntil = null;

        if ($newAttempts >= $max) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $lockout);
            $newAttempts = 0;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?'
        );
        $stmt->execute([$newAttempts, $lockedUntil, $userId]);
    }

    private function resetLoginAttempts(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?'
        );
        $stmt->execute([$userId]);
    }

    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET last_login_at = datetime(\'now\') WHERE id = ?'
        );
        $stmt->execute([$userId]);
    }
}
