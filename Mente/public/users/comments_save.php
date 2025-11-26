<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método inválido"]);
    exit;
}

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Você precisa estar logado."]);
    exit;
}

$quiz_id = isset($_POST['quiz_id']) ? (int) $_POST['quiz_id'] : 0;
$coment = isset($_POST['coment']) ? trim($_POST['coment']) : '';

if ($quiz_id <= 0 || $coment === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Dados inválidos"]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // pega id do usuario pela sessão (email)
    $stmt = $pdo->prepare("SELECT id, nome FROM usuario WHERE email = ? LIMIT 1");
    $stmt->execute([$_SESSION['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // sessão inválida em relação ao DB
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Usuário não encontrado."]);
        exit;
    }
    $usuario_id = $user['id'];
    $nome = $user['nome'] ?? null;

    // insere comentário
    $ins = $pdo->prepare("INSERT INTO comentario (usuario_id, quiz_id, coment) VALUES (?, ?, ?)");
    $ins->execute([$usuario_id, $quiz_id, $coment]);
    $insertedId = $pdo->lastInsertId();

    // busca o comentário inserido (para enviar de volta com timestamp)
    $sel = $pdo->prepare("SELECT c.id, c.coment, c.criado_em, u.nome
                         FROM comentario c
                         LEFT JOIN usuario u ON c.usuario_id = u.id
                         WHERE c.id = ? LIMIT 1");
    $sel->execute([$insertedId]);
    $comment = $sel->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "comment" => $comment]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro no servidor: " . $e->getMessage()]);
}
