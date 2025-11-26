<?php
require_once "../config/database.php";
require_once "../class/quiz.class.php"; // se já existir essa classe

$h2 = "Gerenciar Quizzes";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciar Quizzes</title>
<style>
:root{
  --bg-1:#0f1724; --bg-2:#1b2540; --accent:#2d9cdb; --muted:#a8b3c7; --surface: rgba(255,255,255,0.02);
}
*{box-sizing:border-box}
body{margin:0;font-family:'Segoe UI', Roboto, Arial, sans-serif;color:#e6eef8;background:linear-gradient(180deg,var(--bg-1),var(--bg-2));-webkit-font-smoothing:antialiased;padding:36px 18px;display:flex;justify-content:center}
main{width:100%;max-width:980px}
.card{background:linear-gradient(180deg,var(--surface),rgba(255,255,255,0.01));border:1px solid rgba(255,255,255,0.04);padding:22px;border-radius:12px;box-shadow:0 6px 24px rgba(2,6,23,0.6);margin:12px 0}
.title{font-size:20px;margin:0 0 12px 0;color:#fff}
.actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:10px;text-decoration:none;font-weight:700;border:1px solid rgba(255,255,255,0.04)}
.btn-primary{background:linear-gradient(90deg,var(--accent),#6ec1ff);color:#062034}
.btn-ghost{background:transparent;color:inherit}
@media(max-width:520px){body{padding:18px}.actions{grid-template-columns:1fr}}
</style>
</head>
<body>

<?php include 'menu.php'; ?>

<main>
  <div class="card">
    <h2 class="title">Gerenciar Quizzes</h2>
    <div class="actions">
      <a class="btn btn-primary" href="../quiz_admin/criar_quiz.php">➕ Criar Novo Quiz</a>
      <a class="btn btn-ghost" href="../quiz_admin/listar_quizzes.php">📖 Gerenciar Quizzes Locais</a>
      <a class="btn btn-ghost" href="../quiz_admin/quizzes.php">📖 Gerenciar Quizzes Online</a>
    </div>
  </div>
</main>

</body>
</html>
