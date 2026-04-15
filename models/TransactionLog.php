<?php
// ============================================================
//  Model: TransactionLog
//  File : models/TransactionLog.php
//  Handles: Audit-trail inserts and queries
// ============================================================

declare(strict_types=1);

class TransactionLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Insert a new audit log entry.
     *
     * @param int         $userId
     * @param string      $actionType  (must match ENUM in schema)
     * @param string      $description Human-readable summary
     * @param string|null $refTable    e.g. 'production_data'
     * @param int|null    $refId       PK of the affected record
     */
    public function log(
        int    $userId,
        string $actionType,
        string $description,
        ?string $refTable = null,
        ?int    $refId    = null
    ): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $this->db->prepare(
            'INSERT INTO transaction_logs
                (user_id, action_type, reference_table, reference_id, description, ip_address)
             VALUES (:uid, :act, :rt, :rid, :desc, :ip)'
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':act'  => $actionType,
            ':rt'   => $refTable,
            ':rid'  => $refId,
            ':desc' => $description,
            ':ip'   => $ip,
        ]);
    }

    /**
     * Return the most recent N log entries with actor name.
     */
    public function getRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.full_name
               FROM transaction_logs l
               JOIN users u ON u.user_id = l.user_id
              ORDER BY l.created_at DESC
              LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Filter logs by action type.
     */
    public function getByAction(string $actionType): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.*, u.full_name
               FROM transaction_logs l
               JOIN users u ON u.user_id = l.user_id
              WHERE l.action_type = :act
              ORDER BY l.created_at DESC'
        );
        $stmt->execute([':act' => $actionType]);
        return $stmt->fetchAll();
    }
}
