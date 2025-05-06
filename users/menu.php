<?php
if (!isset($_SESSION['nome_usuario'])) {
    header('Location: login.php');
    exit();
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="field flex-container">
    <div class="menu-icons">
        <a href="inicio.php" class="menu"><img src="../img/casa.png" alt="Início" class="btImg"></a>
        <a href="perfil.php" class="menu"><img src="../img/perfil.png" alt="Perfil" class="btImg"></a>
        <a href="configu.php" class="menu"><img src="../img/configura.png" alt="Configurações" class="btImg"></a>
    </div>
    <h1 class="usuario-nome">Usuario logado: <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></h1>
</div>
    