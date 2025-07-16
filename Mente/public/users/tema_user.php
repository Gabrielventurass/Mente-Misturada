<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100">
        <p class="text-red-600 font-semibold">Você precisa estar logado para responder este quiz.</p>
    </div>');
}

$email = $_SESSION['email'];
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">Quiz não encontrado.</p>
            <a href="inicio_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar quiz
    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
            <div class="bg-white shadow rounded p-6">
                <p class="text-gray-800 font-semibold">Quiz não encontrado.</p>
                <a href="inicio_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
            </div>
        </div>');
    }

    // Buscar perguntas
    $stmt = $pdo->prepare("
        SELECT p.id AS pergunta_id, p.texto AS pergunta_texto, a.id AS alternativa_id, a.texto AS alternativa_texto
        FROM pergunta p
        JOIN alternativa a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$perguntas) {
        die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
            <div class="bg-white shadow rounded p-6">
                <p class="text-gray-800 font-semibold">Nenhuma pergunta cadastrada para este quiz.</p>
                <a href="inicio_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
            </div>
        </div>');
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Responder Quiz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">
  <div class="w-full max-w-3xl bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-2 text-gray-800">Respondendo o Quiz: <?= htmlspecialchars($quiz['art']) ?></h1>
    <h3 class="text-gray-600 mb-6">Tema: <?= htmlspecialchars($quiz['tema']) ?></h3>

    <form method="POST" action="salvar_resposta.php" class="space-y-6">
      <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

      <?php
      // Organizar perguntas e alternativas
      $perguntasAgrupadas = [];
      foreach ($perguntas as $item) {
          $perguntasAgrupadas[$item['pergunta_id']]['texto'] = $item['pergunta_texto'];
          $perguntasAgrupadas[$item['pergunta_id']]['alternativas'][] = [
              'id' => $item['alternativa_id'],
              'texto' => $item['alternativa_texto'],
          ];
      }
      ?>

      <?php foreach ($perguntasAgrupadas as $pergunta_id => $p): ?>
        <div class="space-y-2">
          <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['texto']) ?></p>
          <?php foreach ($p['alternativas'] as $alt): ?>
            <label class="flex items-center space-x-2">
              <input type="radio" name="respostas[<?= $pergunta_id ?>]" value="<?= $alt['id'] ?>" required class="text-blue-600">
              <span><?= htmlspecialchars($alt['texto']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="flex space-x-2">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Enviar Respostas</button>
        <button type="button" onclick="window.location.href='inicio_user.php'" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</button>
      </div>
    </form>
  </div>
</body>
</html>
