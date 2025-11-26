<?php
require_once "../config/database.php";
session_start();

$msg = "";

// Se já estiver logado
if (isset($_SESSION['adm_nome'])) {
  header("Location: painel_adm.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $senha = trim($_POST['senha'] ?? '');

  if (empty($email) || empty($senha)) {
    $msg = "⚠️ Preencha todos os campos!";
  } else {
    $con = conectarPDO();

    // Verifica se está na lista de admins aprovados
    $sql = $con->prepare("SELECT * FROM admin WHERE email = ?");
    $sql->execute([$email]);
    $admin = $sql->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
      // Confere senha
      if (password_verify($senha, $admin['senha'])) {
        // Login OK
        $_SESSION['adm_id'] = $admin['codigo'];
        $_SESSION['adm_nome'] = $admin['nome'];
        $_SESSION['adm_email'] = $admin['email'];
        header("Location: painel_adm.php");
        exit;
      } else {
        $msg = "❌ Senha incorreta!";
      }
    } else {
      // Pode estar pendente
      $checkPend = $con->prepare("SELECT * FROM admins_pendentes WHERE email = ?");
      $checkPend->execute([$email]);
      if ($checkPend->fetch()) {
        $msg = "⏳ Seu cadastro ainda está aguardando aprovação do superadmin!";
      } else {
        $msg = "❌ Nenhum administrador encontrado com esse e-mail!";
      }
    }

    $con = null;
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login de Administrador</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #121212;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
form {
  background-color: #1e1e1e;
  padding: 25px;
  border-radius: 10px;
  width: 300px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
input {
  padding: 10px;
  border: none;
  border-radius: 5px;
  background: #2a2a2a;
  color: white;
}
button {
  padding: 10px;
  border: none;
  border-radius: 5px;
  background-color: #2d3a4a;
  color: #fff;
  cursor: pointer;
}
button:hover {
  background-color: #3f556f;
}
.msg {
  margin-top: 10px;
  text-align: center;
  color: #00ff88;
}
h3 {
  text-align: center;
  margin-bottom: 10px;
}
</style>
</head>
<body>
  <form method="POST">
    <h3>Login de Admin</h3>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
    <button type="button"><a href="recuperar_senha.php">Esqueci minha senha</a></button>
    <button type="button"><a href="cadastro_adm.php">Não possuo conta</a></button>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
  </form>
</body>
</html>
