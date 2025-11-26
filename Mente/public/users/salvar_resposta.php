<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para enviar as respostas.";
    exit;
}

$email = $_SESSION['email'];

// Pega o id do usuário a partir do email
$pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email");
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

$id = $usuario['id']; // id do usuário logado

$quiz_id = $_POST['quiz_id'] ?? null;
$respostas = $_POST['respostas'] ?? [];

if (!$quiz_id || empty($respostas)) {
    echo "Respostas incompletas.";
    exit;
}

// Agora salva normalmente as respostas
foreach ($respostas as $pergunta_id => $alternativa_id) {
    $stmt = $pdo->prepare("
        INSERT INTO resposta_comunidade (usuario_id, quiz_id, pergunta_id, alternativa_id)
        VALUES (:usuario_id, :quiz_id, :pergunta_id, :alternativa_id)
    ");
    $stmt->execute([
        'usuario_id' => $id,
        'quiz_id' => $quiz_id,
        'pergunta_id' => $pergunta_id,
        'alternativa_id' => $alternativa_id
    ]);
}

echo "Respostas enviadas com sucesso!";
?>