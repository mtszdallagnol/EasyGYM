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

class TreinoDTO {
    public int $id_treino = -1;
    public string $nome_treino;
    public string $duracao_treino;
    public string $repeticoes_treino;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyName($data["nome_treino"] ?? null);
        Utility::verifyName($data["duracao_treino"] ?? null);
        Utility::verifyName($data["repeticoes_treino"] ?? null);
        Utility::verifyAcademia($data["id_academia"] ?? null);

        foreach($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Treino implements ControllerInterace {
    private function __construct() {}

    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            if ($id_academia === -1) {
                $stmt = $conn->query("SELECT * FROM treinos");
            }

            $result = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = new TreinoDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        $query = "SELECT * FROM treinos WHERE ";

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
                $result[] = new TreinoDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();
        
        if (count(Treino::getByParams(["id_academia" => $data->id_academia, "nome_treino" => $data->nome_treino])) > 0) {
            die(json_encode(["error", "Nome já existente"]));
        }

        unset($data->id_treino);

        $params = [];
        foreach($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO treinos (nome_treino, duracao_treino, repeticoes_treino, id_academia)
                    VALUES (:nome_treino, :duracao_treino, :repeticoes_treino, :id_academia)");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        $curr = Treino::getByParams(["id_academia" => $data->id_academia, "id_treino" => $data->id_treino])[0];
        $repeat = Treino::getByParams(["id_academia" => $data->id_academia, "nome_treino" => $data->nome_treino]);
        if (count($repeat) > 0 && $curr->nome_treino !== $repeat[0]->nome_treino) {
            die(json_encode(["error", "Nome já existente"]));
        }

        $params = [];
        foreach ($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            $stmt = $conn->prepare("UPDATE treinos
                    SET nome_treino = :nome_treino, duracao_treino = :duracao_treino, repeticoes_treino = :repeticoes_treino
                    WHERE id_academia = :id_academia AND id_treino = :id_treino");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete($id, $id_academia): int {
        $conn = Database::getInstance();

        try {
            $stmt = $conn->prepare("DELETE FROM treinos
                    WHERE id_academia = :id_academia AND id_treino = :id_treino");
            $stmt->execute(["id_academia" => $id_academia, "id_treino" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}