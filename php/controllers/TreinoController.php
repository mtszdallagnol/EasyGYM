<?php

namespace Controllers;

require_once __DIR__ . '/../models/Treino.php'; // Adjust the path as necessary// If you use this class as well

use Models\Treino;
use Models\TreinoDTO;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_GET)) {
        echo json_encode(["success", Treino::getAll(-1)]);
    } else  {
        echo json_encode(["success", Treino::getByParams($_GET)]);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = new TreinoDTO($data);

    if (Treino::post($curr) < 1) {
        die(json_encode(["info", "Nenhum cadastro realizado"]));
    }

    echo json_encode(["success", "Cadastro realizado com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if(count(Treino::getByParams(["id_academia" => (int)$data["id_academia"] ?? -1])) < 1) {
        die(json_encode(["error", "Treino não existente"]));
    }
    
    $curr = new TreinoDTO($data);

    if (Treino::put($curr) < 1) {
        die(json_encode(["info", "Nenhuma edição realizada"]));
    }

    echo json_encode(["success", "Edição realizada com sucesso"]);
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    $curr = Treino::getByParams(["id_academia" => $data["id_academia"] ?? -1, "id_treino" => $data["id_treino"] ?? null]);

    if (count($curr) < 1) {
        die(json_encode(['error', "Treino não existente"]));
    }

    $curr = $curr[0];
    if (Treino::delete($curr->id_treino, $curr->id_academia) < 1) {
        die(json_encode(["info", "Nenhuma remoção realizada"]));
    }

    echo json_encode(["success","Remoção realizada com sucesso"]);
}