<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para criar ou editar um quiz.";
    exit;
}

$email = $_SESSION['email'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $quiz_id = $_GET['quiz_id'] ?? null;
    $quiz_data = null;
    $perguntas_organizacao = [];

    if ($quiz_id) {
        $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id AND usuario_email = :email");
        $stmt->execute(['quiz_id' => $quiz_id, 'email' => $email]);
        $quiz_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quiz_data) {
            echo "Quiz não encontrado ou você não tem permissão para editar este quiz.";
            exit;
        }

        $stmt_perguntas = $pdo->prepare("SELECT p.id AS pergunta_id, p.texto AS pergunta_texto, a.id AS alternativa_id, a.texto AS alternativa_texto, a.correta FROM perguntas_user p LEFT JOIN alternativas a ON p.id = a.pergunta_id WHERE p.quiz_id = :quiz_id");
        $stmt_perguntas->execute(['quiz_id' => $quiz_id]);
        $perguntas_data = $stmt_perguntas->fetchAll(PDO::FETCH_ASSOC);

        foreach ($perguntas_data as $item) {
            $perguntas_organizacao[$item['pergunta_id']]['texto'] = $item['pergunta_texto'];
            $perguntas_organizacao[$item['pergunta_id']]['alternativas'][] = [
                'id' => $item['alternativa_id'],
                'texto' => $item['alternativa_texto'],
                'correta' => $item['correta']
            ];
        }
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_titulo = $_POST['titulo'] ?? null;
    $quiz_descricao = $_POST['descricao'] ?? null;
    $quiz_tema = $_POST['tema'] ?? null;
    $perguntas = $_POST['perguntas'] ?? [];

    if (!$quiz_titulo || !$quiz_descricao || !$quiz_tema || empty($perguntas)) {
        echo "Por favor, preencha todos os campos e adicione perguntas.";
        exit;
    }

    try {
        if ($quiz_id) {
            $stmt = $pdo->prepare("UPDATE quizzes_user SET titulo = :titulo, descricao = :descricao, tema = :tema WHERE id = :quiz_id");
            $stmt->execute(['titulo' => $quiz_titulo,'descricao' => $quiz_descricao,'tema' => $quiz_tema,'quiz_id' => $quiz_id]);
            $mensagem = "Quiz atualizado com sucesso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO quizzes_user (titulo, descricao, tema, usuario_email) VALUES (:titulo, :descricao, :tema, :usuario_email)");
            $stmt->execute(['titulo' => $quiz_titulo,'descricao' => $quiz_descricao,'tema' => $quiz_tema,'usuario_email' => $email]);
            $quiz_id = $pdo->lastInsertId();
            $mensagem = "Quiz criado com sucesso!";
        }

        // Limpar dados antigos se estiver editando
        if ($quiz_id) {
            $pdo->prepare("DELETE FROM alternativas WHERE pergunta_id IN (SELECT id FROM perguntas_user WHERE quiz_id = :quiz_id)")->execute(['quiz_id' => $quiz_id]);
            $pdo->prepare("DELETE FROM perguntas_user WHERE quiz_id = :quiz_id")->execute(['quiz_id' => $quiz_id]);
        }

        // Inserir perguntas/alternativas
        foreach ($perguntas as $pergunta) {
            $stmt = $pdo->prepare("INSERT INTO perguntas_user (quiz_id, texto) VALUES (:quiz_id, :texto)");
            $stmt->execute(['quiz_id' => $quiz_id, 'texto' => $pergunta['texto']]);
            $pergunta_id = $pdo->lastInsertId();
            foreach ($pergunta['alternativas'] as $alt) {
                $correta = isset($alt['correta']) ? 1 : 0;
                $stmt = $pdo->prepare("INSERT INTO alternativas (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)");
                $stmt->execute(['pergunta_id' => $pergunta_id,'texto' => $alt['texto'],'correta' => $correta]);
            }
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar ou Editar Quiz</title>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let perguntaIndex = <?php echo count($perguntas_organizacao); ?>;
            const maxPerguntas = 5;
            const container = document.getElementById('perguntas-container');
            const addBtn = document.getElementById('addPergunta');

            function criarBlocoPergunta(index) {
                const div = document.createElement('div');
                div.className = 'pergunta';
                div.innerHTML = `
                    <label>Texto da Pergunta:</label><br>
                    <input type="text" name="perguntas[${index}][texto]" required><br><br>
                    <h4>Alternativas:</h4>
                    ${[0,1,2,3].map(i => `
                        <label>Alternativa ${i+1}:</label><br>
                        <input type="text" name="perguntas[${index}][alternativas][${i}][texto]" required><br>
                        <label>Correta?</label>
                        <input type="checkbox" name="perguntas[${index}][alternativas][${i}][correta]" value="1"><br><br>
                    `).join('')}
                    <button type="button" class="remover-pergunta">Remover Pergunta</button>
                `;
                div.querySelector('.remover-pergunta').addEventListener('click', () => {
                    div.remove();
                    perguntaIndex--;
                    addBtn.disabled = false;
                });
                return div;
            }

            addBtn.addEventListener('click', () => {
                if (perguntaIndex >= maxPerguntas) return;
                container.appendChild(criarBlocoPergunta(perguntaIndex));
                perguntaIndex++;
                if (perguntaIndex >= maxPerguntas) addBtn.disabled = true;
            });
        });
    </script>
