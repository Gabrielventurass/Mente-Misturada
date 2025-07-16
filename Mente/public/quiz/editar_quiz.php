<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

$email = $_SESSION['email'];

// Verifica se o quiz_id foi passado
$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">ID do quiz não fornecido.</p>
            <a href="meu_quiz.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar quiz do usuário
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id AND usuario_email = :email");
    $stmt->execute(['quiz_id' => $quiz_id, 'email' => $email]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
            <div class="bg-white shadow rounded p-6">
                <p class="text-gray-800 font-semibold">Quiz não encontrado ou você não tem permissão para editá-lo.</p>
                <a href="meu_quiz.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
            </div>
        </div>');
    }

    // Processar atualização
    // Processar exclusão
if (isset($_POST['excluir_quiz'])) {
    $stmt_delete_quiz = $pdo->prepare("DELETE FROM quizzes_user WHERE id = :quiz_id");
    $stmt_delete_quiz->execute(['quiz_id' => $quiz_id]);
    header("Location: meu_quiz.php");
    exit;
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['excluir_quiz'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($titulo && $descricao) {
        $stmt = $pdo->prepare("UPDATE quizzes_user SET titulo = :titulo, descricao = :descricao WHERE id = :quiz_id");
        $stmt->execute([
            'titulo' => $titulo,
            'descricao' => $descricao,
            'quiz_id' => $quiz_id
        ]);
        $mensagem = "Quiz atualizado com sucesso!";
        // Atualiza $quiz para refletir alterações
        $quiz['titulo'] = $titulo;
        $quiz['descricao'] = $descricao;
    } else {
        $erro = "Título e descrição não podem estar vazios.";
    }
}


} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Quiz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10">
  <div class="max-w-2xl mx-auto bg-white p-6 shadow rounded">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Editar Quiz</h1>

    <?php if (isset($mensagem)): ?>
      <p class="text-green-600 mb-4 font-semibold"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
      <p class="text-red-600 mb-4 font-semibold"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block font-semibold mb-1">Título do Quiz:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($quiz['titulo']) ?>" class="w-full border p-2 rounded" required>
      </div>
      <div>
        <label class="block font-semibold mb-1">Descrição do Quiz:</label>
        <textarea name="descricao" class="w-full border p-2 rounded" required><?= htmlspecialchars($quiz['descricao']) ?></textarea>
      </div>
      <div class="flex space-x-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Atualizar Quiz</button>
        <a href="meu_quiz.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</a>
      </div>
    </form>

    <form method="POST" class="mt-6">
      <button type="submit" name="excluir_quiz" value="1" onclick="return confirm('Tem certeza que deseja excluir este quiz?');" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold">Excluir Quiz</button>
    </form>
  </div>
</body>
</html>
