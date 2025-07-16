<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder quizzes.";
    exit;
}

$email = $_SESSION['email'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    <title>Quizzes da Comunidade</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-md rounded p-6 w-full max-w-2xl">
        <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">Quizzes da Comunidade</h1>
        <p class="mb-6 text-center text-gray-600">Escolha um quiz para responder:</p>

        <?php if (count($quizzes) > 0): ?>
            <ul class="space-y-3">
                <?php foreach ($quizzes as $quiz): ?>
                    <li>
                        <a href="responder_quiz.php?quiz_id=<?= $quiz['id'] ?>" 
                           class="block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded text-center">
                            <?= htmlspecialchars($quiz['titulo']) ?> 
                            <span class="text-sm font-normal">(Tema: <?= htmlspecialchars($quiz['tema']) ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 text-center">Não há quizzes disponíveis no momento.</p>
        <?php endif; ?>
    </div>
</body>
</html>
