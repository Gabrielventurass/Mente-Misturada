<?php
require_once '../config/database.php';
require_once '../class/adm.class.php';
require_once 'email_recuperacao.php';

$msg = '';
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email) {
    $token = admin::gerarTokenRecuperacao(conectarPDO(), $email);
    if ($token) {
        enviarEmailRecuperacao($email, $token);
        $msg = "✅ Um link de recuperação foi enviado para o seu e-mail.";
    } else {
        $msg = "❌ E-mail não encontrado no sistema.";
    }
}
$token = admin::gerarTokenRecuperacao(conectarPDO(), $email);
$link = "https://seusite.com/admin/redefinir_senha.php?token=$token";
enviarEmailRecuperacao($email, $link);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha - Admin</title>
<style>
  body {
    background: #1e1e2f;
    color: #fff;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .container {
    background: #2d3a4a;
    padding: 30px;
    border-radius: 10px;
    width: 350px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    text-align: center;
  }
  input[type="email"] {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: none;
    margin: 15px 0;
  }
  button {
    background: #4caf50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  button:hover {
    background: #45a049;
  }
  .msg {
    margin-bottom: 15px;
    font-weight: bold;
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Recuperar Senha</h2>
    <?php if ($msg): ?>
      <p class="msg"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Seu e-mail" required>
      <button type="submit">Enviar link</button>
    </form>
  </div>
</body>
</html>
