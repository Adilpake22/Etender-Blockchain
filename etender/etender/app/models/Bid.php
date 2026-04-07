<?php
// app/models/Bid.php
require_once __DIR__ . '/../config/database.php';

class Bid {
    public static function create(array $data): int {
        $db = getDB();
        $db->prepare("INSERT INTO bids (tender_id,bidder_id,bid_hash,commit_tx_hash,status) VALUES (?,?,?,?,'committed')")
           ->execute([
               $data['tender_id'],
               $data['bidder_id'],
               $data['bid_hash'],
               $data['commit_tx_hash'],
           ]);
        return (int)$db->lastInsertId();
    }
    public static function find(int $id): ?array {
        $stmt = getDB()->prepare("SELECT b.*, u.name as bidder_name, u.company_name FROM bids b JOIN users u ON b.bidder_id=u.id WHERE b.id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    public static function forTender(int $tenderId): array {
        $stmt = getDB()->prepare("SELECT b.*, u.name as bidder_name, u.company_name FROM bids b JOIN users u ON b.bidder_id=u.id WHERE b.tender_id=? ORDER BY b.final_score DESC");
        $stmt->execute([$tenderId]);
        return $stmt->fetchAll();
    }
    public static function byBidder(int $bidderId): array {
        $stmt = getDB()->prepare("SELECT b.*, t.title as tender_title, t.status as tender_status FROM bids b JOIN tenders t ON b.tender_id=t.id WHERE b.bidder_id=? ORDER BY b.submitted_at DESC");
        $stmt->execute([$bidderId]);
        return $stmt->fetchAll();
    }
    public static function existsForTender(int $tenderId, int $bidderId): bool {
        $stmt = getDB()->prepare("SELECT id FROM bids WHERE tender_id=? AND bidder_id=?");
        $stmt->execute([$tenderId, $bidderId]);
        return (bool)$stmt->fetch();
    }
    public static function reveal(int $id, float $amount, string $txHash): void {
        getDB()->prepare("UPDATE bids SET amount=?,reveal_tx_hash=?,status='revealed',revealed_at=NOW() WHERE id=?")
               ->execute([$amount,$txHash,$id]);
    }
    public static function score(int $id, int $tech, int $fin, string $notes): void {
        $final = ($tech * 0.4) + ($fin * 0.6);
        getDB()->prepare("UPDATE bids SET technical_score=?,financial_score=?,final_score=?,notes=?,status='evaluated' WHERE id=?")
               ->execute([$tech,$fin,$final,$notes,$id]);
    }
    public static function award(int $id): void {
        getDB()->prepare("UPDATE bids SET status='awarded' WHERE id=?")->execute([$id]);
    }
    public static function rejectOthers(int $tenderId, int $winnerId): void {
        getDB()->prepare("UPDATE bids SET status='rejected' WHERE tender_id=? AND id!=?")->execute([$tenderId,$winnerId]);
    }
}