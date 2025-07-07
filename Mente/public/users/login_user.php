<?php
// Conectar ao banco de dados (exemplo com PDO)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=mente', 'root', ''); // Alterar usuário e senha do banco
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Preparar a consulta SQL
    $sql = "SELECT * FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Verificar se o usuário foi encontrado
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Verificar se a senha está correta
        if (password_verify($senha, $usuario['senha'])) {
            // Senha correta - iniciar a sessão
            session_start();
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['nome'] = $usuario['nome'];
            
            // Redirecionar para 'inicio.php' após login bem-sucedido
            header("Location: inicio_user.php");
            exit();  // Certifique-se de usar exit para garantir que o código pare após o redirecionamento
        } else {
            // Senha incorreta
            echo "Senha incorreta.";
        }
    } else {
        // Usuário não encontrado
        echo "Usuário não encontrado.";
    }
}
?>

<!-- Formulário de login -->
<form method="POST" action="login_user.php">
    <label for="email">Email:</label>
    <input type="email" name="email" required>
    <br>
    <label for="senha">Senha:</label>
    <input type="password" name="senha" required>
    <br>
    <button type="submit">Login</button>
</form>
