<?php
// app/models/User.php
require_once __DIR__ . '/../config/database.php';

class User {
    public static function findByEmail(string $email): ?array {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
    public static function findById(int $id): ?array {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    public static function create(array $data): int {
        $db = getDB();
        $db->prepare("INSERT INTO users (name,email,password,role,company_name,phone) VALUES (?,?,?,?,?,?)")
           ->execute([
               $data['name'], $data['email'],
               password_hash($data['password'], PASSWORD_BCRYPT),
               $data['role'] ?? 'bidder',
               $data['company_name'] ?? null,
               $data['phone'] ?? null,
           ]);
        return (int)$db->lastInsertId();
    }
    public static function verify(string $email, string $password): ?array {
        $user = self::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) return $user;
        return null;
    }
    public static function emailExists(string $email): bool {
        $stmt = getDB()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }
}
