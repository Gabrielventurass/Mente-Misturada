<?php
require_once("../class/adm.class.php");
session_start();

$erroLogin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $admin = admin::buscarPorEmail($email);

    if ($admin && password_verify($senha, $admin->getSenha())) {
        // Login bem-sucedido
        $_SESSION['nome_admin'] = $admin->getNome();
        $_SESSION['email_admin'] = $admin->getEmail();

        header("Location: inicio_adm.php"); // Página protegida
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
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    
</head>
<body>
    <center>
        <h1>Boas vindas! Entre com sua conta de ADM</h1>
    </center>
<div class="container">
    <fieldset class="popUp">
        <legend>Efetuar login</legend>
        <form method="POST" action="login_adm.php">
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

        <p>Deseja criar uma conta? <a href="cadastro_adm.php">Criar nova conta</a></p>
        <p>É usuário? <a href="../users/login.php">Fazer login</a></p>
    </fieldset>
</div>
</body>
</html>
