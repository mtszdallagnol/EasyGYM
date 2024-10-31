<?php

namespace Controllers;

require_once __DIR__ . '/../models/Academia.php'; // Adjust the path as necessary// If you use this class as well

use Models\Academia;
use Models\AcademiaDTO;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $result = Academia::post(new AcademiaDTO($data));

    if ($result > 0) {
        echo json_encode(["success", "Academia registrada com sucesso"]);
    }
}