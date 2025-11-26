<?php
require_once "../config/database.php";
require_once "../class/adm.class.php";

$pdo = conectarPDO();
$msg = "";

$email = $_GET['email'] ?? '';

if (!$email) {
    die("❌ Admin não especificado.");
}

$stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
$stmt->execute([$email]);
$adminData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminData) {
    die("❌ Admin não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($nome) {
        $stmt = $pdo->prepare("UPDATE admin SET nome = :nome WHERE email = :email");
        $stmt->execute([':nome' => $nome, ':email' => $email]);
        $msg = "✅ Nome atualizado com sucesso!";
    }

    if ($senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET senha = :senha WHERE email = :email");
        $stmt->execute([':senha' => $hash, ':email' => $email]);
        $msg .= " ✅ Senha atualizada!";
    }

    $adminData['nome'] = $nome ?: $adminData['nome'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editar Admin</title>
<link rel="stylesheet" href="style_admin.css">
</head>
<body>

<?php
$h2 = "Editar Admin";
include 'menu.php';
?>

<main>
  <div class="card">
    <h2 style="margin:0 0 10px 0;color:#fff">Editar Admin</h2>
    <div class="form-wrap">
      <form method="POST">
        <label for="nome">Nome</label>
        <input id="nome" type="text" name="nome" placeholder="Nome" value="<?= htmlspecialchars($adminData['nome']) ?>" required>

        <label for="senha">Nova Senha <span style="color:var(--muted);font-weight:500;font-size:12px">(deixe vazio para não alterar)</span></label>
        <input id="senha" type="password" name="senha" placeholder="Nova Senha">

        <div class="row">
          <button type="submit" class="btn btn-primary">Salvar Alterações</button>
          <a href="gerenciar_admins.php" class="btn btn-secondary">Voltar</a>
        </div>

        <div class="msg"><?= htmlspecialchars($msg) ?></div>
      </form>
    </div>
  </div>
</main>

</body>
</html>
