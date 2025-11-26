<?php
// editar_quiz.php - versão corrigida e estilizada
// Ajuste as credenciais abaixo ao seu ambiente, ou substitua por require da sua conexão já existente.
$dsn = "mysql:host=localhost;dbname=mente;charset=utf8mb4";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . htmlspecialchars($e->getMessage()));
}

// ----------- CARREGAR QUIZ DO MYSQL -----------
function carregarQuizSQL(PDO $pdo, $quiz_id) {
    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id = ?");
    $stmt->execute([$quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) return null;

    $quizData = [
        "id" => $quiz["id"],
        "titulo" => $quiz["titulo"],
        "descricao" => $quiz["descricao"],
        "tema" => $quiz["tema"] ?? '',
        "art" => $quiz["art"] ?? '',
        "total_questoes" => $quiz["total_questoes"] ?? 0,
        "perguntas" => []
    ];

    $stmtPerg = $pdo->prepare("SELECT * FROM pergunta WHERE quiz_id = ? ORDER BY id ASC");
    $stmtPerg->execute([$quiz_id]);
    $perguntas = $stmtPerg->fetchAll(PDO::FETCH_ASSOC);

    foreach ($perguntas as $p) {
        $stmtAlt = $pdo->prepare("SELECT * FROM alternativa WHERE pergunta_id = ? ORDER BY id ASC");
        $stmtAlt->execute([$p['id']]);
        $alternativas = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);

        $quizData['perguntas'][] = [
            'id' => $p['id'],
            'texto' => $p['texto'],
            'alternativas' => array_map(function($alt){
                return [
                    'id' => $alt['id'],
                    'texto' => $alt['texto'],
                    'correta' => !empty($alt['correta']) ? true : false,
                ];
            }, $alternativas),
        ];
    }

    return $quizData;
}

// ----------- CARREGAR JSON -----------
function carregarQuizJSON($id) {
    $arquivo = __DIR__ . "/quizzes/$id.json";
    if (!file_exists($arquivo)) return null;
    $json = json_decode(file_get_contents($arquivo), true);
    if (!$json) return null;
    $json['art'] = $json['art'] ?? '';
    // garantir formato consistente
    foreach ($json['perguntas'] as $i => $p) {
        $resposta = '';
        foreach ($p['alternativas'] as $alt) {
            if (!empty($alt['correta'])) { $resposta = $alt['texto']; break; }
        }
        $json['perguntas'][$i]['resposta_correta'] = $resposta;
    }
    return $json;
}

$quizId = $_GET['editar'] ?? $_GET['id'] ?? null;
if (!$quizId) { echo "ID inválido"; exit; }

$quiz = carregarQuizSQL($pdo, $quizId);
if (!$quiz) { $quiz = carregarQuizJSON($quizId); }
if (!$quiz) { echo "Quiz não encontrado."; exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editar Quiz</title>
    <link rel="stylesheet" href="/public/css/style_admin.css">
    <style>
      /* Fieldset / legend */
      .field-legend{color:var(--accent);font-weight:700}
      fieldset.card{border:1px solid rgba(255,255,255,0.04);padding:12px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));}
      fieldset.card legend{padding:0 8px}

      /* Labels */
      label.muted{display:block;margin:8px 0 6px;color:var(--muted);font-size:13px}

      /* Inputs & textareas */
      input[type="text"], textarea{width:100%;padding:10px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;font-size:14px}
      textarea{resize:vertical;min-height:44px}
      input[type="text"]:focus, textarea:focus{outline:none;box-shadow:0 6px 18px rgba(2,6,23,0.45), 0 0 0 4px rgba(45,156,219,0.06);border-color:var(--accent)}

      /* Alternatives row */
      .alt-row{display:flex;gap:8px;align-items:center;margin-bottom:6px}
      .alt-row input[type="text"]{flex:1;padding:10px}
      .alt-row label{white-space:nowrap;color:var(--muted);font-size:13px}
      .alt-row input[type="checkbox"]{transform:scale(1.05);margin-right:6px}

      /* Buttons */
      .btn{padding:10px 14px;border-radius:10px}
      .btn-primary{font-weight:700}
    </style>
</head>
<body class="dark-mode">
<?php $h2 = "✏️ Editar Quiz"; include 'menu_quiz.php'; ?>

<main>
  <div class="card" style="max-width:1000px;margin:0 auto;">
    <h2 class="title">Editando Quiz: <?= htmlspecialchars($quiz['titulo']) ?></h2>

    <form method="POST" action="salvar_quiz.php" class="form-wrap">
      <input type="hidden" name="id" value="<?= htmlspecialchars($quiz['id']) ?>">

      <label class="muted">Título</label>
      <input type="text" name="titulo" value="<?= htmlspecialchars($quiz['titulo']) ?>">

      <label class="muted">Descrição</label>
      <textarea name="descricao" rows="3"><?= htmlspecialchars($quiz['descricao']) ?></textarea>

      <label class="muted">Tema</label>
      <input type="text" name="tema" value="<?= htmlspecialchars($quiz['tema'] ?? '') ?>">

      <h3 class="title" style="margin-top:12px;">Perguntas</h3>

      <?php foreach ($quiz['perguntas'] as $i => $p): ?>
        <fieldset class="card" style="margin-bottom:12px;">
          <legend class="field-legend">Pergunta <?= $i+1 ?></legend>

          <input type="hidden" name="perguntas[<?= $i ?>][id]" value="<?= htmlspecialchars($p['id'] ?? '') ?>">

          <label class="muted">Texto da Pergunta</label>
          <textarea name="perguntas[<?= $i ?>][texto]" rows="2"><?= htmlspecialchars($p['texto'] ?? '') ?></textarea>

          <div style="margin-top:8px;">
            <strong class="muted">Alternativas</strong>
            <?php foreach ($p['alternativas'] as $j => $alt): ?>
              <div class="alt-row">
                <input type="hidden" name="perguntas[<?= $i ?>][alternativas][<?= $j ?>][id]" value="<?= htmlspecialchars($alt['id'] ?? '') ?>">
                <input type="text" name="perguntas[<?= $i ?>][alternativas][<?= $j ?>][texto]" value="<?= htmlspecialchars($alt['texto'] ?? '') ?>">
                <label style="white-space:nowrap"><input type="checkbox" name="perguntas[<?= $i ?>][alternativas][<?= $j ?>][correta]" <?= !empty($alt['correta'] ?? false) ? 'checked' : '' ?>> Correta</label>
              </div>
            <?php endforeach; ?>
          </div>
        </fieldset>
      <?php endforeach; ?>

      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px;">
        <a href="visualizar_quiz.php?id=<?= htmlspecialchars($quiz['id']) ?>" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
      </div>
    </form>
  </div>
</main>

</body>
</html>
