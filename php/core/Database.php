<?php

namespace Core;

require_once __DIR__ . "/../config/config.php";

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn = null;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
            return self::$instance->conn;
        }
        return self::$instance->conn;
    }
}