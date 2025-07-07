<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder quizzes.";
    exit;
}

$email = $_SESSION['email'];

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter todos os quizzes disponíveis na comunidade
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user");
    $stmt->execute();
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Responder Quiz</title>
</head>
<body>
    <h1>Quizzes da Comunidade</h1>
    <h3>Escolha um quiz para responder:</h3>

    <?php if (count($quizzes) > 0): ?>
        <ul>
            <?php foreach ($quizzes as $quiz): ?>
                <li>
                    <!-- Redireciona para a página de responder_quiz.php passando o quiz_id como parâmetro -->
                    <a href="responder_quiz.php?quiz_id=<?= $quiz['id'] ?>">
                        <?= htmlspecialchars($quiz['titulo']) ?> (Tema: <?= htmlspecialchars($quiz['tema']) ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Não há quizzes disponíveis no momento.</p>
    <?php endif; ?>
</body>
</html>
