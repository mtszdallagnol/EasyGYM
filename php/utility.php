<?php

function verifyCEP($cep) {
    $cep_verification = @file_get_contents("https://viacep.com.br/ws/" . $cep . "/json/");
    $cep_verification = $cep_verification == false ? $cep_verification : json_decode($cep_verification, true);

    if ($cep_verification == false || (isset($cep_verification['erro']) && $cep_verification['erro'] == true)) {
        echo json_encode(['error'=> 'CEP inválido']);
        exit;
    }
}

function verifyTelefone($telefone) {
    if (empty($telefone)) {
        echo json_encode(['error'=> 'Número de telefone inválido']);
        exit;
    }
    if (strlen($telefone) > 14) {
        echo json_encode(['error'=> 'Número de telefone inválido']);
        exit;
    }
}

function verifyCPF($cpf) {
    if (strlen($cpf) != 11) {
        echo json_encode(['error' => 'CPF inválido']);
        exit;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
        echo json_encode(['error' => 'CPF inválido']);
        exit;
    }

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            echo json_encode(['error' => 'CPF inválido']);
            exit;
        }
    }
}

function verifyAge($birth) {
    if (empty($birth)) {
        echo json_encode(['error' => 'Data de nascimento inválida']);
        exit;
    }

    $birthOBJ = DateTime::createFromFormat('Y-m-d', $birth);
    if (!$birthOBJ || $birthOBJ->format('Y-m-d') !== $birth) {
        echo json_encode(['error' => 'Formato de data de nascimento inválido']);
        exit;
    }

    $currentDate = new Datetime();
    $age = $currentDate->diff($birthOBJ)->y;

    if ($birthOBJ > $currentDate) {
        echo json_encode(['error' => 'Data de nascimento está no futuro']);
        exit;
    }

    if ($age > 120) {
        echo json_encode(['error' => 'Idade ultrapassa o limite de 120 anos']);
        exit;
    }
}

function verifyEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Email inválido']);
        exit;
    }
}

function verifyName($name) {
    if (empty($name)) {
        echo json_encode(['error'=> 'Nome inválido']);
        exit;
    }
    if (strlen($name) > 50) {
        echo json_encode(['error'=> 'Nome maior do que 50 caracteres']);
        exit;
    }
}

function verifySex($sex) {
    if (empty($sex)) {
        echo json_encode(['error' => 'Sexo inválido']);
        exit;
    }

    $sex = strtoupper($sex);

    if ($sex !== "M" && $sex !== "F") {
        echo json_encode(['error' => 'Sexo inválido']);
        exit;
    }  
}

function verifyImage($image, $dir): string {
    if (!$image || empty($image['name'])) {
        echo json_encode(['error'=> 'Selecione uma imagem']);
        exit;
    }

    $file = new finfo(FILEINFO_MIME_TYPE);
    $mime = $file->file($image['tmp_name']);
    
    if ($mime === false) {
        json_encode(['error' => "Erro ao detectar tipo MIME do arquivo: " . error_get_last()['message']]);
        exit;
    }

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    if (!in_array($mime, $allowed)) {
        echo json_encode(['error'=> 'Formato de arquivo inválido. [JPEG, JPG, PNG, GIF]']);
        exit;
    }

    $uploadMaxSize = 2;
    if ($image['size'] > $uploadMaxSize ** 1024) {
        echo json_encode(['error'=> 'Imagem não pode ser maior que '.$uploadMaxSize."MB"]);
        exit;
    }

    $allowed_characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $curr_attempt = 0;
    while ($curr_attempt < 10) {
        $temp = "";
        for ($i = 0; $i < 16; $i++) {
            $temp .= $allowed_characters[random_int(0, strlen($allowed_characters) -1)];
        }
        $temp = $dir . $temp . "." . substr($mime, strpos($mime, '/') + 1);
        if (!file_exists($temp)) {
            finfo_close($file);
            return $temp;
        }
        $curr_attempt++;
    }

    finfo_close($file);
    echo json_encode(["error"=> "Não foi possível gerar o arquivo. Por favor, tente novamente"]);
    exit;
}

function formDataPutRead($input, $context): array {
    preg_match_all('/name="([^"]+)"\s*([^\n]+)/', $input, $matches);
    $data = [];
    foreach ($matches[1] as $index => $name) $data[$name] = trim($matches[2][$index]);
    $data[$context] = [];
    preg_match('/filename="([^"]*)"/', $input, $temp_filename);
    $data[$context]['name'] = $temp_filename[1];
    preg_match('/Content-Type:\s*image\/[a-zA-Z]+\s*(.*)\s*----------------------------\d+/s', $input, $temp_binaryImage);
    $data[$context]['image'] = $temp_binaryImage[1];
    $data[$context]['tmp_name'] = tempnam(sys_get_temp_dir(), 'img');
    if (file_put_contents($data[$context]['tmp_name'], $data[$context]['image']) === false) {
        unlink($data[$context]['tmp_name']);
        echo json_encode(['error' => 'Erro ao transcrever dados binários à arquivo temporário: ' . error_get_last()['message']]);
        exit;
    }
    $data[$context]['size'] = filesize($data[$context]['tmp_name']);
    return $data;
}