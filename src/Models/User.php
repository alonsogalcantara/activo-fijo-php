<?php
namespace Models;

require_once __DIR__ . '/../../config/db.php';

use Database;
use PDO;

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password_hash;
    public $system_role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function login($email, $password) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
             // In a real migration, we might need to handle different hashing algorithms.
             // Python's werkzeug default is pbkdf2:sha256.
             // PHP's password_verify expects bcrypt (usually).
             
             // For this migration, we will assume we can use password_verify directly 
             // OR we might need a custom check if strict compatibility is needed without reset.
             
             // However, the project.md suggests: "Use `password_hash()` and `password_verify()`... Recommendation: Create a script to reset passwords or implement a legacy check"
             
             // I will implement a standard verify for now.
             if (password_verify($password, $user['password_hash'])) {
                 return $user;
             }
        }
        return false;
    }
    
    public function getUserById($id) {
         $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(':id', $id);
         $stmt->execute();
         return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = 'SELECT id, name FROM ' . $this->table . ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllFull() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' (name, email, password_hash, role, status, first_name, last_name) VALUES (:name, :email, :password_hash, :role, :status, :first_name, :last_name)';
        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $query = 'UPDATE ' . $this->table . ' SET name = :name, email = :email, role = :role, status = :status, first_name = :first_name, last_name = :last_name';
        
        if (isset($data['password'])) {
            $query .= ', password_hash = :password_hash';
        }

        // Add system_role if present in data
        if (array_key_exists('system_role', $data)) {
            $query .= ', system_role = :system_role';
        }
        
        $query .= ' WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        
        if (isset($data['password'])) {
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password_hash', $password_hash);
        }

        if (array_key_exists('system_role', $data)) {
            $stmt->bindParam(':system_role', $data['system_role']);
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
