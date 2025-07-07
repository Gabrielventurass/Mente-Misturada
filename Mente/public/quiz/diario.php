<?php
require_once "../../src/class/quiz.class.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para acessar o quiz.";
    exit;
}

$email = $_SESSION['email'];

// Verificar se o usuário já jogou o quiz hoje
$hoje = date('Y-m-d'); // Formato da data (ano-mês-dia)

if (isset($_SESSION['ultimo_quiz_data']) && $_SESSION['ultimo_quiz_data'] == $hoje) {
    echo "Você já jogou o quiz hoje. Tente novamente amanhã!";
    exit;
}

// Se não jogou hoje, permite jogar o quiz
$quizObj = new Quiz();
$quizAleatorio = $quizObj->obterQuizAleatorio($email);

if (!$quizAleatorio) {
    echo "Nenhum quiz disponível no momento.
          <button type='button' class='btDf'><a href='../users/inicio.php'>voltar</a></button>";
    exit;
}

// Atualiza a data do último quiz jogado
$_SESSION['ultimo_quiz_data'] = $hoje;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Responder Quiz</title>
</head>
<body>
    <h1>Quiz: <?= htmlspecialchars($quizAleatorio['tema']) ?></h1>
    <p><?= nl2br(htmlspecialchars($quizAleatorio['art'])) ?></p>

    <form method="POST" action="SR_diario.php">
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
        <button type="button" onclick="window.location.href='../users/inicio_user.php'" class="btDf">Voltar</button>
    </form>

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
