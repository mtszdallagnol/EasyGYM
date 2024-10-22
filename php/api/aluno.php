<?php

require "../dbcon.php";
require "../utility.php";

$uploadDir = "D:".DIRECTORY_SEPARATOR."Apache".DIRECTORY_SEPARATOR."Apache24".DIRECTORY_SEPARATOR."htdocs".DIRECTORY_SEPARATOR."upl_imgs".DIRECTORY_SEPARATOR."alunos_pfp".DIRECTORY_SEPARATOR;

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    if (empty($_GET[''])) {
        try {
            $stmt = $conn->query("SELECT * FROM alunos");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType;
            for ($i = 0; $i < count($result); $i++) {
                $mimeType = finfo_file($finfo, $result[$i]["url_foto_aluno"]);
                $result[$i]['url_foto_aluno'] = "data:" . $mimeType . ";base64," . base64_encode(file_get_contents($result[$i]["url_foto_aluno"]));
            }
            finfo_close($finfo);

            echo json_encode(['success', $result]);
        } catch (PDOException $e) {
            echo json_encode(['error', $e->getMessage()]);
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

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType;
            for ($i = 0; $i < count($result); $i++) {
                $mimeType = finfo_file($finfo, $result[$i]["url_foto_aluno"]);
                $result[$i]['url_foto_aluno'] = "data:" . $mimeType . ";base64," . base64_encode(file_get_contents($result[$i]["url_foto_aluno"]));
            }
            finfo_close($finfo);

            echo json_encode(['success', $result]);
        } catch (PDOException $e) {
            echo json_encode(['error', $e->getMessage()]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    verifyCEP($_POST['cep_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE cep_aluno = :cep');
        $stmt->bindParam(':cep', $_POST['cep_aluno'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error', 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }

    verifyCPF($_POST['cpf_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE cpf_aluno = :cpf');
        $stmt->bindParam(':cpf', $_POST['cpf_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error', 'CPF já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;   
    }

    verifyAge($_POST['data_nascimento_aluno'] ?? null);
    
    verifyEmail($_POST['email_aluno'] ?? null);
    try {
        $stmt = $conn->prepare('SELECT * FROM alunos WHERE email_aluno = :email');
        $stmt->bindParam(':email', $_POST['email_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0) {
            echo json_encode(['error', 'Email já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }

    verifyName($_POST['nome_aluno'] ?? null);
    verifySex($_POST['sexo_aluno'] ?? null);
    verifyTelefone($_POST['telefone_aluno'] ?? null);

    if (!isset($_POST['id_academia'])) {
        echo json_encode(['error', "Academia inválida"]);
        exit;
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE id_academia = :id");
        $stmt->bindParam(":id", $_POST['id_academia']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error', 'Academia não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }

    $uploadFile = verifyImage($_FILES['url_foto_aluno'] ?? null, $uploadDir);
    if (!move_uploaded_file($_FILES['url_foto_aluno']['tmp_name'], $uploadFile)) {
        echo json_encode(['error', "Falha ao mover arquivo enviado: " . error_get_last()["message"]]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO alunos (cep_aluno, cpf_aluno, data_nascimento_aluno, email_aluno, n_inscricao, nome_aluno, sexo_aluno, telefone_aluno, url_foto_aluno, id_academia) 
            VALUES (:cep, :cpf, :data_n, :email, :inscricao, :nome, :sexo, :telefone, :f_url, :id)");
        $stmt->bindParam(':cep', $_POST['cep_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':cpf', $_POST['cpf_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':data_n', $_POST['data_nascimento_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $_POST['email_aluno'], PDO::PARAM_STR);

        $hash = substr(sha1($_POST['cpf_aluno']), 0, 20);
        $stmt->bindParam(':inscricao', $hash, PDO::PARAM_STR);

        $stmt->bindParam(':nome', $_POST['nome_aluno'], PDO::PARAM_STR);
        
        $sexo = strtoupper($_POST['sexo_aluno']);
        $stmt->bindParam(':sexo', $sexo, PDO::PARAM_STR_CHAR);

        $stmt->bindParam(":telefone", $_POST['telefone_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":f_url", $uploadFile, PDO::PARAM_STR);
        $stmt->bindParam(":id", $_POST['id_academia'], PDO::PARAM_INT);
        
        $stmt->execute();

        echo json_encode(['success', 'Cadastro realizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === "PUT") {
    $data = formDataPutRead(file_get_contents("php://input"), 'url_foto_aluno');

    verifyCEP($data['cep_aluno'] ?? null);
    try {
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE cep_aluno = :cep");
        $stmt->bindParam(":cep", $data['cep_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 1 || (count($result) === 1 && $result[0]['cep_aluno'] !== $data['cep_aluno'])) {
            echo json_encode(['error', 'CEP já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
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
            echo json_encode(['error', 'Email já existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }
    verifyName($data['nome_aluno'] ?? null);
    verifyTelefone($data['telefone_aluno'] ?? null);

    if (!isset($data['id_academia'])) {
        echo json_encode(['error', 'Academia não existente']);
        exit;
    }
    try {
        $stmt = $conn->prepare("SELECT * FROM academias WHERE id_academia = :id");
        $stmt->bindParam(":id", $data['id_academia']);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) <= 0) {
            echo json_encode(['error', 'Academia não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }

    $filePath = verifyImage($data['url_foto_aluno'], $uploadDir);
    $result;
    try {
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE cpf_aluno = :cpf");
        $stmt->bindParam(":cpf", $data['cpf_aluno']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        unlink($data['url_foto_aluno']['tmp_name']);
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }
    if (file_exists($result['url_foto_aluno'])) unlink($result['url_foto_aluno']);
    if (file_put_contents($filePath, $data['url_foto_aluno']['image']) === false) {
        unlink($data['url_foto_aluno']['tmp_name']);
        echo json_encode(['error', 'Erro ao salvar dados binários ao arquivo final: ' . error_get_last()['message']]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE alunos 
        SET cep_aluno = :cep, data_nascimento_aluno = :nascimento, email_aluno = :email, nome_aluno = :nome, telefone_aluno = :telefone, url_foto_aluno = :f_url
        WHERE cpf_aluno = :cpf");

        $stmt->bindParam(':cep', $data['cep_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':nascimento', $data['data_nascimento_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":nome", $data['nome_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":telefone", $data['telefone_aluno'], PDO::PARAM_STR);
        $stmt->bindParam(":f_url", $filePath, PDO::PARAM_STR);
        $stmt->bindParam(":cpf", $data['cpf_aluno'], PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(['success', 'Aluno atualizado com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    verifyCPF($data['cpf_aluno'] ?? null);
    $result;
    try {
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE cpf_aluno = :cpf");
        $stmt->bindParam(":cpf", $data['cpf_aluno']);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) <= 0) {
            echo json_encode(['error', 'CPF não existente']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM alunos WHERE cpf_aluno = :cpf");
        $stmt->bindParam(':cpf', $data['cpf_aluno']);
        $stmt->execute();

        if (file_exists($result['url_foto_aluno'])) unlink($result['url_foto_aluno']);
        
        echo json_encode(['success', 'Aluno excluído com sucesso']);
    } catch (PDOException $e) {
        echo json_encode(['error', $e->getMessage()]);
        exit;
    }
}