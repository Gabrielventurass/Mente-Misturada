<?php
require_once "../../src/class/quiz.class.php";
session_start();

if (!isset($_SESSION['email'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100"><p class="text-red-600 font-semibold">Você precisa estar logado para enviar respostas.</p></div>');
}

$email = $_SESSION['email'];
$quiz_id = $_POST['quiz_id'] ?? null;
$respostas = $_POST['respostas'] ?? [];
$tempo = $_POST['tempo'] ?? 0;

if (!$quiz_id || empty($respostas)) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100"><p class="text-red-600 font-semibold">Respostas incompletas.</p></div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT p.id AS pergunta_id, a.texto AS resposta_correta
        FROM pergunta p
        JOIN alternativa a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id AND a.correta = 1
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $gabarito = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gabaritoMap = [];
    foreach ($gabarito as $item) {
        $gabaritoMap[$item['pergunta_id']] = $item['resposta_correta'];
    }

    $acertos = 0;
    foreach ($respostas as $pergunta_id => $resposta_usuario) {
        if (isset($gabaritoMap[$pergunta_id]) && $gabaritoMap[$pergunta_id] === $resposta_usuario) {
            $acertos++;
        }
    }

    $pdo->prepare("
        INSERT INTO resposta_usuario (usuario_email, quiz_id, acertos, tempo)
        VALUES (:email, :quiz_id, :acertos, :tempo)
    ")->execute([
        'email' => $email,
        'quiz_id' => $quiz_id,
        'acertos' => $acertos,
        'tempo' => intval($tempo)
    ]);
} catch (PDOException $e) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100"><p class="text-red-600 font-semibold">Erro: ' . htmlspecialchars($e->getMessage()) . '</p></div>');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Resultado do Quiz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white shadow rounded p-6 text-center max-w-md w-full">
    <h2 class="text-2xl font-bold text-green-600 mb-4">Resultado do Quiz</h2>
    <p class="text-gray-800 mb-2">Você acertou <strong><?= $acertos ?></strong> pergunta(s)!</p>
    <p class="text-gray-800 mb-6">Tempo gasto: <strong><?= intval($tempo) ?></strong> segundos</p>
    <div class="flex flex-col space-y-2">
      <form method="post" action="diario.php">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Próxima rodada</button>
      </form>
      <a href="../users/inicio_user.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</a>
    </div>
  </div>
</body>
</html>
