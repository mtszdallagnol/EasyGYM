<?php

namespace Controllers;

require_once __DIR__ . '/../models/Cargo.php'; // Adjust the path as necessary// If you use this class as well

use Models\Cargo;
use Models\CargoDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Cargo::getAll(-1)]);
    } else  {
        echo json_encode(["success", Cargo::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new CargoDTO($data);

    if (Cargo::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if(count(Cargo::getByParams(["id_academia" => (int)$data["id_academia"] ?? -1])) < 1) {
        die(json_encode(["error", "Academia não existente"]));
    }
    
    $curr = new CargoDTO($data);

    if (Cargo::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = Cargo::getByParams(["id_cargo" => $data["id_cargo"] ?? -1, "id_academia" => $data["id_academia"] ?? null]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Academia ou cargo não existente"]));
    }

    $curr = $curr[0];
    if (Cargo::delete($curr->id_cargo, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}