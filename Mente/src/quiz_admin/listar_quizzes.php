<?php
$dir = __DIR__ . '/quizzes';
$quizzes = [];

// Excluir quiz
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $arquivo = $dir . "/quiz_$id.json";
    if (file_exists($arquivo)) {
        if (unlink($arquivo)) {
            header("Location: listar_quizzes.php");
            exit;
        } else {
            die("Erro: não foi possível deletar o arquivo $arquivo");
        }
    } else {
        die("Arquivo $arquivo não encontrado");
    }
}

// Carregar quizzes
if (is_dir($dir)) {
    foreach (glob($dir . '/*.json') as $arquivo) {
        $conteudo = json_decode(file_get_contents($arquivo), true);
        $idArquivo = pathinfo($arquivo, PATHINFO_FILENAME);
        if ($conteudo) {
            if (!isset($conteudo['id'])) {
                $conteudo['id'] = $idArquivo;
            }
            $quizzes[] = $conteudo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gerenciar Quizzes</title>
  <link rel="stylesheet" href="../../public/css/style_admin.css">
</head>
<body>
<?php 
$h2 = "🧩 Gerenciar Quizzes Locais";
include 'menu_quiz.php';
?>

<main>
  <div class="card">
    <div style="margin-bottom:12px" class="center">
      <a href="criar_quiz.php" class="btn btn-primary">➕ Criar Novo Quiz</a>
    </div>

    <?php if (empty($quizzes)): ?>
      <div class="card empty center" style="max-width:720px;margin:0 auto;text-align:center">
        <p class="muted">Nenhum quiz salvo ainda 😅</p>
      </div>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($quizzes as $q): 
            $idQuiz = $q['id'] ?? '';
        ?>
          <div class="card">
            <h2 class="title"><?= htmlspecialchars($q['titulo'] ?? '(Sem título)') ?></h2>
            <p class="muted" style="margin:8px 0 12px 0"><?= htmlspecialchars($q['descricao'] ?? '(Sem descrição)') ?></p>
            <p class="muted" style="font-size:13px;margin-bottom:12px"><b>Tema:</b> <?= htmlspecialchars($q['tema'] ?? 'Não especificado') ?></p>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <a href="visualizar_quiz.php?id=<?= urlencode($idQuiz) ?>" class="btn btn-ghost">👁️ Visualizar</a>
              <a href="resultados_quiz.php?id=<?= urlencode($idQuiz) ?>" class="btn btn-ghost">📊 Resultados</a>
              <a href="editar_quiz.php?editar=<?= urlencode($idQuiz) ?>" class="btn btn-ghost">✏️ Editar</a>
              <a href="listar_quizzes.php?excluir=<?= urlencode($idQuiz) ?>" class="btn btn-danger" onclick="return confirm('Excluir quiz \"<?= addslashes($q['titulo'] ?? '') ?>\"?');">🗑️ Excluir</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
