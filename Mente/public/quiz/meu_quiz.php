<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

include 'menu_quiz.php'; // Ajuste o caminho conforme necessário

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultar quizzes do usuário
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE usuario_email = :email");
    $stmt->execute(['email' => $_SESSION['email']]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se não vier nada, garante array vazio
    if (!$quizzes) {
        $quizzes = [];
    }

} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Quizzes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10">
  <div class="max-w-4xl mx-auto bg-white p-6 shadow rounded">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Meus Quizzes</h1>

    <div class="mb-4 flex justify-between">
        <a href="criar_quiz.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Criar Novo Quiz</a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold">Deslogar</a>
    </div>

    <?php if (count($quizzes) > 0): ?>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2 text-left">Título</th>
                    <th class="border p-2 text-left">Descrição</th>
                    <th class="border p-2 text-left">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2"><?= htmlspecialchars($quiz['titulo']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($quiz['descricao']) ?></td>
                        <td class="border p-2">
                            <a href="editar_quiz.php?quiz_id=<?= $quiz['id'] ?>" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-700">Você ainda não criou nenhum quiz.</p>
    <?php endif; ?>
  </div>
</body>
</html>
