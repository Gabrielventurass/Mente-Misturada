<?php
require_once "../class/quiz.class.php";
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do quiz inválido.");
}

$quiz_id = (int) $_GET['id'];
$quiz = new Quiz();
$dadosQuiz = $quiz->buscarQuizCompleto($quiz_id); // Essa função você vai criar no quiz.class.php

if (!$dadosQuiz) {
    die("Quiz não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $quiz->atualizarQuiz($quiz_id, $_POST); // Outra função que você adicionará na classe
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Quiz</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pergunta {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
        }
        input[type=text], textarea {
            width: 100%;
            padding: 6px;
            margin-bottom: 5px;
            border: 1px solid black;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <a href="gerenciar_quiz.php"><img src="../img/seta.png" alt="" class="btImg"></a>
    <h1>Editar Quiz</h1>

    <?php if (!empty($mensagem)) echo "<p><strong>$mensagem</strong></p>"; ?>

    <form method="post">
        <label>Tema:</label><br>
        <input type="text" name="tema" value="<?= htmlspecialchars($dadosQuiz['tema']) ?>" required><br>

        <label>Texto do Artigo:</label><br>
        <textarea name="art" rows="6" required><?= htmlspecialchars($dadosQuiz['art']) ?></textarea><br>

        <h3>Perguntas:</h3>
        <div id="perguntas-container">
            <?php foreach ($dadosQuiz['perguntas'] as $pIndex => $pergunta): ?>
                <div class="pergunta">
                    <label>Pergunta:</label><br>
                    <input type="text" name="perguntas[<?= $pIndex ?>][texto]" value="<?= htmlspecialchars($pergunta['texto']) ?>" required><br>

                    <label>Alternativas:</label><br>
                    <?php foreach ($pergunta['alternativas'] as $aIndex => $alt): ?>
                        <input type="radio" name="perguntas[<?= $pIndex ?>][correta]" value="<?= $aIndex ?>" <?= $alt['correta'] ? 'checked' : '' ?> required>
                        <input type="text" name="perguntas[<?= $pIndex ?>][alternativas][]" value="<?= htmlspecialchars($alt['texto']) ?>" required><br>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btDf">Salvar Alterações</button>
    </form>
</body>
</html>
