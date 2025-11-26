<?php
// listar_quizzes_db.php
// Lista apenas quizzes salvos no banco de dados.
// ⚠️ Substitua os placeholders abaixo:
// - nomeDoBanco
// - usuarioDoBanco
// - senhaDoBanco
// - quiz

// Conexão PDO (ajuste os valores)
include '../config/config.inc.php';
$dsn = DSN;
$dbUser = USUARIO;
$dbPass = SENHA;

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die("Erro ao conectar ao banco: " . htmlspecialchars($e->getMessage()));
}

// Excluir quiz (via ?excluir=ID)
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    if ($id > 0) {
        $del = $pdo->prepare("DELETE FROM quiz WHERE id = ?");
        $del->execute([$id]);
        header("Location:quizzes.php");
        exit;
    }
}

// Buscar quizzes no banco (substitua quiz)
try {
    $stmt = $pdo->query("SELECT * FROM quiz ORDER BY id DESC");
    $quizzes = $stmt->fetchAll();
} catch (Exception $e) {
    die("Erro ao buscar quizzes: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gerenciar Quizzes (Banco)</title>
  <link rel="stylesheet" href="../../public/css/style_admin.css">
</head>
<body>
<?php 
$h2 = "🧩 Gerenciar Quizzes (Banco)";
include '../quiz_admin/menu_quiz.php';
?>

  <main>
    <div class="card">
      <div style="margin-bottom:12px" class="center">
        <a href="criar_quiz.php" class="btn btn-primary">➕ Criar Novo Quiz</a>
      </div>

      <?php if (empty($quizzes)): ?>
        <div class="card empty center" style="max-width:720px;margin:0 auto;text-align:center">
          <p class="muted">Nenhum quiz salvo no banco ainda 😅</p>
        </div>
      <?php else: ?>
        <div class="cards-grid">
          <?php foreach ($quizzes as $q): ?>
            <div class="card">
              <h2 class="title"><?= htmlspecialchars($q['titulo']) ?></h2>
              <p class="muted" style="margin:8px 0 12px 0"><?= htmlspecialchars($q['descricao'] ?? '') ?></p>
              <p class="muted" style="font-size:13px;margin-bottom:12px"><b>Tema:</b> <?= htmlspecialchars($q['tema'] ?? '') ?></p>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="visualizar_quiz.php?id=<?= urlencode($q['id']) ?>" class="btn btn-ghost">👁️ Visualizar</a>
                <a href="resultados_quiz.php?id=<?= urlencode($q['id']) ?>" class="btn btn-ghost">📊 Resultados</a>
                <a href="editar_quiz.php?editar=<?= urlencode($q['id']) ?>" class="btn btn-ghost">✏️ Editar</a>
                <a href="?excluir=<?= urlencode($q['id']) ?>" class="btn btn-danger" onclick="return confirm('Excluir quiz \"<?= addslashes($q['titulo']) ?>\"?');">🗑️ Excluir</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

</body>
</html>
