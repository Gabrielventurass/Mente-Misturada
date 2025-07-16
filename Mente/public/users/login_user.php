<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=mente', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['nome'] = $usuario['nome'];

            header("Location: inicio_user.php");
            exit();
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login de Usuário</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <form method="POST" action="login_user.php" class="bg-white shadow-md rounded px-8 pt-6 pb-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Login de Usuário</h1>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">E-mail</label>
            <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" required>
        </div>

        <div class="mb-6">
            <label for="senha" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
            <input type="password" name="senha" id="senha" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring" required>
        </div>

        <?php if (isset($erro)) : ?>
            <div class="mb-4 bg-red-100 text-red-700 p-2 rounded text-center">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring">
                Entrar
            </button>
        </div>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Ainda não tem conta? <a href="cadastro_user.php" class="text-blue-600 hover:underline">Clique aqui</a>
            </p>
            <p class="text-sm text-gray-600">
                É administrador? <a href="../../src/admin/login_adm.php" class="text-blue-600 hover:underline">Clique aqui</a>
            </p>
        </div>
    </form>
</body>
</html>
