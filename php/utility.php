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