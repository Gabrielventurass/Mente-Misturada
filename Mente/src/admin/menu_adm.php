<?php

declare(strict_types=1);

if (!isset($_SESSION['nome_admin'])) {
    header('Location: login_adm.php');
    exit;
}
?>

<link rel="stylesheet" href="../css/style_adm.css">

<div class="field flex-container">
    <div class="menu-icons">
        <a href="inicio_adm.php" class="menu">
            <img src="../img/casa.png" alt="Início" class="btImg">
        </a>
        <a href="perfil_adm.php" class="menu">
            <img src="../img/perfil.png" alt="Perfil" class="btImg">
        </a>
        <a href="configu_adm.php" class="menu">
            <img src="../img/configura.png" alt="Configurações" class="btImg">
        </a>
    </div>
    <h1 class="admin-nome">
        Administrador logado: <?= htmlspecialchars($_SESSION['nome_admin']) ?>
    </h1>
</div>
