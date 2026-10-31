<?php
/**
 * APEX Car Rental Management System
 * Authentication, Session & Role-Based Access Control
 */

require_once __DIR__ . '/Database.php';

class Auth {
    /**
     * Start session if needed
     */
    private static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate user with email and password
     */
    public static function login(string $email, string $password): array {
        self::initSession();
        $email = trim(strtolower($email));

        $user = Database::fetchOne(
            "SELECT * FROM `users` WHERE `email` = ? LIMIT 1",
            [$email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this email address.'];
        }

        if ($user['status'] === 'blocked') {
            return ['success' => false, 'message' => 'Your account has been suspended by administration. Please contact support.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Incorrect password entered. Please try again.'];
        }

        // Store user in session
        $_SESSION['apex_user'] = [
            'id'             => $user['id'],
            'name'           => $user['name'],
            'email'          => $user['email'],
            'role'           => $user['role'],
            'status'         => $user['status'],
            'phone'          => $user['phone'],
            'license_number' => $user['license_number']
        ];

        return ['success' => true, 'user' => $_SESSION['apex_user']];
    }

    /**
     * Register a new customer
     */
    public static function register(string $name, string $email, string $password, ?string $phone = null, ?string $license = null): array {
        self::initSession();
        $name = trim($name);
        $email = trim(strtolower($email));
        $phone = trim($phone ?? '');
        $license = trim($license ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Please provide all required fields.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address format.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
        }

        // Check if email already registered
        $existing = Database::fetchOne("SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1", [$email]);
        if ($existing) {
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        Database::query(
            "INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `license_number`, `role`, `status`) VALUES (?, ?, ?, ?, ?, 'customer', 'active')",
            [$name, $email, $passwordHash, $phone, $license]
        );

        $newUserId = (int)Database::lastInsertId();

        // Auto login
        $_SESSION['apex_user'] = [
            'id'             => $newUserId,
            'name'           => $name,
            'email'          => $email,
            'role'           => 'customer',
            'status'         => 'active',
            'phone'          => $phone,
            'license_number' => $license
        ];

        return ['success' => true, 'user' => $_SESSION['apex_user']];
    }

    /**
     * Terminate user session
     */
    public static function logout(): void {
        self::initSession();
        unset($_SESSION['apex_user']);
        session_destroy();
    }

    /**
     * Get current user
     */
    public static function user(): ?array {
        self::initSession();
        return $_SESSION['apex_user'] ?? null;
    }

    public static function id(): ?int {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function isLoggedIn(): bool {
        return self::user() !== null;
    }

    public static function isAdmin(): bool {
        $u = self::user();
        return $u && $u['role'] === 'admin';
    }

    public static function isStaff(): bool {
        $u = self::user();
        return $u && in_array($u['role'], ['admin', 'staff'], true);
    }

    /**
     * Require authentication or redirect
     */
    public static function requireLogin(string $redirectTo = 'login.php'): void {
        if (!self::isLoggedIn()) {
            header("Location: " . apex_url($redirectTo . '?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '')));
            exit;
        }

        // Re-check if blocked in database
        $check = Database::fetchOne("SELECT `status` FROM `users` WHERE `id` = ?", [self::id()]);
        if (!$check || $check['status'] === 'blocked') {
            self::logout();
            header("Location: " . apex_url('login.php?error=blocked'));
            exit;
        }
    }

    /**
     * Require specific roles or redirect/abort
     */
    public static function requireRole(array $roles, string $redirectTo = 'login.php'): void {
        self::requireLogin($redirectTo);
        $u = self::user();
        if (!in_array($u['role'], $roles, true)) {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>You do not have administrative privileges to access this page.</p><p><a href='" . apex_url('') . "'>Return to Home</a></p>");
        }
    }

    /**
     * CSRF protection token generation
     */
    public static function generateCsrfToken(): string {
        self::initSession();
        if (empty($_SESSION['apex_csrf'])) {
            $_SESSION['apex_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['apex_csrf'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(?string $token): bool {
        self::initSession();
        if (empty($token) || empty($_SESSION['apex_csrf'])) {
            return false;
        }
        return hash_equals($_SESSION['apex_csrf'], $token);
    }
}
