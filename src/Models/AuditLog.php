<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class AuditLog {
    private $conn;
    private $table = 'audit_logs';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll($limit = 100) {
        // Schema has actor_username, no user_id. 
        // We can try to join users on name=name to get email if we really want, but simpler is just raw select.
        // Let's keep it simple for now or Left Join on username if names are unique.
        // Schema Users.name is varchar(255).
        
        $query = 'SELECT l.*, l.actor_username, u.email as actor_email
                  FROM ' . $this->table . ' l
                  LEFT JOIN users u ON l.actor_username = u.name
                  ORDER BY l.created_at DESC LIMIT :limit';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function log($username, $action, $table_name, $record_id, $old_value = null, $new_value = null) {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (actor_username, action, table_name, record_id, old_value, new_value, timestamp)
                  VALUES (:username, :action, :table_name, :record_id, :old_value, :new_value, NOW())';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':table_name', $table_name);
        $stmt->bindParam(':record_id', $record_id);
        $stmt->bindParam(':old_value', $old_value);
        $stmt->bindParam(':new_value', $new_value);
        
        return $stmt->execute();
    }
}
