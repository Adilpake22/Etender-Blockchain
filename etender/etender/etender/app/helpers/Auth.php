<?php
// app/helpers/Auth.php
class Auth {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
    public static function login(array $user): void {
        self::start();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        session_regenerate_id(true);
    }
    public static function logout(): void {
        self::start();
        session_destroy();
    }
    public static function check(): bool {
        self::start();
        return isset($_SESSION['user_id']);
    }
    public static function id(): ?int {
        return self::check() ? (int)$_SESSION['user_id'] : null;
    }
    public static function role(): ?string {
        return self::check() ? $_SESSION['user_role'] : null;
    }
    public static function name(): ?string {
        return self::check() ? $_SESSION['user_name'] : null;
    }
    public static function require(string ...$roles): void {
        self::start();
        if (!self::check()) {
            header('Location: /etender/views/auth/login.php'); exit;
        }
        if (!empty($roles) && !in_array(self::role(), $roles)) {
            header('Location: /etender/views/auth/login.php'); exit;
        }
    }
    public static function requireJson(string ...$roles): void {
        self::start();
        if (!self::check()) {
            echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit;
        }
        if (!empty($roles) && !in_array(self::role(), $roles)) {
            echo json_encode(['success'=>false,'message'=>'Access denied']); exit;
        }
    }
}
