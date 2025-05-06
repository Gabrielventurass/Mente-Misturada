<?php
session_start();

// Verificação de segurança opcional: apenas admins
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
// }

// Excluir as respostas associadas ao quiz


$id = (int) $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o quiz existe
    $stmt = $pdo->prepare("SELECT id FROM quiz WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        die("Quiz não encontrado.");
    }

// Excluir as respostas associadas ao quiz
$stmt = $pdo->prepare("DELETE FROM resposta_usuario WHERE quiz_id = ?");
$stmt->execute([$id]);

// Agora, pode excluir o quiz sem problemas
$stmt = $pdo->prepare("DELETE FROM quiz WHERE id = ?");
$stmt->execute([$id]);


    header("Location: gerenciar_quiz.php?msg=excluido");
    exit;

} catch (PDOException $e) {
    die("Erro ao excluir quiz: " . $e->getMessage());
}
