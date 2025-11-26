<?php

declare(strict_types=1);

session_start();

// Somente administradores devem acessar (ajuste conforme necessário)
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
// }

$id = (int) ($_GET['id'] ?? 0);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o quiz existe
    $stmt = $pdo->prepare("SELECT id FROM quiz WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        die("Quiz não encontrado.");
    }

    // Excluir respostas associadas
    $stmt = $pdo->prepare("DELETE FROM resposta_usuario WHERE quiz_id = ?");
    $stmt->execute([$id]);

    // Excluir o quiz
    $stmt = $pdo->prepare("DELETE FROM quiz WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: gerenciar_quiz.php?msg=excluido");
    exit;
} catch (PDOException $e) {
    die("Erro ao excluir quiz: " . $e->getMessage());
}
