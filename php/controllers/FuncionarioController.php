<?php

namespace Controllers;

require_once __DIR__ . '/../models/Funcionario.php'; // Adjust the path as necessary// If you use this class as well

use Models\Funcionario;
use Models\FuncrionarioDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Funcionario::getAll(-1)]);
    } else  {
        echo json_encode(["success", Funcionario::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new FuncrionarioDTO($data);

    if (Funcionario::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if(count(Funcionario::getByParams(["id_academia" => (int)$data["id_academia"] ?? -1])) < 1) {
        die(json_encode(["error", "Academia não existente"]));
    }
    
    $curr = new FuncrionarioDTO($data);

    if (Funcionario::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = Funcionario::getByParams(["id_academia" => $data["id_academia"] ?? -1, "id_funcionario" => $data["id_funcionario"] ?? null]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Academia não existente"]));
    }

    $curr = $curr[0];
    if (Funcionario::delete($curr->id_funcionario, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}