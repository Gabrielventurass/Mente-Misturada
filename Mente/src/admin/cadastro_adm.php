<?php
require_once "../config/database.php";
require_once "../class/adm.class.php";

$mensagem = "";
$pdo = conectarPDO(); // 🔹 conexão PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "❌ Preencha todos os campos!";
    } else {
        try {
            // 🔹 passa o PDO pro construtor
            $novoAdmin = new admin(0, $nome, $email, $senha, $pdo);

            if ($novoAdmin->inserir()) {
                $mensagem = "✅ Cadastro enviado! Aguarde aprovação do superadmin.";
            } else {
                $mensagem = "❌ Erro: " . $novoAdmin->getErro();
            }
        } catch (Exception $e) {
            $mensagem = "❌ Erro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastro de Administrador</title>
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
  padding: 20px;
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
}
</style>
</head>
<body>
  <form method="POST" action="">
    <h3>Cadastro de Admin</h3>
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Cadastrar</button>
    <a href="login_adm.php">Logar</a>
    <div class="msg"><?= htmlspecialchars($mensagem) ?></div>
  </form>
</body>
</html>
