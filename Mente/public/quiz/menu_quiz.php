<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar a sessão apenas se ela não estiver ativa
}

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    // Se não estiver logado, redirecionar para login_user.php
    header('Location: login_user.php');
    exit;
}
?>

<div class="field flex-container">
    <div class="menu-icons">
        <a href="../users/inicio_user.php" class="menu">
            <img src="../img/casa.png" alt="Início" class="btImg">
        </a>
    </div>
    <h1 class="usuario-nome">
        Usuário logado: <?= htmlspecialchars($_SESSION['nome']) ?>
    </h1>
</div>
