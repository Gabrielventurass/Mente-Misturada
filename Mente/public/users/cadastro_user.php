<?php

declare(strict_types=1);

require_once '../class/user.class.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['acao']) &&
    $_POST['acao'] === 'salvar'
) {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuario = new usuario($nome, $email, $senha);

    if ($usuario->inserir()) {
        header('Location: login.php');
        exit;
    } else {
        $erroCadastro = $usuario->getErro();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <form action="cadastro.php" method="post">
        <center>
            <h1>Bem-vindo ao mundo Mente Misturada! Garanto que vai gostar!</h1>
        </center>
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

            <p>Já possui uma conta? <a href="login.php">Clique aqui</a></p>
            <p>É administrador? <a href="../admin/login_adm.php">Clique aqui</a></p>

            <?php if (isset($erroCadastro)) : ?>
                <p style="color:red; text-align:center;">
                    <?= htmlspecialchars($erroCadastro) ?>
                </p>
            <?php endif; ?>
        </fieldset>
    </form>
</body>
</html>
