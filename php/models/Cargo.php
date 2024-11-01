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

class CargoDTO {
    public int $id_cargo = -1;
    public string $nome_cargo;
    public int $nvl_cargo = 0;
    public int $id_academia;

    public function __construct($data) {
        Utility::verifyName($data["nome_cargo"] ?? null);
        Utility::verifyUint((int)$data["nvl_cargo"] ?? null);
        Utility::verifyAcademia((int)$data["id_academia"] ?? null);

        foreach($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class Cargo implements ControllerInterace{
    public static function getAll(int $id_academia): array {
        $conn = Database::getInstance();

        try {
            // Prepare, binda e executa a instrução SQL
            $stmt = $conn->prepare("SELECT * FROM cargos WHERE id_academia = :id_academia");
            $stmt->execute([":id_academia" => $id_academia]);

            // Busca os resultados e os coloca em uma array de objetos CargoDTO
            $response = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($response, new CargoDTO($row));
            }

            return $response;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function getByParams($params): array {
        $conn = Database::getInstance();

        // Monta a query e a array de parâmetros dinamicamente baseado nos argumentos providenciados
        $query = "SELECT * FROM cargos WHERE id_academia = :id_academia; ";
        
        $tempParamas = [];
        foreach ($params as $key => $value) {
            if ($key !== "id_academia") {
                $query .= $key . " LIKE :" . $key . "; ";
            }
            $tempParamas[":" . $key] = $value; 
        }
        $query = str_replace(";", " AND ", $query);
        $query = substr($query, 0, -5);

        try {
            // Prepare, binda e executa a instrução SQL
            $stmt = $conn->prepare($query);
            $stmt->execute($tempParamas);

            // Busca os resultados e os coloca em uma array de objetos CargoDTO
            $response = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($response, new CargoDTO($row));
            }

            return $response;
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
    
    public static function post($data): int {
        $conn = Database::getInstance();

        // Verifica se já existe registro com mesmo nome
        if (count(Cargo::getByParams(
            ["id_academia" => $data->id_academia, "nome_cargo" => $data->nome_cargo])) > 0) {
            die(json_encode(["error", "Cargo de mesmo nome já existente"]));
        }


        // Remove o id_cargo do objeto e cria o array de parâmetros
        unset($data->id_cargo);

        $params = [];
        foreach ($data as $key => $value) {
            $params[":" . $key] = $value;
        }

        try {
            // Prepara, binda e executa a instrução SQL
            $stmt = $conn->prepare("INSERT INTO cargos (id_academia, nome_cargo, nvl_cargo)
                    VALUES (:id_academia, :nome_academia, :nvl_cargo)");

            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function put($data): int {
        $conn = Database::getInstance();

        // Verifica se já existe registro com mesmo nome
        $curr = Cargo::getByParams(
            ["id_academia" => $data->id_academia, "id_cargo" => $data->id_cargo])[0];
        $repeat = Cargo::getByParams(
            ["id_academia" => $data->id_academia, "nome_cargo" => $data->nome_cargo]);
        if (count($repeat) > 0 && $curr->nome_cargo !== $repeat[0]->nome_cargo) {
            die(json_encode(["error", "Nome de cargo já existente"]));
        }


        //Cria dinamicamente a array de parâmetros
        $params = [];
        foreach($data as $key => $value) {
            $params[":". $key] = $value;
        }

        try {
            // Prepara, binda e executa a instrução SQL
            $stmt = $conn->prepare("UPDATE cargos SET
                    nome_cargo = :nome_cargo, nvl_cargo = :nvl_cargo WHERE 
                    id_academia = :id_academia AND id_cargo = :id_cargo");

            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }

    public static function delete(int $id, int $id_academia): int {
        $conn = Database::getInstance();

        try {
            // Prepara, binda e executa a instrução SQL
            $stmt = $conn->prepare("DELETE FROM cargos WHERE 
                    id_academia = :id_academia AND id_cargo = :id_cargo");
            
            $stmt->execute([":id_academia" => $id_academia, ":id_cargo" => $id]);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die(json_encode(["error", $e->getMessage()]));
        }
    }
}