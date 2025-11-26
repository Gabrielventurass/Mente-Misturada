<?php
require_once '../config/database.php';
require_once '../class/adm.class.php';

$pdo = conectarPDO();
$msg = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    $msg = "❌ Token não informado.";
} else {
$dados = admin::validarToken(conectarPDO(), $token);


    if (!$dados) {
        $msg = "❌ Link inválido ou expirado.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novaSenha = $_POST['senha'] ?? '';
        $confirmSenha = $_POST['confirm_senha'] ?? '';

        if (!$novaSenha || !$confirmSenha) {
            $msg = "❌ Preencha todos os campos.";
        } elseif ($novaSenha !== $confirmSenha) {
            $msg = "❌ As senhas não coincidem.";
        } else {
            $sucesso = admin::atualizarSenha($pdo, (int)$dados['codigo'], $novaSenha);
            if ($sucesso) {
                $msg = "✅ Senha atualizada com sucesso!";
            } else {
                $msg = "❌ Erro ao atualizar senha.";
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinir Senha - Admin</title>
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
  input[type="password"] {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: none;
    margin: 10px 0;
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
    <h2>Redefinir Senha</h2>
    <?php if ($msg): ?>
      <p class="msg"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <?php if (isset($dados) && $dados && (!isset($sucesso) || !$sucesso)): ?>
      <form method="POST">
        <input type="password" name="senha" placeholder="Nova senha" required>
        <input type="password" name="confirm_senha" placeholder="Confirme a nova senha" required>
        <button type="submit">Salvar nova senha</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
