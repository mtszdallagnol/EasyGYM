<?php

namespace Controllers;

require_once __DIR__ . '/../models/Aula.php'; // Adjust the path as necessary// If you use this class as well

use Models\Aula;
use Models\AulaDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Aula::getAll(-1)]);
    } else  {
        echo json_encode(["success", Aula::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new AulaDTO($data);

    if (Aula::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if(count(Aula::getByParams(["id_academia" => (int)$data["id_academia"] ?? -1])) < 1) {
        die(json_encode(["error", "Academia não existente"]));
    }
    
    $curr = new AulaDTO($data);

    if (Aula::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = Aula::getByParams(["id_academia" => $data["id_academia"] ?? -1, "id_aula" => $data["id_aula"] ?? null]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Academia não existente"]));
    }

    $curr = $curr[0];
    if (Aula::delete($curr->id_aula, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}