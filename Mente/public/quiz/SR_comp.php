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

    // Gabarito
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

    // Acertos
    $acertos = 0;
    foreach ($respostas as $pergunta_id => $resposta_usuario) {
        if (isset($gabaritoMap[$pergunta_id]) && $gabaritoMap[$pergunta_id] === $resposta_usuario) {
            $acertos++;
        }
    }

    $total_questoes = count($gabaritoMap);
    $pontuacao_por_questao = 200 / $total_questoes;
    $pontuacao_total = $acertos * $pontuacao_por_questao;
    $penalidade_tempo = floor($tempo / 2);
    $pontuacao_total -= $penalidade_tempo;
    if ($pontuacao_total < 0) $pontuacao_total = 0;

    $pdo->prepare("UPDATE usuario SET pontuacao = pontuacao + :pontuacao WHERE email = :email")
        ->execute(['pontuacao' => $pontuacao_total, 'email' => $email]);

    $pdo->prepare("
        INSERT INTO resposta_usuario (usuario_id, quiz_id, acertos, tempo, pontuacao)
        VALUES (:email, :quiz_id, :acertos, :tempo, :pontuacao)
    ")->execute([
        'email' => $email,
        'quiz_id' => $quiz_id,
        'acertos' => $acertos,
        'tempo' => $tempo,
        'pontuacao' => $pontuacao_total
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
    <p class="text-gray-800 mb-2">Tempo gasto: <strong><?= intval($tempo) ?></strong> segundos</p>
    <p class="text-gray-800 mb-6">Pontuação final: <strong><?= $pontuacao_total ?></strong> pontos</p>
    <div class="flex flex-col space-y-2">
      <a href="comp.php?quiz_id=<?= $quiz_id ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Próxima rodada</a>
      <a href="../users/inicio_user.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">Voltar</a>
    </div>
  </div>
</body>
</html>
