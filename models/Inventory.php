<?php
// ============================================================
//  Model: Inventory
//  File : models/Inventory.php
//  Handles: Stock queries and adjustments (Bookkeeper)
// ============================================================

declare(strict_types=1);

class Inventory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * All inventory items with created_by user name.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT i.*, u.full_name AS created_by_name
               FROM inventory_data i
               JOIN users u ON u.user_id = i.created_by
              ORDER BY i.category, i.item_name'
        );
        return $stmt->fetchAll();
    }

    /**
     * Items at or below reorder level (low-stock alerts).
     */
    public function getLowStock(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM inventory_data
              WHERE quantity_on_hand <= reorder_level
              ORDER BY quantity_on_hand ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Count of low-stock items (dashboard widget).
     */
    public function getLowStockCount(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM inventory_data
              WHERE quantity_on_hand <= reorder_level'
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Single item by ID.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM inventory_data WHERE item_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new inventory item.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO inventory_data
                (item_name, category, unit, quantity_on_hand, reorder_level, unit_cost, description, created_by)
             VALUES
                (:item_name, :category, :unit, :qty, :reorder, :cost, :desc, :created_by)'
        );
        $stmt->execute([
            ':item_name'   => $data['item_name'],
            ':category'    => $data['category'],
            ':unit'        => $data['unit'],
            ':qty'         => $data['quantity_on_hand'],
            ':reorder'     => $data['reorder_level'],
            ':cost'        => $data['unit_cost'],
            ':desc'        => $data['description'] ?? null,
            ':created_by'  => $data['created_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Restock an item (add quantity).
     */
    public function restock(int $id, float $qty): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory_data
                SET quantity_on_hand = quantity_on_hand + :qty
              WHERE item_id = :id'
        );
        return $stmt->execute([':qty' => $qty, ':id' => $id]);
    }

    /**
     * Update item metadata (not quantity — use restock() for that).
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory_data
                SET item_name     = :item_name,
                    category      = :category,
                    unit          = :unit,
                    reorder_level = :reorder_level,
                    unit_cost     = :unit_cost,
                    description   = :description
              WHERE item_id       = :id'
        );
        return $stmt->execute([
            ':item_name'     => $data['item_name'],
            ':category'      => $data['category'],
            ':unit'          => $data['unit'],
            ':reorder_level' => $data['reorder_level'],
            ':unit_cost'     => $data['unit_cost'],
            ':description'   => $data['description'] ?? null,
            ':id'            => $id,
        ]);
    }

    /**
     * Total inventory value (qty_on_hand × unit_cost) across all items.
     */
    public function getTotalValue(): float
    {
        $stmt = $this->db->query(
            'SELECT COALESCE(SUM(quantity_on_hand * unit_cost), 0) FROM inventory_data'
        );
        return (float) $stmt->fetchColumn();
    }
}
