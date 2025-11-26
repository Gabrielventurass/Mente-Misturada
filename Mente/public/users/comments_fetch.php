<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "quiz_id inválido"]);
    exit;
}
$quiz_id = (int) $_GET['quiz_id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
      SELECT c.id, c.coment, c.criado_em, u.nome
      FROM comentario c
      LEFT JOIN usuario u ON c.usuario_id = u.id
      WHERE c.quiz_id = ?
      ORDER BY c.id DESC
      LIMIT 200
    ");
    $stmt->execute([$quiz_id]);
    $coms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($coms);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro no servidor: " . $e->getMessage()]);
}
