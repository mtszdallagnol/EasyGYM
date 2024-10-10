<?php

require '../dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    if (empty($_GET[''])) {
        try {
            $stmt = $conn->query("SELECT * FROM instrutores");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => $result]);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        $query = "SELECT * FROM instrutores WHERE ";

        $params = [];

        if (isset($_GET['cpf_instrutor'])) {
            $query .= "cpf_instrutor = :cpf";
            $params[":cpf"] = $_GET['cpf_instrutor'];
        } elseif (isset($_GET['email_instrutor'])) {
            $query .= "email_instrutor = :email";
            $params[":email"] = $_GET['email_instrutor'];
        } elseif (isset($_GET["nome_instrutor"])) {
            $query .= "nome_instrutor = :nome";
            $params[":nome"] = $_GET['nome_instrutor'];
        } elseif (isset($_GET["telefone_instrutor"])) {
            $query .= "telefone_instrutor = :telefone";
            $params[":telefone"] = $_GET["telefone_instrutor"];
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
    verifyCEP($_POST['cep_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE cep_instrutor = :cep");
        $stmt->bindParam(":cep", $_POST['cep_instrutor']);
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

    verifyAge($_POST['data_nascimento_instrutor'] ?? null);

    verifyCPF($_POST['cpf_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE cpf_instrutor = :cpf");
        $stmt->bindParam(":cpf", $_POST['cpf_instrutor']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error' => "CPF já existente"]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    verifyEmail($_POST['email_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE email_instrutor = :email");
        $stmt->bindParam(":email", $_POST['email_intrutor']);
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

    verifyName($_POST['nome_instrutor'] ?? null);
    verifyTelefone($_POST['telefone_intrutor'] ?? null);

    verifySex($_POST['sexo_instrutor'] ?? null);
    $_POST['sexo_instrutor'] = strtoupper($_POST['sexo_instrutor']);

    $uploadDir = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../upl_imgs/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        if (!move_uploaded_file($_FILE['image']['tmp_name'], $uploadFile)) {
            echo json_encode(['error' => "Falha ao mover arquivo enviado"]);
            exit;
        }
    } else {
        echo json_encode(['error' => "Arquivo inválido"]);
        exit;
    }

    ##POST EVERYTHING
    try {
        $stmt = $conn->prepare("INSERT INTO instrutores 
            (cep_instrutor, cpf_instrutor, data_nascimento_instrutor, email_instrutor, nome_instrutor, sexo_instrutor, telefone_instrutor, url_foto_instrutor)
            VALUES (:cep, :cpf, :data_n, :email, :nome, :sexo, :telefone, :url_f)");

        $stmt->bindParam(':cep', $_POST['cep_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(':cpf', $_POST['cpf_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(':data_n', $_POST['data_nascimento_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $_POST['email_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":nome", $_POST['nome_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(':sexo', $_POST['sexo_instrutor'], PDO::PARAM_STR_CHAR);
        $stmt->bindParam(':telefone', $_POST['telefone_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(':url_f', $uploadDir, PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(['success' => 'Cadastro realizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === "PUT")