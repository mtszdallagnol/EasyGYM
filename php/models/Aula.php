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

class AulaDTO {
    public int $id_aula = -1;
    public string $nome_atividade_aula;
    public string $duracao_aula;
    public string $dia_aula;
    public string $nome_instrutor_aula;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyName($data["nome_atividade_aula"] ?? null);
        Utility::verifyName($data["duracao_aula"] ?? null);
        Utility::verifyName($data["dia_aula"] ?? null);
        Utility::verifyName($data["nome_instrutor_aula"] ?? null);
        Utility::verifyAcademia($data["id_academia"] ?? null);

        foreach($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Aula implements ControllerInterace {
    private function __construct() {}

    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            if ($id_academia === -1) {
                $stmt = $conn->query("SELECT * FROM aulas");
            }

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = new AulaDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        $query = "SELECT * FROM aulas WHERE ";

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
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = new AulaDTO($row);
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();

        if (count(Aula::getByParams(["id_academia" => $data->id_academia, "nome_atividade_aula" => $data->nome_atividade_aula])) > 0) {
            die(json_encode(["error", "Nome já existente"]));
        }

        unset($data->id_aula);

        $params = [];
        foreach($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO aulas (nome_atividade_aula, duracao_aula, dia_aula, nome_instrutor_aula, id_academia)
                    VALUES (:nome_atividade_aula, :duracao_aula, :dia_aula, :nome_instrutor_aula, :id_academia)");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        $curr = Aula::getByParams(["id_academia" => $data->id_academia, "id_aula" => $data->id_aula])[0];
        $repeat = Aula::getByParams(["id_academia" => $data->id_academia, "nome_atividade_aula" => $data->nome_atividade_aula]);
        if (count($repeat) > 0 && $curr->nome_atividade_aula !== $repeat[0]->nome_atividade_aula) {
            die(json_encode(["error", "Nome já existente"]));
        }

        $params = [];
        foreach($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            $stmt = $conn->prepare("UPDATE aulas
                    SET nome_atividade_aula = :nome_atividade_aula, duracao_aula = :duracao_aula, dia_aula = :dia_aula, nome_instrutor_aula = :nome_instrutor_aula
                    WHERE id_academia = :id_academia AND id_aula = :id_aula");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete($id, $id_academia): int {
        $conn = Database::getInstance();

        try {
            $stmt = $conn->prepare("DELETE FROM aulas
                    WHERE id_academia = :id_academia AND id_aula = :id_aula");
            $stmt->execute([":id_academia" => $id_academia, ":id_aula" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}