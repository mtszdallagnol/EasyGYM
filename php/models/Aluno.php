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

class AlunoDTO {
    public int $id_aluno = -1;
    public string $cpf_aluno;
    public string $nome_aluno;
    public string $email_aluno;
    public string $senha_aluno;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyCPF((string)$data['cpf_aluno'] ?? null);
        Utility::verifyName((string)$data["nome_aluno"] ?? null);
        Utility::verifyName((string)$data["senha_aluno"] ?? null);
        Utility::verifyEmail((string)$data["email_aluno"] ?? null);
        Utility::verifyAcademia($data["id_academia"] ?? -1);

        foreach($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Aluno implements ControllerInterace {
    private function __construct() {}
    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            if ($id_academia !== -1) {
                $stmt = $conn->prepare("SELECT * FROM alunos WHERE id_academia :id_academia");
                $stmt->bindParam(":id_academia", $id_academia);
                $stmt->execute();
            } else {
                $stmt = $conn->query("SELECT * FROM alunos");
            }

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($result, new AlunoDTO($row));
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        $query = "SELECT * FROM alunos WHERE ";

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
                array_push($result, new AlunoDTO($row));
            }
            
            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();
        
        if (count(Aluno::getByParams(["id_academia" => $data->id_academia, "cpf_aluno" => $data->cpf_aluno])) > 0) {
            die(json_encode(["error", "CPF já existente"]));
        }

        if (count(Aluno::getByParams(["id_academia" => $data->id_academia, "nome_aluno" => $data->nome_aluno])) > 0) {
            die(json_encode(["error", "Nome já existente"]));
        }

        unset($data->id_aluno);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {

            $stmt = $conn->prepare("INSERT INTO alunos (cpf_aluno, email_aluno, nome_aluno, senha_aluno, id_academia)
                    VALUES (:cpf_aluno, :email_aluno, :nome_aluno, :senha_aluno, :id_academia)");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        $curr = Aluno::getByParams(["id_academia" => $data->id_academia, "id_aluno" => $data->id_aluno])[0];
        $repeat = Aluno::getByParams(["id_academia" => $data->id_academia, "email_aluno" => $data->email_aluno]);
        if (count($repeat) > 0 && $curr->email_aluno !== $repeat[0]->email_aluno) {
            die(json_encode(["error", "Email já existente"]));
        }

        unset($data->nome_aluno);
        unset($data->cpf_aluno);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            $stmt = $conn->prepare("UPDATE alunos
                    SET email_aluno = :email_aluno, senha_aluno = :senha_aluno
                    WHERE id_academia = :id_academia AND id_aluno = :id_aluno");
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete(int $id, int $id_academia): int {
        $conn = Database::getInstance();

        try {
            $stmt = $conn->prepare("DELETE FROM alunos 
                    WHERE id_academia = :id_academia AND id_aluno = :id_aluno");
            $stmt->execute([":id_academia" => $id_academia, ":id_aluno" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}