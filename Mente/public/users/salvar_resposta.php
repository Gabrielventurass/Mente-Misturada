<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para enviar as respostas.";
    exit;
}

$email = $_SESSION['email'];  // Obtemos o email do usuário logado

$quiz_id = $_POST['quiz_id'] ?? null;
$respostas = $_POST['respostas'] ?? [];

if (!$quiz_id || empty($respostas)) {
    echo "Respostas incompletas.";
    exit;
}

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Salvar as respostas do usuário
    foreach ($respostas as $pergunta_id => $alternativa_id) {
        // Inserir a resposta na tabela resposta_comunidade
        $stmt = $pdo->prepare("
            INSERT INTO resposta_comunidade (usuario_email, quiz_id, pergunta_id, alternativa_id)
            VALUES (:usuario_email, :quiz_id, :pergunta_id, :alternativa_id)
        ");
        $stmt->execute([
            'usuario_email' => $email,
            'quiz_id' => $quiz_id,
            'pergunta_id' => $pergunta_id,
            'alternativa_id' => $alternativa_id
        ]);
    }

    echo "Respostas enviadas com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
