<?php
// app/models/Tender.php
require_once __DIR__ . '/../config/database.php';

class Tender {
    public static function all(array $filters = []): array {
        $sql = "SELECT t.*, u.name as creator_name,
                (SELECT COUNT(*) FROM bids b WHERE b.tender_id = t.id) as bid_count
                FROM tenders t JOIN users u ON t.created_by = u.id WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) { $sql .= " AND t.status=?"; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $sql .= " AND t.category=?"; $params[] = $filters['category']; }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $params[] = '%'.$filters['search'].'%';
            $params[] = '%'.$filters['search'].'%';
        }
        $sql .= " ORDER BY t.created_at DESC";
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public static function find(int $id): ?array {
        $stmt = getDB()->prepare("SELECT t.*, u.name as creator_name FROM tenders t JOIN users u ON t.created_by=u.id WHERE t.id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    public static function create(array $data): int {
        $db = getDB();
        $db->prepare("INSERT INTO tenders (title,description,category,budget,deadline,reveal_deadline,status,created_by) VALUES (?,?,?,?,?,?,'open',?)")
           ->execute([$data['title'],$data['description'],$data['category'],$data['budget'],$data['deadline'],$data['reveal_deadline'],$data['created_by']]);
        return (int)$db->lastInsertId();
    }
    public static function updateTxHash(int $id, string $txHash): void {
        getDB()->prepare("UPDATE tenders SET tx_hash=? WHERE id=?")->execute([$txHash, $id]);
    }
    public static function updateStatus(int $id, string $status): void {
        getDB()->prepare("UPDATE tenders SET status=? WHERE id=?")->execute([$status, $id]);
    }
    public static function setAwarded(int $id): void {
        getDB()->prepare("UPDATE tenders SET status='awarded' WHERE id=?")->execute([$id]);
    }
}
