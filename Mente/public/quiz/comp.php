<?php
require_once "../../src/class/quiz.class.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para enviar respostas.";
    exit;
}

$email = $_SESSION['email'];

// Obter o quiz
$quiz_id = $_GET['quiz_id'] ?? null;
$quizObj = new Quiz();
$quizAleatorio = $quizObj->obterQuizAleatorio($email, $quiz_id);

if (!$quizAleatorio) {
    echo "Quiz não encontrado.";
    exit;
}

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

    <!-- Formulário de Respostas -->
    <form method="POST" action="SR_comp.php">
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

        <button type="submit" class="btDf" onclick="registrarTempo()">Enviar Respostas</button>
    </form>

    <!-- Script de contagem de tempo -->
    <h3>Tempo decorrido: <span id="cronometro">0</span> segundos</h3>

    <script>
        let tempo = 0;
        let intervalo;

        window.onload = function () {
            // Inicia o cronômetro
            intervalo = setInterval(() => {
                tempo++;
                document.getElementById('cronometro').textContent = tempo;
            }, 1000);
        };

        function registrarTempo() {
            // Para o cronômetro e envia o tempo
            clearInterval(intervalo);
            document.getElementById('tempo').value = tempo;
        }
    </script>
</body>
</html>
