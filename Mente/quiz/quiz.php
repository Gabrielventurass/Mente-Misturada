<?php
require_once "../class/quiz.class.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email_usuario'])) {
    echo "Você precisa estar logado para acessar o quiz.";
    exit;
}

$email_usuario = $_SESSION['email_usuario'];

$quizObj = new Quiz();
$quizAleatorio = $quizObj->obterQuizAleatorio($email_usuario);

if (!$quizAleatorio) {
    echo "Nenhum quiz disponível no momento.
          <button type='button' class='btDf'><a href='../users/inicio.php'>voltar</a></button>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Responder Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial; padding: 20px; }
        .pergunta { margin-bottom: 20px; }
        .btDf { padding: 8px 16px; margin: 10px 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Quiz: <?= htmlspecialchars($quizAleatorio['tema']) ?></h1>
    <p><?= nl2br(htmlspecialchars($quizAleatorio['art'])) ?></p>

    <form method="POST" action="salvar_respostas.php">
        <input type="hidden" name="quiz_id" value="<?= $quizAleatorio['id'] ?>">
        <input type="hidden" name="tempo" id="tempo">

        <?php foreach ($quizAleatorio['perguntas'] as $p): ?>
            <div class="pergunta">
                <p><strong><?= htmlspecialchars($p['texto']) ?></strong></p>
                <?php foreach ($p['alternativas'] as $alt): ?>
                    <label>
                        <input type="radio" name="respostas[<?= $p['id'] ?>]" value="<?= htmlspecialchars($alt['texto']) ?>" required>
                        <?= htmlspecialchars($alt['texto']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" onclick="registrarTempo()" class="btDf">Enviar Respostas</button>
    </form>

    <!-- Botões fora do formulário -->
    <button type="button" onclick="window.location.href='quiz.php'" class="btDf">Próxima rodada</button>
    <button type="button" onclick="window.location.href='../users/inicio.php'" class="btDf">Voltar</button>

    <!-- Script de contagem de tempo -->
    <script>
        let tempo = 0;
        let intervalo;

        window.onload = function () {
            intervalo = setInterval(() => {
                tempo++;
            }, 1000);
        };

        function registrarTempo() {
            clearInterval(intervalo);
            document.getElementById('tempo').value = tempo;
        }
    </script>
</body>
</html>
