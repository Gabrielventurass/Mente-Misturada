<?php
session_start();
include 'menu.php';

if (!isset($_SESSION['nome_usuario']) || !isset($_SESSION['email_usuario'])) {
    header('Location: login.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <script src="../js/javaS.js"></script>

</head>
<body>
    <center>
        <h1 id="tit">Mente Misturada</h1>
        <p class="sub">Mantenha seu cérebro informado!</p>
    </center>

    <center>
        <br>
        <button type="button" id="bt2" name="bt2" class="btLar" onclick="diario()">
            <div>
                <div style="float: left;
                            margin: 10px 5px 5px 0px;">
                    Diário
                </div>
                <div style="float: right;
                     margin-top: 0px;">
                    <img src="../img/diario.png" height="55px">
                </div>
            </div>
        </button>

        <button type="button" id="bt2" name="bt2" class="btGrn" onclick="vsTemp()">
            <div>
                <div style="float: left;
                            margin: 3px 5px 0px 0px;">
                    Contra<br>o<br>tempo
                </div>
                <div style="float: right;
                     margin-top: 6px;">
                    <img src="../img/ampulheta.png" height="55px">
                </div>
            </div>
        </button>

        <br><br><br>

        <button type="button" id="bt2" name="bt2"  class="btYlw" onclick="comp()"><div>
                <div style="float: left;
                            margin: 3px 5px 0px 0px    ;">
                    Dando<br>seu<br>melhor
                </div>
                <div style="float: right;
                     margin-top: 3px;">
                    <img src="../img/taça.png" height="60px">
                </div>
            </div>
        </button>

        <button type="button" id="bt2" name="bt2"  class="btPrl" onclick="casu()"><div>
                <div style="float: left;
                            margin: 3px 5px 0px 0px    ;">
                    Casual
                </div>
                <div style="float: right;
                     margin-top: 3px;">
                    <img src="../img/taça.png" height="60px">
                </div>
            </div>
        </button>

        <br><br><br>

        <button id="bt2" name="bt2"  class="btRed"><div>
                <div style="float: left;
                            margin: 15px 5px 0px 0px    ;">
                    Escolha<br>o<br>temas
                </div>
                <div style="float: right;
                     margin-top: 3px;">
                    <img src="../img/interrogacao.png" class="btImg">
                </div>
            </div>
        </button>

        <button id="bt2" name="bt2"  class="btBlu"><div>
                <div style="float: left;
                            margin: 15px 5px 0px 0px    ;">
                    Faça<br>seu<br>quiz
                </div>
                <div style="float: right;
                     margin-top: 3px;">
                    <img src="../img/interrogacao.png" class="btImg">
                </div>
            </div>
        </button>
    </center>
    <br><br><br><br><br><br><br><br>   
    <hr>
    <p>Mente Misturada é um projeto que envolve misturar a Wikipédia com os Jogos Diários achados facilmente pela internet. Consiste em ler um artigo e responder algumas perguntas logo depois. <a href="saiba.html">Saiba mais</a></p>
    <p>Confira aqui nossos termos de usu <a href="termos.html">Termos de uso</a></p>
    <p>Confira aqui nossa politica de privacidade <a href="politica.html">Política de privacidade</a></p>
    <p>Se tiver dúvidas ou sugestões sobre os Termos de Uso ou a Política de Privacidade, entre em contato conosco. contato@gmail.com</p>
</body>
</html>