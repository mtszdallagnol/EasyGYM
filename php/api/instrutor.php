<?php

require '../dbcon.php';
require '../utility.php';

$uploadDir = "D:".DIRECTORY_SEPARATOR."Apache".DIRECTORY_SEPARATOR."Apache24".DIRECTORY_SEPARATOR."htdocs".DIRECTORY_SEPARATOR."upl_imgs".DIRECTORY_SEPARATOR."instrutores_pfp".DIRECTORY_SEPARATOR;

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    if (empty($_GET[''])) {
        try {
            $stmt = $conn->query("SELECT * FROM instrutores");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType;
            for ($i = 0; $i < count($result); $i++) {
                $mimeType = finfo_file($finfo, $result[$i]["url_foto_instrutor"]);
                $result[$i]['url_foto_instrutor'] = "data:" . $mimeType . ";base64," . base64_encode(file_get_contents($result[$i]["url_foto_instrutor"]));
            }
            finfo_close($finfo);

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

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType;
            for ($i = 0; $i < count($result); $i++) {
                $mimeType = finfo_file($finfo, $result[$i]["url_foto_instrutor"]);
                $result[$i]['url_foto_instrutor'] = "data:" . $mimeType . ";base64," . base64_encode(file_get_contents($result[$i]["url_foto_instrutor"]));
            }
            finfo_close($finfo);

            echo json_encode(['success', $result]);
        } catch (PDOException $e) {
            echo json_encode(['error', $e->getMessage()]);
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
    verifyTelefone($_POST['telefone_instrutor'] ?? null);

    verifySex($_POST['sexo_instrutor'] ?? null);
    $_POST['sexo_instrutor'] = strtoupper($_POST['sexo_instrutor']);

    $uploadFile = verifyImage($_FILES['url_foto_instrutor'] ?? null, $uploadDir);
    if (!move_uploaded_file($_FILES['url_foto_instrutor']['tmp_name'], $uploadFile)) {
        echo json_encode(['error' => "Falha ao mover arquivo enviado: " . error_get_last()["message"]]);
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
        $stmt->bindParam(':url_f', $uploadFile, PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(['success' => 'Cadastro realizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    $data = formDataPutRead(file_get_contents("php://input"), 'url_foto_instrutor');
        ##GENERAL VERIFICATION
    verifyCPF($data['cpf_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE cpf_instrutor = :cpf");
        $stmt->bindParam(":cpf", $data['cpf_instrutor'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error', "Instrutor não existente"]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["error", $e->getMessage()]);
        exit;
    }

    verifyCEP($data['cep_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE cep_instrutor = :cep");
        $stmt->bindParam(":cep", $data['cep_instrutor']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 1 || (count($result) === 1 && $result[0]['cep_instrutor'] !== $data['cep_instrutor'])) {
            unlink($data['url_foto_instrutor']['tmp_name']);
            echo json_encode(['error' => 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    verifyAge($data['data_nascimento_instrutor'] ?? null);

    verifyEmail($data['email_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM instrutores WHERE email_instrutor = :email');
        $stmt->bindParam(":email", $data['email_instrutor']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 1|| (count($result) === 1 && $result[0]['email_instrutor'] !== $data['email_instrutor'])) {
            echo json_encode(['error'=> 'Email já existente']);
            exit;
        }
    } catch (PDOException $e) {
        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(['error'=> $e->getMessage()]);
        exit;
    }

    verifyName($data['nome_instrutor'] ?? null);
    verifyTelefone($data['telefone_instrutor'] ?? null);

    $filePath = verifyImage($data['url_foto_instrutor'], $uploadDir);
    $result;
    try {
        $stmt = $conn->prepare("SELECT * FROM instrutores WHERE cpf_instrutor = :cpf");
        $stmt->bindParam(":cpf", $data['cpf_instrutor']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(['error'=> $e->getMessage()]);
        exit;
    }
    if (file_exists($result['url_foto_instrutor'])) unlink($result['url_foto_instrutor']);
    if (file_put_contents($filePath, $data['url_foto_instrutor']['image']) === false) {
        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(['error'=> 'Erro ao salvar dados binários ao arquivo final: ' . error_get_last()['message']]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE instrutores 
            SET cep_instrutor = :cep, data_nascimento_instrutor = :n_date, email_instrutor = :email, nome_instrutor = :nome, telefone_instrutor = :telefone, url_foto_instrutor = :f_url
            WHERE cpf_instrutor = :cpf");
        $stmt->bindParam(":cep", $data['cep_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":n_date", $data['data_nascimento_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":email", $data['email_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":nome", $data['nome_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":telefone", $data['telefone_instrutor'], PDO::PARAM_STR);
        $stmt->bindParam(":f_url", $filePath, PDO::PARAM_STR);
        $stmt->bindParam(":cpf", $data['cpf_instrutor'], PDO::PARAM_STR);
        $stmt->execute();

        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(['success' => "Instrutor atualizado com sucesso!"]);
    } catch (PDOException $e) {
        unlink($data['url_foto_instrutor']['tmp_name']);
        echo json_encode(["error"=> $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
    parse_str(file_get_contents("php://input"), $data);

    verifyCPF($data['cpf_instrutor'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM instrutores WHERE cpf_instrutor = :cpf');
        $stmt->bindParam(":cpf", $data['cpf_instrutor']);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
        if (count($data) <= 0) {
            echo json_encode(['error', "Instrutor não existente"]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["error", $e->getMessage()]);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM instrutores WHERE id_instrutor = :id");
        $stmt->bindParam(":id", $data['id_instrutor']);
        $stmt->execute();

        if (file_exists($data['url_foto_instrutor'])) unlink($data['url_foto_instrutor']);

        echo json_encode(['success','Instrutor excluído com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
    }
}