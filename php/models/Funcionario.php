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

class FuncrionarioDTO {
    public int $id_funcionario = -1;
    public string $nome_funcionario;
    public string $email_funcionario;
    public string $senha_funcionario;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyName($data["nome_funcionario"] ?? null);
        Utility::verifyEmail($data["email_funcionario"] ?? null);
        Utility::verifyName($data["senha_funcionario"] ?? null);
        Utility::verifyAcademia($data["id_academia"] ?? null);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Funcionario implements ControllerInterace {
    private function __construct() {}

    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            if ($id_academia !== -1) {
                $stmt = $conn->prepare("SELECT * FROM funcionarios WHERE id_academia = :id_academia");
                $stmt->bindParam(":id_academia", $id_academia);
                $stmt->execute();
            } else {
                $stmt = $conn->query("SELECT * FROM funcionarios");
            }

            $result = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($result, new FuncrionarioDTO($row));
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        $query = "SELECT * FROM funcionarios WHERE ";

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
                array_push($result, new FuncrionarioDTO($row));
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();

        if (count(Funcionario::getByParams(["id_academia" => $data->id_academia, "nome_funcionario" => $data->nome_funcionario])) > 0) {
            die(json_encode(["error", "Nome já existente"]));
        }

        if (count(Funcionario::getByParams(["id_academia" => $data->id_academia, "email_funcionario" => $data->email_funcionario])) > 0) {
            die (json_encode(["error", "Email já existente"]));
        }

        unset($data->id_funcionario);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }
    
        try {
            $stmt = $conn->prepare("INSERT INTO funcionarios (nome_funcionario, email_funcionario, senha_funcionario, id_academia)
                    VALUES (:nome_funcionario, :email_funcionario, :senha_funcionario, :id_academia)");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        $curr = Funcionario::getByParams(["id_academia" => $data->id_academia, "id_funcionario" => $data->id_funcionario])[0];
        $repeat = Funcionario::getByParams(["id_academia" => $data->id_academia, "nome_funcionario" => $data->email_funcionario]);
        if (count($repeat) > 0 && $curr->email_aluno !== $repeat[0]->email_aluno) {
            die(json_encode(["error", "Email já existente"]));
        }

        unset($data->nome_funcionario);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            $stmt = $conn->prepare("UPDATE funcionarios
                    SET email_funcionario = :email_funcionario, senha_funcionario = :senha_funcionario
                    WHERE id_academia = :id_academia AND id_funcionario = :id_funcionario");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete($id, $id_academia): int {
        $conn = Database::getInstance();

        try {
            $stmt = $conn->prepare("DELETE FROM funcionarios
                    WHERE id_academia = :id_academia AND id_funcionario = :id_funcionario");
            $stmt->execute([":id_academia" => $id_academia, ":id_funcionario" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}