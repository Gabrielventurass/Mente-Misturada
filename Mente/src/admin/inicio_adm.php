<?php

declare(strict_types=1);

session_start();
include 'menu_adm.php';

if (!isset($_SESSION['nome_admin']) || !isset($_SESSION['email_admin'])) {
    header('Location: login_adm.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style_adm.css">
    <script src="../js/javaS.js"></script>
</head>
<body>
    <center>
        <h1 id="tit">Mente Misturada</h1>
    </center>

    <center>
        <br>
        <button type="button" id="bt2" class="btLar" onclick="criacao()">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="margin: 10px 5px 5px 0px;">Criação</div>
                <div><img src="../img/lapis.png" height="55px" alt="Criação"></div>
            </div>
        </button>

        <button type="button" id="bt2" class="btGrn" onclick="gerQuiz()">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="margin: 3px 5px 0px 0px;">Gerenciar <br> quizzes</div>
                <div><img src="../img/ampulheta.png" height="55px" alt="Quizzes"></div>
            </div>
        </button>
    </center>
    <hr>
</body>
</html>
