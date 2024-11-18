<?php

namespace Controllers;

require_once __DIR__ . '/../models/Equipamento.php'; // Adjust the path as necessary// If you use this class as well

use Models\Equipamento;
use Models\EquipamentoDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Equipamento::getAll(-1)]);
    } else  {
        echo json_encode(["success", Equipamento::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new EquipamentoDTO($data);

    if (Equipamento::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if(count(Equipamento::getByParams(["id_academia" => (int)$data["id_academia"] ?? -1])) < 1) {
        die(json_encode(["error", "Academia não existente"]));
    }
    
    $curr = new EquipamentoDTO($data);

    if (Equipamento::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    $curr = Equipamento::getByParams(["id_academia" => $data["id_academia"] ?? -1, "id_equipamento" => $data["id_equipamento"] ?? null]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Academia ou Equipamento não existente"]));
    }

    $curr = $curr[0];
    if (Equipamento::delete($curr->id_equipamento, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}