<?php

namespace Models;

require_once __DIR__ . "/../core/ControllerInterface.php";
require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . "/../core/Utility.php";

use Core\ControllerInterace;
use Core\Database;
use Core\Utility;
use PDO;
use PDOException;

class EquipamentoDTO {
    public int $id_equipamento = -1;
    public string $modelo_equipamento;
    public string $marca_equipamento;
    public string $nome_equipamento;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyName($data["modelo_equipamento"] ?? null);
        Utility::verifyName($data["marca_equipamento"] ?? null);
        Utility::verifyName($data["nome_equipamento"] ?? null);
        Utility::verifyAcademia($data["id_academia"] ?? null);

        foreach($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Equipamento implements ControllerInterace {
    private function __construct() {}

    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            if ($id_academia === -1) {
                $stmt = $conn->query("SELECT * FROM equipamentos");
            }

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = new EquipamentoDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        $query = "SELECT * FROM equipamentos WHERE ";

        $tempParam = [];
        foreach($params as $key => $value) {
            $query .= $key . " = " . ":" . $key . ";";
            $tempParam[":" . $key] = $value;
        }
        $query = str_replace(";", " AND ", $query);
        $query = substr($query, 0, -5);

        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($tempParam);

            $result = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = new EquipamentoDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();

        unset($data->id_equipamento);

        $params = [];
        foreach($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO equipamentos (modelo_equipamento, marca_equipamento, nome_equipamento, id_academia)
                    VALUES (:modelo_equipamento, :marca_equipamento, :nome_equipamento, :id_academia)");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        $params = [];
        foreach($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            $stmt = $conn->prepare("UPDATE equipamentos
                    SET modelo_equipamento = :modelo_equipamento, marca_equipamento = :marca_equipamento, nome_equipamento = :nome_equipamento
                    WHERE id_academia = :id_academia AND id_equipamento = :id_equipamento");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete($id, $id_academia): int {
        $conn = Database::getInstance();

        try {
            $stmt = $conn->prepare("DELETE FROM equipamentos
                    WHERE id_academia = :id_academia AND id_equipamento = :id_equipamento");
            $stmt->execute([":id_academia" => $id_academia, ":id_equipamento" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}