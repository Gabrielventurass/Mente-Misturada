<?php
require_once("../class/user.class.php");
session_start();

$erroLogin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuario = Usuario::buscarPorEmail($email);

    if ($usuario && password_verify($senha, $usuario->getSenha())) {
        // Login bem-sucedido
        $_SESSION['nome_usuario'] = $usuario->getNome();
        $_SESSION['email_usuario'] = $usuario->getEmail();

        header("Location: inicio.php"); // Página protegida
        exit();
    } else {
        $erroLogin = "E-mail ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <center>
        <h1>Boas vindas jogador! Tá preparado?</h1>
    </center>
    <fieldset class="popUp">
        <legend>Efetuar login</legend>
        <form method="POST" action="login.php">
            <label class="labels">Email:</label><br>
            <input type="email" name="email" class="inputs" required>
            <br>

            <label class="labels">Senha:</label><br>
            <input type="password" name="senha" class="inputs" required>
            <br>

            <button type="submit" class="btDf" style="margin-left: 35%; margin-right: auto;">Entrar</button>
        </form>

        <?php if (!empty($erroLogin)): ?>
            <div class="erro"><?= $erroLogin ?></div>
        <?php endif; ?>

        <p>Não possui conta? <a href="cadastro.php">Criar nova conta</a></p>
        <p>É administrador? <a href="../admin/login_adm.php">Fazer login</a></p>
    </fieldset>
</div>
</body>
</html>
