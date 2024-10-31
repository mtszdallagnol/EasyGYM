<?php

namespace Core;

require_once __DIR__ . "/../config/config.php";

use mysqli;

class Database {
    private static $instance = null;
    private $conn = null;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die(["error", "Conexão com banco falhou: " . $this->conn->connect_error]);
        }
    }

    public function __destruct() {
        $this->conn->close();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
            return self::$instance->conn;
        }
        return self::$instance->conn;
    }
}