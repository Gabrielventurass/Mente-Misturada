<?php
declare(strict_types=1);
require_once (__DIR__ . '/../../src/class/user.class.php');

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
        header('Location: login_user.php');
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <form action="cadastro_user.php" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Bem-vindo ao Mente Misturada!</h1>

        <div class="mb-4">
            <label for="nome" class="block text-gray-700 text-sm font-bold mb-2">Nome</label>
            <input type="text" name="nome" id="nome" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
            <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" required>
        </div>

        <div class="mb-6">
            <label for="senha" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
            <input type="password" name="senha" id="senha" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring" required>
        </div>

        <input type="hidden" name="acao" value="salvar">

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring">
                Cadastrar
            </button>
        </div>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Já possui uma conta? <a href="login_user.php" class="text-blue-600 hover:underline">Clique aqui</a>
            </p>
            <p class="text-sm text-gray-600">
                É administrador? <a href="../../src/admin/login_adm.php" class="text-blue-600 hover:underline">Clique aqui</a>
            </p>
        </div>

        <?php if (isset($erroCadastro)) : ?>
            <div class="mt-4 bg-red-100 text-red-700 p-2 rounded text-center">
                <?= htmlspecialchars($erroCadastro) ?>
            </div>
        <?php endif; ?>
    </form>
</body>
</html>
