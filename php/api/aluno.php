<?php

require "../dbcon.php";
require "../utility.php";

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    if (empty($_GET[''])) {
        try {
            $stmt = $conn->query("SELECT * FROM alunos");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => $result]);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        $query = "SELECT * FROM alunos WHERE ";

        $params = [];

        if (isset($_GET['cep_aluno'])) {
            $query .= "cep_aluno = :cep";
            $params[':cep'] = $_GET['cep_aluno'];
        } elseif (isset($_GET['cpf_aluno'])) {
            $query .= "cpf_aluno = :cpf";
            $params[':cpf'] = $_GET['cpf_aluno'];
        } elseif (isset($_GET['data_nascimento_aluno'])) {
            $query .= 'data_nascimento_aluno = :data';
            $params[':data'] = $_GET['data_nascimento_aluno'];
        } elseif (isset($_GET['email_aluno'])) {
            $query .= "email_aluno = :email";
            $params[':email'] = $_GET['email_aluno'];
        } elseif (isset($_GET['n_inscricao_aluno'])) {
            $query .= "n_inscricao = :nr";
            $params[':nr'] = $_GET['n_inscricao_aluno'];
        } elseif (isset($_GET['nome_aluno'])) {
            $query .= "nome_aluno = :nome";
            $params[':nome'] = $_GET['nome_aluno'];
        } else {
            $queery .= "telefone_aluno = :telefone";
            $params[':telefone'] = $_GET['telefone_aluno'];
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

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    verifyCEP($data['cep_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE cep_aluno = :cep');
        $stmt->bindParam(':cep', $data['cep_aluno'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error' => 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    verifyCPF($data['cpf_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE cpf_aluno = :cpf');
        $stmt->bindParam(':cpf', $data['cpf_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error' => 'CPF já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;   
    }

    verifyAge($data['data_nascimento_aluno'] ?? null);
    
    verifyEmail($data['email_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE email_aluno = :email');
        $stmt->bindParam(':email', $data['email_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error' => 'Email já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    verifyName($data['nome_aluno'] ?? null);
    verifySex($data['sexo_aluno'] ?? null);
    verifyTelefone($data['telefone_aluno'] ?? null);

    if (!isset($data['id_academia'])) {
        echo json_encode(['error' => "Academia inválida"]);
        exit;
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE id_academia = :id");
        $stmt->bindParam(":id", $data['id_academia']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error' => 'Academia não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO alunos (cep_aluno, cpf_aluno, data_nascimento_aluno, email_aluno, n_inscricao, nome_aluno, sexo_aluno, telefone_aluno, id_academia) 
            VALUES (:cep, :cpf, :data_n, :email, :inscricao, :nome, :sexo, :telefone, :id)");
        $stmt->bindParam(':cep', $data['cep_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':cpf', $data['cpf_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':data_n', $data['data_nascimento_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email_aluno'], PDO::PARAM_STR);

        $hash = substr(sha1($data['cpf_aluno']), 0, 20);
        $stmt->bindParam(':inscricao', $hash, PDO::PARAM_STR);

        $stmt->bindParam(':nome', $data['nome_aluno'], PDO::PARAM_STR);
        
        $sexo = strtoupper($data['sexo_aluno']);
        $stmt->bindParam(':sexo', $sexo, PDO::PARAM_STR_CHAR);

        $stmt->bindParam(":telefone", $data['telefone_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":id", $data['id_academia']);
        
        $stmt->execute();

        echo json_encode(['success' => 'Cadastro realizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    verifyCEP($data['cep_aluno'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE cep_aluno = :cep");
        $stmt->bindParam(":cep", $data['cep_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0 || (count($result) === 1 && $result[0]['cep_aluno'] !== $data['cep_aluno'])) {
            echo json_encode(['error' => 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    verifyCPF($data['cpf_aluno'] ?? null);
    verifyAge($data['data_nascimento_aluno'] ?? null);

    verifyEmail($data['email_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE email_aluno = :email');
        $stmt->bindParam(":email", $data['email_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 1 || (count($result) === 1 && $result[0]['email_aluno'] !== $data['email_aluno'])) {
            echo json_encode(['error' => 'Email já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
    verifyName($data['nome_aluno'] ?? null);
    verifySex($data['sexo_aluno'] ?? null);
    verifyTelefone($data['telefone_aluno'] ?? null);

    if (!isset($data['id_academia'])) {
        echo json_encode(['error' => 'Academia não existente']);
        exit;
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE id_academia = :id");
        $stmt->bindParam(":id", $data['id_academia']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error' => 'Academia não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE alunos SET cep_aluno = :cep, data_nascimento_aluno = :nascimento, email_aluno = :email, nome_aluno = :nome, telefone_aluno = :telefone 
            WHERE cpf_aluno = :cpf");

        $stmt->bindParam(':cep', $data['cep_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':nascimento', $data['data_nascimento_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":nome", $data['nome_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":telefone", $data['telefone_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":cpf", $data['cpf_aluno'], PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(['success', $data]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    verifyCPF($data['cpf_aluno'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE cpf_aluno = :cpf");
        $stmt->bindParam(":cpf", $data['cpf_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error' => 'CPF não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM alunos WHERE cpf_aluno = :cpf");

        $stmt->bindParam(':cpf', $data['cpf_aluno']);

        $stmt->execute();
        
        echo json_encode(['success' => 'Aluno excluído com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}