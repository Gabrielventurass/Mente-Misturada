<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder este quiz.";
    exit;
}

$email = $_SESSION['email'];

// Verificar se o ID do quiz foi passado
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">Quiz não encontrado.</p>
            <a href="comunidade_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar quiz
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
            <div class="bg-white shadow rounded p-6">
                <p class="text-gray-800 font-semibold">Quiz não encontrado.</p>
                <a href="comunidade_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
            </div>
        </div>');
    }

    // Buscar perguntas e alternativas
    $stmt = $pdo->prepare("
        SELECT p.id AS pergunta_id, p.texto AS pergunta_texto
        FROM perguntas_user p
        WHERE p.quiz_id = :quiz_id
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $perguntas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$perguntas_raw) {
        die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
            <div class="bg-white shadow rounded p-6">
                <p class="text-gray-800 font-semibold">Este quiz não possui perguntas cadastradas.</p>
                <a href="comunidade_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
            </div>
        </div>');
    }

    // Montar perguntas e alternativas
    $perguntas = [];
    foreach ($perguntas_raw as $p) {
        $stmtAlt = $pdo->prepare("
            SELECT id, texto
            FROM alternativas
            WHERE pergunta_id = :pergunta_id
        ");
        $stmtAlt->execute(['pergunta_id' => $p['pergunta_id']]);
        $alternativas = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);

        $perguntas[] = [
            'id' => $p['pergunta_id'],
            'texto' => $p['pergunta_texto'],
            'alternativas' => $alternativas
        ];
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Responder Quiz: <?= htmlspecialchars($quiz['titulo']) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10">
  <div class="max-w-3xl mx-auto bg-white p-6 shadow rounded">
    <h1 class="text-2xl font-bold mb-4">Respondendo o Quiz: <?= htmlspecialchars($quiz['titulo']) ?></h1>
    <h3 class="text-gray-700 mb-6">Tema: <?= htmlspecialchars($quiz['tema']) ?></h3>

    <form method="POST" action="salvar_resposta.php" class="space-y-6">
      <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

      <?php foreach ($perguntas as $pergunta): ?>
        <div class="mb-4">
          <p class="font-semibold mb-2"><?= htmlspecialchars($pergunta['texto']) ?></p>
          <?php foreach ($pergunta['alternativas'] as $alt): ?>
            <label class="block mb-1">
              <input type="radio" name="respostas[<?= $pergunta['id'] ?>]" value="<?= $alt['id'] ?>" required class="mr-2">
              <?= htmlspecialchars($alt['texto']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="flex space-x-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Enviar Respostas</button>
        <a href="comunidade_user.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</a>
      </div>
    </form>
  </div>
</body>
</html>
