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

class AcademiaDTO {
    public int $id_academia = -1;
    public string $cnpj_academia;
    public string $cep_academia;
    public string $nome_academia;
    public string $telefone_academia;

    public function __construct($data) {
        Utility::verifyCNPJ($data["cnpj_academia"] ?? null);
        Utility::verifyCEP($data["cep_academia"] ?? null);
        Utility::verifyName($data["nome_academia"] ?? null);
        Utility::verifyTelefone($data["telefone_academia"] ?? null);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Academia implements ControllerInterace{
    public static function getAll(): array {
        $conn = Database::getInstance();

        try {
            // Prepara e executa a instrução SQL
            $stmt = $conn->query("SELECT * FROM academias");

            // Busca os resultados e os coloca em uma array de objetos AcademiaDTO
            $result = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($result, new AcademiaDTO($row));
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error",$e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();
        // Monta dinamicamente a query e a array de parâmetros baseado nos parâmetros providenciados
        $query = "SELECT * FROM academias WHERE ";

        $tempParam = [];

        foreach ($params as $key => $value) {
            $query .= $key . " LIKE :" . $key . ";";
            $tempParam[":" . $key] = "%" . $value . "%";
        }
        $query = str_replace(";", " AND ", $query);
        $query = substr($query, 0, -5);

        try {
            // Prepara e executa a instrução SQL
            $stmt = $conn->prepare($query);
            $stmt->execute($tempParam);

            // Busca os resultados e os coloca em uma array de objetos AcademiaDTO
            $result = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($result, new AcademiaDTO($row));
            }

            return $result;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function post($data): int {
        $conn = Database::getInstance();

        // Verifica se já existe registro com mesmo CEP
        if (count(Academia::getByParams(["cep_academia" => $data->cep_academia])) > 0) {
            die(json_encode(["error", "CEP já existente"]));
        }

        // Remove id_academia do objeto e prepara array de parâmetros
        unset($data->id_academia); 

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            // Prepara, binda e executa instrução SQL
            $stmt = $conn->prepare("INSERT INTO academias (cep_academia, cnpj_academia, nome_academia, telefone_academia)
                    VALUES (:cep_academia, :cnpj_academia, :nome_academia, :telefone_academia)");
            
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        // Compara se já existe registro diferente com mesmo CEP
        $curr = Academia::getByParams(["id_academia" => $data->id_academia])[0];
        $repeat = Academia::getByParams(["cep_academia" => $data->cep_academia]);
        if (count($repeat) > 1 
            || (count($repeat) === 1 && $repeat[0]->cep_academia !== $curr->cep_academia)) {
                die(json_encode(["error", "CEP já existente"]));
        }

        // Monta dinamicamente a array de parâmetros baseado nos argumentos dados
        $params = [];
        foreach ($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            // Prepara, binda e executa a instrução SQL
            $stmt = $conn->prepare("UPDATE academias
                    SET cep_academia = :cep_academia, cnpj_academia = :cnpj_academia, nome_academia = :nome_academia, telefone_academia = :telefone_academia
                    WHERE id_academia = :id_academia");
                
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete (int $id): int {
        $conn = Database::getInstance();

        try {
            // Prepare, binda e executa instrução SQL
            $stmt = $conn->prepare("DELETE FROM academias WHERE id_academia = :id_academia");
            $stmt->execute([":id_academia" => $id]);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}

