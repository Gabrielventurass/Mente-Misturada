<?php

declare(strict_types=1);

include 'adm.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Administrador</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style_adm.css">
</head>
<body>
    <center>
        <h1>Bem-vindo ao mundo Mente Misturada, novo administrador!</h1>
    </center>

    <form action="cadastro_adm.php" method="post">
        <fieldset class="popUp">
            <legend>Faça seu cadastro</legend>

            <label for="nome" class="labels">Nome</label><br>
            <input type="text" name="nome" id="nome" class="inputs" required><br>

            <label for="email" class="labels">E-mail</label><br>
            <input type="email" name="email" id="email" class="inputs" required><br>

            <label for="senha" class="labels">Senha</label><br>
            <input type="password" name="senha" id="senha" class="inputs" required><br>

            <input type="hidden" name="acao" value="salvar">

            <input type="submit" value="Cadastrar" class="btDf" style="margin-left: 100px;">

            <p>Já possui uma conta? <a href="login_adm.php">Clique aqui</a></p>
            <p>É usuário? <a href="../users/login.php">Clique aqui</a></p>

            <?php if (isset($erroCadastro)) : ?>
                <p style="color:red; text-align:center;">
                    <?= htmlspecialchars($erroCadastro) ?>
                </p>
            <?php endif; ?>
        </fieldset>
    </form>
</body>
</html>
