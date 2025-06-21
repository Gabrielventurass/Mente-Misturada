<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['nome_usuario'])) {
    header('Location: login.php');
    exit;
}
?>

<link rel="stylesheet" href="../css/style.css">

<div class="field">
    <a href="../users/inicio.php" class="menu">
        <img src="../img/casa.png" alt="Início" class="btImg">
    </a>
    <center></center>
</div>
