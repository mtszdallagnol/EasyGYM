<?php

namespace Models;

require_once __DIR__ . "/../core/ControllerInterface.php";
require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . "/../core/Utility.php";

use Core\ControllerInterace;
use Core\Database;
use Core\Utility;

class AcademiaDTO {
    public int $id_academia = -1;
    public string $cnpj_academia;
    public string $cep_academia;
    public string $nome_academia;
    public string $telefone_academia;

    public function __construct($data) {
        Utility::verifyID($data["id_academia"] ?? null);
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
    private function __construct() { }

    public static function getAll() : array {
        $conn = Database::getInstance();

        // Prepara e executa instrução SQL
        $result = $conn->query("SELECT * FROM academias");

        // Busca os resultados e os coloca em uma array de objetos AcademiaDTO
        $response = [];
        while ($row = $result->fetch_assoc()) {
            $response[] = new AcademiaDTO($row);
        }
        
        return $response;
    }

    public static function getByParams(array $params) : array {
        $conn = Database::getInstance();

        // Organiza o array em ordem alfabética
        ksort($params);

        // Inicializa e constroi a QUERY SQL dinamicamente baseado nos parâmetros providenciados
        $query = "SELECT * FROM academias WHERE ";

        foreach ($params as $key => &$value) {
            if (!property_exists('Models\\AcademiaDTO', str_replace(":", "", $key))) {
                die(json_encode(["error", "Parâmetro: " . str_replace(":", "", $key) . " inválido"]));
            }

            $query .= str_replace(":", "", $key) . " LIKE ? ;";
            $value .= "%";
        }

        $query = str_replace(";", " AND ", $query);
        $query = substr($query, 0, -5);
        
        //Prepare a instrução SQL
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die(["error", "Falha na preparação: " . $conn->error]);
        }

        //Extrai o tipo e os valores do array dos parâmetros
        //Binda a instrução SQL
        $types = str_repeat("s", count($params));
        $temp = array_values($params);
        $stmt->bind_param($types, ...$temp);

        //Executa a instrução SQL
        if(!$stmt->execute()) {
            die(["error", "Erro na execução: " . $stmt->error]);
        }

        //Busca os resultados e os coloca em uma array de objetos AcademiaDTO
        $response = [];
        while ($row = $stmt->get_result()->fetch_assoc()) {
            $response[] = new AcademiaDTO($row);
        }

        return $response;
    }

    public static function post($data): int  {
        $conn = Database::getInstance();

        // Verifica se já existe CEP
        if (count(Academia::getByParams([":cep_academia" => $data->cep_academia ]))) {
            die (["error", "CEP já existente"]);
        }
        
        // Prepara a instrução SQL
        $stmt = $conn->prepare("INSERT INTO academias (cep_academia, cnpj_academia, nome_academia, telefone_academia)
                VALUES (?, ?, ?, ?)");

        if (!$stmt) {
            die(["error", "Falha na preparação: " . $conn->error]); 
        }

        // Converte objeto AcademiaDTO para uma array associativa 
        // Remove o campo id_academia 
        // Organiza por ordem alfabética 
        $data = (array)$data;
        unset($data["id_academia"]);
        ksort($data);

        // Extrai valores e tipos
        $types = str_repeat("s", count($data));
        $temp = array_values($data);

        // Binda os parâmetros
        $stmt->bind_param($types, ...$temp);

        //Executa e lida com os resultados
        if ($stmt->execute()) {
            return $stmt->affected_rows;
        } 
        else {
            die(["error", "Falha na execução: " . $stmt->error]);
        } 
         
    } 

    public static function put($data) : int {
        $conn = Database::getInstance();
        
        if (count(Academia::getByParams([":cep_academia" => $data->cep_academia])) > 0) die(["error" => "CEP já existente"]);

        $stmt = $conn->prepare("UPDATE academias SET (cep_academia = :cep_academia, nome_academia = :nome_academia, telefone_academia = :telefone_academia)
                WHERE id_academia = :id_academia");
        if (!$stmt) die(["error", "Falaha na preparação: " . $conn->error]);
        $params = [];
        foreach ($data as $key => $value) {
            if ($key === "cnpj_academia") continue;
            $params[":" . $key] = $value;
        }
        if ($stmt->execute($params)) return $stmt->affected_rows;
        else die(["error", "Falha na execução: " . $stmt->error]);
    }

    public static function delete($id) : int {
        if (empty(($id))) die(["error", "ID inválido"]);
        Utility::verifyID($id);

        $conn = Database::getInstance();
        $stmt = $conn->prepare("DELETE FROM academias WHERE id_academia = :id_academia");

        if (!$stmt) die(["error", "Falha na preparação: " . $conn->error]);

        $stmt->bind_param(":id_academia", $id);
        if ($stmt->execute()) return $stmt->affected_rows;
        else die(["error", "Falha na execução: " . $stmt->error]);
        
    }
}

