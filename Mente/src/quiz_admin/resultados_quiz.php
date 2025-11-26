<?php
session_start();

if (!isset($_SESSION['adm_email'])) {
    header("Location: ../admin/login_adm.php");
    exit;
}

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">ID do quiz não fornecido.</p>
            <a href="listar_quizzes.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // -------------------------
    // 1. CARREGAR QUIZ DO JSON
    // -------------------------
    $arquivo_json = __DIR__ . "/quizzes/$quiz_id.json";

    if (!file_exists($arquivo_json)) {
        die("<p>Quiz não encontrado no formato JSON.</p>");
    }

    $json = file_get_contents($arquivo_json);
    $quiz = json_decode($json, true);

    if (!$quiz) {
        die("<p>Erro ao carregar o quiz JSON.</p>");
    }

    // -------------------------
    // 2. Estatísticas no MySQL
    // -------------------------

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_respostas,
            AVG(acertos) AS media_acertos,
            AVG(tempo) AS media_tempo
        FROM resposta_usuario
        WHERE quiz_id = :quiz_id
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT usuario_id, acertos, tempo, data_resposta
        FROM resposta_usuario
        WHERE quiz_id = :quiz_id
        ORDER BY data_resposta DESC
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Resultados do Quiz</title>
  <link rel="stylesheet" href="/public/css/style_admin.css">
</head>


<body class="dark-mode">
<?php 
$h2 = "📊 Resultados do Quiz";
include 'menu_quiz.php';
?>

<main>
  <div class="card">
    <h2 class="title" style="margin-bottom:18px;">Resultados – <?= htmlspecialchars($quiz['titulo']) ?></h2>

    <?php if ($stats && $stats['total_respostas'] > 0): ?>
      <div class="cards-grid" style="margin-bottom:24px;grid-template-columns:repeat(3,1fr)">
        <div class="card stats-blue">
          <p class="muted">Participantes</p>
          <p class="title" style="font-size:2em; color:#fff; margin:0;"><?= (int)$stats['total_respostas'] ?></p>
        </div>
        <div class="card stats-green">
          <p class="muted">Média de Acertos</p>
          <p class="title" style="font-size:2em; color:#fff; margin:0;"><?= round($stats['media_acertos'], 2) ?></p>
        </div>
        <div class="card stats-yellow">
          <p class="muted">Tempo Médio (s)</p>
          <p class="title" style="font-size:2em; color:#fff; margin:0;"><?= round($stats['media_tempo'], 1) ?></p>
        </div>
      </div>

      <h3 class="title" style="margin-bottom:12px;">📋 Resultados Individuais</h3>
      <div class="table-container">
        <table class="styled-table">
          <thead>
            <tr>
              <th>Usuário</th>
              <th>Acertos</th>
              <th>Tempo (s)</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($respostas as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['usuario_id']) ?></td>
                <td><?= (int)$r['acertos'] ?></td>
                <td><?= (int)$r['tempo'] ?></td>
                <td><?= htmlspecialchars($r['data_resposta']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="card empty center" style="max-width:720px;margin:0 auto;text-align:center">
        <p class="muted">Nenhum usuário respondeu este quiz ainda 😅</p>
      </div>
    <?php endif; ?>

    <div class="center" style="margin-top:24px">
      <a href="listar_quizzes.php" class="btn btn-primary">Voltar</a>
    </div>
  </div>
</main>
</body>
</html>
