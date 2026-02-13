<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class Account {
    private $conn;
    private $table = 'accounts';

    public $id;
    public $service_name;
    public $username;
    public $password;
    public $provider;
    public $contract_ref;
    public $renewal_date;
    public $cost;
    public $currency;
    public $frequency;
    public $account_type;
    public $assigned_to;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = 'SELECT a.*, u.name as assigned_user_name 
                  FROM ' . $this->table . ' a 
                  LEFT JOIN users u ON a.assigned_to = u.id 
                  ORDER BY a.service_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = 'SELECT a.*, u.name as assigned_user_name 
                  FROM ' . $this->table . ' a 
                  LEFT JOIN users u ON a.assigned_to = u.id 
                  WHERE a.id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' 
            (service_name, username, password, provider, contract_ref, renewal_date, cost, currency, frequency, account_type, assigned_to, max_licenses, observations) 
            VALUES (:service_name, :username, :password, :provider, :contract_ref, :renewal_date, :cost, :currency, :frequency, :account_type, :assigned_to, :max_licenses, :observations)';
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':service_name', $data['service_name']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':provider', $data['provider']);
        $stmt->bindParam(':contract_ref', $data['contract_ref']);
        $stmt->bindParam(':renewal_date', $data['renewal_date']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':frequency', $data['frequency']);
        $stmt->bindParam(':account_type', $data['account_type']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        $stmt->bindParam(':max_licenses', $data['max_licenses']);
        $stmt->bindParam(':observations', $data['observations']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $query = 'UPDATE ' . $this->table . ' 
            SET service_name = :service_name, 
                username = :username, 
                password = :password, 
                provider = :provider, 
                contract_ref = :contract_ref, 
                renewal_date = :renewal_date, 
                cost = :cost, 
                currency = :currency, 
                frequency = :frequency, 
                account_type = :account_type, 
                assigned_to = :assigned_to, 
                max_licenses = :max_licenses,
                observations = :observations
            WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':service_name', $data['service_name']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':provider', $data['provider']);
        $stmt->bindParam(':contract_ref', $data['contract_ref']);
        $stmt->bindParam(':renewal_date', $data['renewal_date']);
        $stmt->bindParam(':cost', $data['cost']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':frequency', $data['frequency']);
        $stmt->bindParam(':account_type', $data['account_type']);
        $stmt->bindParam(':assigned_to', $data['assigned_to']);
        $stmt->bindParam(':max_licenses', $data['max_licenses']);
        $stmt->bindParam(':observations', $data['observations']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    public function getByUser($userId) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE assigned_to = :user_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
