<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $this->host = trim($env['DB_HOST'], "'\"");
        $this->db_name = trim($env['DB_NAME'], "'\"");
        $this->username = trim($env['DB_USER'], "'\"");
        $this->password = trim($env['DB_PASS'], "'\"");
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
