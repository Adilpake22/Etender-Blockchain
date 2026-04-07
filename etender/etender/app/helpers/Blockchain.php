<?php
// app/helpers/Blockchain.php
// Pure PHP blockchain simulation — no Node.js needed
// Generates realistic tx hashes and records all actions in audit_log

class Blockchain {

    // Generate a realistic-looking Ethereum transaction hash
    public static function generateTxHash(): string {
        return '0x' . bin2hex(random_bytes(32));
    }

    // Simulate publishing a tender on-chain
    public static function publishTender(int $tenderId, string $title): array {
        $txHash = self::generateTxHash();
        self::audit('tender_published', $tenderId, 'tender', $txHash, "Tender: $title");
        return ['success' => true, 'tx_hash' => $txHash];
    }

    // Simulate closing a tender on-chain
    public static function closeTender(int $tenderId): array {
        $txHash = self::generateTxHash();
        self::audit('tender_closed', $tenderId, 'tender', $txHash);
        return ['success' => true, 'tx_hash' => $txHash];
    }

    // Simulate committing a bid hash on-chain
    public static function commitBid(int $bidId, string $bidHash): array {
        $txHash = self::generateTxHash();
        self::audit('bid_committed', $bidId, 'bid', $txHash, "Hash: $bidHash");
        return ['success' => true, 'tx_hash' => $txHash];
    }

    // Simulate revealing a bid on-chain
    public static function revealBid(int $bidId, float $amount): array {
        $txHash = self::generateTxHash();
        self::audit('bid_revealed', $bidId, 'bid', $txHash, "Amount: $amount");
        return ['success' => true, 'tx_hash' => $txHash];
    }

    // Simulate awarding a tender on-chain
    public static function awardTender(int $tenderId, string $winnerName): array {
        $txHash = self::generateTxHash();
        self::audit('tender_awarded', $tenderId, 'tender', $txHash, "Winner: $winnerName");
        return ['success' => true, 'tx_hash' => $txHash];
    }

    // Create bid commitment hash (SHA-256)
    // Create bid commitment hash using 4-digit PIN
public static function createBidHash(float $amount, string $pin): string {
    return hash('sha256', $amount . '|' . $pin);
}

// Verify bid hash matches original commitment
public static function verifyBidHash(float $amount, string $pin, string $storedHash): bool {
    return hash_equals($storedHash, self::createBidHash($amount, $pin));
}

    // Write to audit log
    private static function audit(string $action, int $recordId, string $recordType, string $txHash, ?string $details = null): void {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            $actorId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $db->prepare("INSERT INTO audit_log (action, actor_id, record_type, record_id, tx_hash, details) VALUES (?,?,?,?,?,?)")
               ->execute([$action, $actorId, $recordType, $recordId, $txHash, $details]);
        } catch (Exception $e) {
            // Silently fail audit log — don't break the main flow
        }
    }
}
