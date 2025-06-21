<?php
require_once "../class/quiz.class.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz = new Quiz();
    $mensagem = $quiz->criarQuiz($_POST);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
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
        .correta {
            color: green;
        }
    </style>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <a href="../admin/inicio_adm.php"><img src="../img/seta.png" alt="" class="btImg"></a>
    <h1>Criar Novo Quiz</h1>

    <?php if (!empty($mensagem)): ?>
        <p><strong><?= $mensagem ?></strong></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Tema:</label><br>
        <input type="text" name="tema" required><br>

        <label>Texto do Artigo:</label><br>
        <textarea name="art" rows="6" required></textarea><br>

        <h3>Perguntas:</h3>
        <div id="perguntas-container"></div>

        <button type="button" onclick="adicionarPergunta()" class="btReset">Adicionar Pergunta</button><br><br>
        <button type="submit" class="btDf">Salvar Quiz</button>
    </form>

    <script>
        let countPerguntas = 0;

        function adicionarPergunta() {
            const container = document.getElementById('perguntas-container');
            const index = countPerguntas++;

            const perguntaHtml = `
                <div class="pergunta">
                    <label>Pergunta:</label><br>
                    <input type="text" name="perguntas[${index}][texto]" required><br>

                    <label>Alternativas:</label><br>
                    ${[0, 1, 2, 3].map(i => `
                        <input type="radio" name="perguntas[${index}][correta]" value="${i}" required>
                        <input type="text" name="perguntas[${index}][alternativas][]" required><br>
                    `).join('')}
                </div>
            `;

            container.insertAdjacentHTML('beforeend', perguntaHtml);
        }
    </script>
</body>
</html>
