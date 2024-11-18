<?php

namespace Controllers;

require_once __DIR__ . '/../models/Aluno.php'; // Adjust the path as necessary// If you use this class as well

use Models\Aluno;
use Models\AlunoDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Aluno::getAll(-1)]);
    } else  {
        echo json_encode(["success", Aluno::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new AlunoDTO($data);

    if (Aluno::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new AlunoDTO($data);
    
    if (Aluno::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = Aluno::getByParams(["id_academia" => $data["id_academia"] ?? -1, 
            "id_aluno" => $data["id_aluno"] ?? -1]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Aluno não existente"]));
    }

    $curr = $curr[0];
    if (Aluno::delete($curr->id_aluno, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}