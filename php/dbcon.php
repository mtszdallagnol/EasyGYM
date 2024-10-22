<?php
 
$env = parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . "env.ini", true);

$host = $env['database']['hostname'];
$db = $env['database']['database'];
$port = 3306;
$user = $env['database']['username'];
$psw = $env['database']['password'];

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $psw);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro na conexão com o banco de dados: " . $e->getMessage();
    exit;
}