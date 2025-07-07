<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para criar um quiz.";
    exit;
}

$email = $_SESSION['email'];  // Obtemos o email do usuário logado

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quiz_titulo = $_POST['titulo'] ?? null;
    $quiz_descricao = $_POST['descricao'] ?? null;
    $quiz_tema = $_POST['tema'] ?? null;  // Tema do quiz
    $perguntas = $_POST['perguntas'] ?? [];

    // Validar os dados
    if (!$quiz_titulo || !$quiz_descricao || !$quiz_tema || empty($perguntas)) {
        echo "Por favor, preencha todos os campos e adicione perguntas.";
        exit;
    }

    try {
        // Conexão com o banco de dados
        $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Inserir o novo quiz no banco de dados, incluindo o tema
        $stmt = $pdo->prepare("
            INSERT INTO quizzes_user (titulo, descricao, tema, usuario_email)
            VALUES (:titulo, :descricao, :tema, :usuario_email)
        ");
        $stmt->execute([
            'titulo' => $quiz_titulo,
            'descricao' => $quiz_descricao,
            'tema' => $quiz_tema,  // Inserindo o tema do quiz
            'usuario_email' => $email  // Salvando o email do usuário logado
        ]);

        // Obter o ID do quiz recém-criado
        $quiz_id = $pdo->lastInsertId();

        // Inserir as perguntas e alternativas
        foreach ($perguntas as $pergunta) {
            // Inserir pergunta no banco de dados
            $stmt = $pdo->prepare("
                INSERT INTO perguntas_user (quiz_id, texto)
                VALUES (:quiz_id, :texto)
            ");
            $stmt->execute([
                'quiz_id' => $quiz_id,
                'texto' => $pergunta['texto']
            ]);

            // Obter o ID da pergunta recém-criada
            $pergunta_id = $pdo->lastInsertId();

            // Inserir as alternativas para cada pergunta
            foreach ($pergunta['alternativas'] as $alt) {
                // Verificar se a chave 'correta' existe, caso contrário, atribuir '0'
                $correta = isset($alt['correta']) ? 1 : 0;
                
                // Inserir alternativa no banco de dados
                $stmt = $pdo->prepare("
                    INSERT INTO alternativas (pergunta_id, texto, correta)
                    VALUES (:pergunta_id, :texto, :correta)
                ");
                $stmt->execute([
                    'pergunta_id' => $pergunta_id,
                    'texto' => $alt['texto'],
                    'correta' => $correta  // Definir '1' para correta, '0' caso contrário
                ]);
            }
        }

        echo "Quiz criado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Quiz</title>
</head>
<body>
    <br>
    <a href="inicio_user.php"><img src="../img/seta.png" height="50px"></a>
    <h1>Criar um Novo Quiz</h1>
    <form method="POST" action="">
        <label for="titulo">Título do Quiz:</label><br>
        <input type="text" name="titulo" required><br><br>

        <label for="descricao">Descrição do Quiz:</label><br>
        <textarea name="descricao" required></textarea><br><br>

        <label for="tema">Tema do Quiz:</label><br>
        <input type="text" name="tema" required><br><br> <!-- Campo para inserir o tema -->

        <h3>Adicionar Perguntas</h3>
        <div id="perguntas-container">
            <div class="pergunta">
                <label for="pergunta_texto">Texto da Pergunta:</label><br>
                <input type="text" name="perguntas[0][texto]" required><br><br>

                <h4>Alternativas:</h4>
                <label for="alt1">Alternativa 1:</label><br>
                <input type="text" name="perguntas[0][alternativas][0][texto]" required><br>
                <label for="alt1_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[0][alternativas][0][correta]" value="1"><br><br>

                <label for="alt2">Alternativa 2:</label><br>
                <input type="text" name="perguntas[0][alternativas][1][texto]" required><br>
                <label for="alt2_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[0][alternativas][1][correta]" value="1"><br><br>

                <label for="alt3">Alternativa 3:</label><br>
                <input type="text" name="perguntas[0][alternativas][2][texto]" required><br>
                <label for="alt3_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[0][alternativas][2][correta]" value="1"><br><br>

                <button type="button" onclick="adicionarPergunta()">Adicionar outra pergunta</button>
            </div>
        </div>

        <button type="submit">Criar Quiz</button>
    </form>

    <script>
        let perguntaCount = 1;

        function adicionarPergunta() {
            if (perguntaCount >= 5) {
                alert("Você pode adicionar no máximo 5 perguntas.");
                return;
            }

            let perguntaContainer = document.createElement('div');
            perguntaContainer.classList.add('pergunta');
            
            perguntaContainer.innerHTML = `
                <label for="pergunta_texto">Texto da Pergunta:</label><br>
                <input type="text" name="perguntas[${perguntaCount}][texto]" required><br><br>
                <h4>Alternativas:</h4>
                <label for="alt1">Alternativa 1:</label><br>
                <input type="text" name="perguntas[${perguntaCount}][alternativas][0][texto]" required><br>
                <label for="alt1_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[${perguntaCount}][alternativas][0][correta]" value="1"><br><br>

                <label for="alt2">Alternativa 2:</label><br>
                <input type="text" name="perguntas[${perguntaCount}][alternativas][1][texto]" required><br>
                <label for="alt2_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[${perguntaCount}][alternativas][1][correta]" value="1"><br><br>

                <label for="alt3">Alternativa 3:</label><br>
                <input type="text" name="perguntas[${perguntaCount}][alternativas][2][texto]" required><br>
                <label for="alt3_corret">Alternativa Correta?</label><input type="checkbox" name="perguntas[${perguntaCount}][alternativas][2][correta]" value="1"><br><br>

                <button type="button" onclick="removerPergunta(this)">Excluir Pergunta</button><br><br>
            `;
            
            document.getElementById('perguntas-container').appendChild(perguntaContainer);
            perguntaCount++;
        }

        function removerPergunta(button) {
            // Remover a pergunta do DOM
            button.parentElement.remove();
            perguntaCount--;
        }
    </script>
</body>
</html>
