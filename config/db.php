<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public  $conn;

    public function __construct() {
        $config = $this->loadYaml(__DIR__ . '/../.yml');

        $this->host     = $config['database']['host']     ?? '127.0.0.1';
        $this->db_name  = $config['database']['name']     ?? '';
        $this->username = $config['database']['user']     ?? '';
        $this->password = $config['database']['password'] ?? '';
        $this->port     = $config['database']['port']     ?? 3306;
    }

    /**
     * Lightweight YAML parser for simple key: value and nested sections.
     * Supports: top-level sections, string/int values, quoted strings.
     */
    private function loadYaml(string $path): array {
        if (!file_exists($path)) {
            die("Config error: .yml file not found at $path");
        }

        // Use the native PHP YAML extension if available
        if (function_exists('yaml_parse_file')) {
            return yaml_parse_file($path);
        }

        // Vanilla PHP fallback parser (handles the app's config structure)
        $lines   = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result  = [];
        $section = null;

        foreach ($lines as $line) {
            // Skip comments
            if (ltrim($line)[0] === '#') continue;

            // Detect top-level section (no leading spaces, ends with colon)
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*):\s*$/', $line, $m)) {
                $section = $m[1];
                $result[$section] = [];
                continue;
            }

            // Detect key: value pair (with optional leading spaces for nesting)
            if (preg_match('/^\s+([a-zA-Z_][a-zA-Z0-9_]*):\s*(.*)$/', $line, $m)) {
                $key   = $m[1];
                $value = trim($m[2]);

                // Strip surrounding quotes
                $value = trim($value, '"\'');

                // Cast to int if numeric and no quotes in original
                if (is_numeric($value) && !preg_match('/["\']/', $m[2])) {
                    $value = (int)$value;
                }

                if ($section !== null) {
                    $result[$section][$key] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }

        return $this->conn;
    }
}
