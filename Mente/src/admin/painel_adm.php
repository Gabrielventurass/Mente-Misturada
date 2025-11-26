<?php
session_start();

// Protege o acesso — só entra se tiver sessão ativa
if (!isset($_SESSION['adm_id'])) {
  header("Location: login_adm.php");
  exit;
}

$nome = $_SESSION['adm_nome'];
$email = $_SESSION['adm_email'];
?>
<head>
<meta charset="UTF-8">
<title>Painel do Administrador</title>
<link rel="stylesheet" href="style_admin.css">
</head>

</style>
</head>
<body>
<?php
$h2 = "🛠 Painel de controle";
include 'menu.php';
?>
<main>
  <div class="card">
    <h3>Bem-vindo, <?= htmlspecialchars($nome) ?>!</h3>
    <p><strong>E-mail:</strong> <?= htmlspecialchars($email) ?></p>
  </div>

  <div class="card">
    <h3>Funções do Admin</h3>
    <div class="actions">
      <a href="gerenciar_admins.php" class="btn"><span class="icon">👤</span> Gerenciar admins</a>
      <a href="gerenciar_quizzes.php" class="btn"><span class="icon">🧩</span> Gerenciar quizzes</a>
      <a href="gerenciar_usuarios.php" class="btn"><span class="icon">👥</span> Gerenciar usuários</a>
    </div>
  </div>
</main>
</body>
</html>