</head>
<body>
    <br>
    <a href="meu_quiz.php"><img src="../img/seta.png" height="50px"></a>
    <h1><?php echo $quiz_id ? "Editar Quiz" : "Criar Novo Quiz"; ?></h1>
    <?php if (isset($mensagem)) echo "<p style='color: green;'>$mensagem</p>"; ?>
    <form method="POST">
        <label>Título do Quiz:</label><br>
        <input type="text" name="titulo" value="<?php echo $quiz_data['titulo'] ?? ''; ?>" required><br><br>

        <label>Descrição do Quiz:</label><br>
        <textarea name="descricao" required><?php echo $quiz_data['descricao'] ?? ''; ?></textarea><br><br>

        <label>Tema do Quiz:</label><br>
        <input type="text" name="tema" value="<?php echo $quiz_data['tema'] ?? ''; ?>" required><br><br>

        <h3>Perguntas</h3>
        <div id="perguntas-container">
            <?php $idx=0; foreach ($perguntas_organizacao as $pergunta): ?>
                <div class="pergunta">
                    <label>Texto da Pergunta:</label><br>
                    <input type="text" name="perguntas[<?php echo $idx; ?>][texto]" value="<?php echo htmlspecialchars($pergunta['texto']); ?>" required><br><br>
                    <h4>Alternativas:</h4>
                    <?php foreach ($pergunta['alternativas'] as $altIndex=>$alt): ?>
                        <label>Alternativa <?php echo $altIndex+1; ?>:</label><br>
                        <input type="text" name="perguntas[<?php echo $idx; ?>][alternativas][<?php echo $altIndex; ?>][texto]" value="<?php echo htmlspecialchars($alt['texto']); ?>" required><br>
                        <label>Correta?</label>
                        <input type="checkbox" name="perguntas[<?php echo $idx; ?>][alternativas][<?php echo $altIndex; ?>][correta]" value="1" <?php echo $alt['correta']?'checked':''; ?>><br><br>
                    <?php endforeach; ?>
                    <button type="button" class="remover-pergunta" onclick="this.closest('.pergunta').remove();">Remover Pergunta</button>
                </div>
            <?php $idx++; endforeach; ?>
        </div>
        <button type="button" id="addPergunta">Adicionar Pergunta</button><br><br>
        <button type="submit"><?php echo $quiz_id ? "Atualizar Quiz" : "Criar Quiz"; ?></button>
    </form>
</body>
</html>
