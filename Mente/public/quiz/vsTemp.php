<?php
require_once "../../src/class/quiz.class.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100">
        <p class="text-red-600 font-semibold">Você precisa estar logado para acessar o quiz.</p>
    </div>');
}

$email = $_SESSION['email'];

$quizObj = new Quiz();
$quizAleatorio = $quizObj->obterQuizAleatorio($email);

if (!$quizAleatorio || !isset($quizAleatorio['perguntas']) || empty($quizAleatorio['perguntas'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">Nenhum quiz disponível no momento.</p>
            <a href="../users/inicio_user.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Quiz Contra o Tempo</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">
  <div class="w-full max-w-3xl bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-2 text-gray-800">Quiz: <?= htmlspecialchars($quizAleatorio['tema']) ?></h1>
    <p class="text-gray-700 mb-6 whitespace-pre-line"><?= htmlspecialchars($quizAleatorio['art']) ?></p>

    <form method="POST" action="SR_temp.php" class="space-y-6">
      <input type="hidden" name="quiz_id" value="<?= $quizAleatorio['id'] ?>">
      <input type="hidden" name="tempo" id="tempo">

      <?php foreach ($quizAleatorio['perguntas'] as $p): ?>
        <div class="space-y-2">
          <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['texto']) ?></p>
          <?php foreach ($p['alternativas'] as $alt): ?>
            <label class="flex items-center space-x-2">
              <input type="radio" name="respostas[<?= $p['id'] ?>]" value="<?= htmlspecialchars($alt['texto']) ?>" required class="text-blue-600">
              <span><?= htmlspecialchars($alt['texto']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

      <div class="flex space-x-2">
        <button type="submit" onclick="registrarTempo()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Enviar Respostas</button>
        <button type="button" onclick="window.location.href='../users/inicio_user.php'" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</button>
      </div>

      <p class="text-sm text-gray-500">Tempo decorrido: <span id="cronometro">0</span> segundos</p>
    </form>
  </div>

  <script>
    let tempo = 0;
    let intervalo;
    window.onload = function () {
      intervalo = setInterval(() => {
        tempo++;
        document.getElementById('cronometro').textContent = tempo;
      }, 1000);
    };
    function registrarTempo() {
      clearInterval(intervalo);
      document.getElementById('tempo').value = tempo;
    }
  </script>
</body>
</html>
