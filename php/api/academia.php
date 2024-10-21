<?php

require "../dbcon.php";
require "../utility.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_GET[''])) {
        try {
            $stmt = $conn->query('SELECT * FROM academias');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => $tasks]);            
        } catch (PDOException $e) {
            echo json_encode(['error'=> $e->getMessage()]);
        }
    } else {
        $query = "SELECT * FROM academias WHERE ";

        $params = [];

        if (isset($_GET['nome_academia'])) {
            $query .= "nome_academia = :nome_academia";
            $params[':nome_academia'] = $_GET['nome_academia'];
        } elseif (isset($_GET['cep_academia'])) {
            $query .= "cep_academia = :cep_academia";
            $params[':cep_academia'] = $_GET['cep_academia'];
        } elseif (isset($_GET['telefone_academia'])) {
            $query .= "telefone_academia = :telefone_academia";
            $params[':telefone_academia'] = $_GET['telefone_academia'];
        } else {
            $query .= "cnpj_academia = :cnpj_academia";
            $params[':cnpj_academia'] = $_GET['cnpj_academia'];
        }

        try {
            $stmt = $conn->prepare($query);

            $stmt->execute($params);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => $result]);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

    }
}

function verifyCNPJ($cnpj) {
    if (empty($cnpj)) {
        echo json_encode(['error'=> 'CNPJ inválido']);
        exit;
    }
    if (strlen($cnpj) != 14) {
        echo json_encode(['error'=> 'CPNJ inválido']);
        exit;
    }
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        echo json_encode(['error'=> 'CNPJ inválido']);
        exit;
    }
    $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);
    if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
        echo json_encode(['error'=> 'CNPJ inválido']);
        exit;
    }
    for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);
    if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
        echo json_encode(['error'=> 'CNPJ inválido']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    ##CEP VERIFICATION SECTION
    verifyCEP($data['cep_academia'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE cep_academia = :cep");
        $stmt->bindParam(":cep", $data['cep_academia'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error'=> 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        json_encode(['error' => $e->getMessage()]);
        exit;
    }


    ##CNPJ VERIFICATION SECTION
    verifyCNPJ($data['cnpj_academia'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM academias WHERE cnpj_academia = :cnpj');
        $stmt->bindParam(':cnpj', $data['cnpj_academia'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error'=> 'CNPJ já existente']);
            exit;
        }
    } catch (PDOException $e) {
        json_encode(['error' => $e->getMessage()]);
        exit;
    }
    ##NOME VERIFICATION SECTION
    verifyName($data['nome_academia'] ?? null);

    ##TELEFONE VERIFICATION SECTION
    verifyTelefone($data['telefone_academia'] ?? null);

    try {
        $stmt = $conn->prepare('INSERT INTO academias (cep_academia, cnpj_academia, nome_academia, telefone_academia) VALUES (:cep, :cnpj, :nome, :telefone)');
        
        $stmt->bindParam(':cep', $data['cep_academia'], PDO::PARAM_STR);
        $stmt->bindParam(':cnpj', $data['cnpj_academia'], PDO::PARAM_STR);
        $stmt->bindParam(':nome', $data['nome_academia'], PDO::PARAM_STR);
        $stmt->bindParam(':telefone', $data['telefone_academia'], PDO::PARAM_STR);

        $stmt->execute();
        
        echo json_encode(["success" => "Cadastro realizado com sucesso!"]);
    } catch(PDOException $e) {
        echo json_encode(['error'=> $e->getMessage()]);
    }  
}

if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    $data = json_decode(file_get_contents('php://input'), true);
    
    verifyCEP($data['cep_academia'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE cep_academia = :cep");
        $stmt->bindParam(":cep",$data['cep_academia']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 1 || (count($result) === 1 && $result[0]['cep_academia'] !== $data['cep_academia'])) {
            echo json_encode(['error' => 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
    verifyCNPJ($data['cnpj_academia'] ?? null);
    verifyName($data['nome_academia'] ?? null);
    verifyTelefone($data['telefone_academia'] ?? null);

    try {
        $stmt = $conn->prepare('UPDATE academias SET cep_academia = :cep, nome_academia = :nome, telefone_academia = :telefone WHERE cnpj_academia = :cnpj');
        
        $stmt->bindParam(':cep', $data['cep_academia'], PDO::PARAM_STR);
        $stmt->bindParam('cnpj', $data['cnpj_academia'], PDO::PARAM_STR);
        $stmt->bindParam(':nome', $data['nome_academia'], PDO::PARAM_STR);
        $stmt->bindParam(':telefone', $data['telefone_academia'], PDO::PARAM_STR);
        
        $stmt->execute();

        echo json_encode(['success' => $data]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['cnpj_academia'])) {
        echo json_encode(["error" => "CNPJ inválido"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE cnpj_academia = :cnpj");
        $stmt->bindParam(':cnpj', $data['cnpj_academia'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(["error" => 'CNPJ não existente']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM academias WHERE cnpj_academia = :cnpj");
        
        $stmt->bindParam(':cnpj', $data['cnpj_academia'], PDO::PARAM_STR);
    
        $stmt->execute();

        echo json_encode(['success' => 'Academia excluída com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}