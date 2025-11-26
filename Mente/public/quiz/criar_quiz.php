<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para criar ou editar um quiz.";
    exit;
}

$email = $_SESSION['email'];

try {
    // 1. Criar a conexão PDO
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Buscar id do usuário
    $stmtUser = $pdo->prepare("SELECT id FROM usuario WHERE email = :email");
    $stmtUser->execute(['email' => $email]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado no banco!";
        exit;
    }

    $usuario_id = $usuario['id'];

    $quiz_id = $_GET['quiz_id'] ?? null;
    $quiz_data = null;
    $perguntas_organizacao = [];

    if ($quiz_id) {
        $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id AND usuario_id = :usuario_id");
        $stmt->execute(['quiz_id' => $quiz_id, 'usuario_id' => $usuario_id]);
        $quiz_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quiz_data) {
            echo "Quiz não encontrado ou você não tem permissão para editar este quiz.";
            exit;
        }

        $stmt_perguntas = $pdo->prepare(
            "SELECT p.id AS pergunta_id, p.texto AS pergunta_texto, a.id AS alternativa_id, a.texto AS alternativa_texto, a.correta 
            FROM perguntas_user p 
            LEFT JOIN alternativas a ON p.id = a.pergunta_id 
            WHERE p.quiz_id = :quiz_id"
        );
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

// 3. Processar POST para criar ou atualizar
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
            $stmt->execute([
                'titulo' => $quiz_titulo,
                'descricao' => $quiz_descricao,
                'tema' => $quiz_tema,
                'quiz_id' => $quiz_id
            ]);
            $mensagem = "Quiz atualizado com sucesso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO quizzes_user (titulo, descricao, tema, usuario_id) VALUES (:titulo, :descricao, :tema, :usuario_id)");
            $stmt->execute([
                'titulo' => $quiz_titulo,
                'descricao' => $quiz_descricao,
                'tema' => $quiz_tema,
                'usuario_id' => $usuario_id
            ]);
            $quiz_id = $pdo->lastInsertId();
            $mensagem = "Quiz criado com sucesso!";
        }

        if ($quiz_id) {
            // Deleta perguntas e alternativas antigas
            $pdo->prepare("DELETE FROM alternativas WHERE pergunta_id IN (SELECT id FROM perguntas_user WHERE quiz_id = :quiz_id)")->execute(['quiz_id' => $quiz_id]);
            $pdo->prepare("DELETE FROM perguntas_user WHERE quiz_id = :quiz_id")->execute(['quiz_id' => $quiz_id]);
        }

        // Insere novas perguntas e alternativas
        foreach ($perguntas as $pergunta) {
            $stmt = $pdo->prepare("INSERT INTO perguntas_user (quiz_id, texto) VALUES (:quiz_id, :texto)");
            $stmt->execute(['quiz_id' => $quiz_id, 'texto' => $pergunta['texto']]);
            $pergunta_id = $pdo->lastInsertId();
            foreach ($pergunta['alternativas'] as $alt) {
                $correta = isset($alt['correta']) ? 1 : 0;
                $stmt = $pdo->prepare("INSERT INTO alternativas (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)");
                $stmt->execute([
                    'pergunta_id' => $pergunta_id,
                    'texto' => $alt['texto'],
                    'correta' => $correta
                ]);
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let perguntaIndex = <?php echo count($perguntas_organizacao); ?>;
            const maxPerguntas = 5;
            const container = document.getElementById('perguntas-container');
            const addBtn = document.getElementById('addPergunta');

            function criarBlocoPergunta(index) {
                const div = document.createElement('div');
                div.className = 'pergunta border p-4 rounded shadow mb-4';
                div.innerHTML = `
                    <label class='block font-semibold mb-1'>Texto da Pergunta:</label>
                    <input type="text" name="perguntas[${index}][texto]" class="w-full border p-2 rounded mb-4" required>
                    <h4 class="font-semibold mb-2">Alternativas:</h4>
                    ${[0,1,2,3].map(i => `
                        <label class="block">Alternativa ${i+1}:</label>
                        <input type="text" name="perguntas[${index}][alternativas][${i}][texto]" class="w-full border p-2 rounded mb-1" required>
                        <label class="inline-block mr-2">Correta?</label>
                        <input type="checkbox" name="perguntas[${index}][alternativas][${i}][correta]" value="1" class="mb-4">
                    `).join('')}
                    <button type="button" class="remover-pergunta bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Remover Pergunta</button>
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
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <a href="meu_quiz.php" class="inline-block mb-6">
            <img src="../img/seta.png" height="50px">
        </a>
        <h1 class="text-3xl font-bold mb-6">
            <?php echo $quiz_id ? "Editar Quiz" : "Criar Novo Quiz"; ?>
        </h1>
        <?php if (isset($mensagem)) echo "<p class='text-green-600 font-semibold mb-4'>$mensagem</p>"; ?>
        <form method="POST" class="bg-white p-6 rounded shadow space-y-6">
            <div>
                <label class="block font-semibold mb-1">Título do Quiz:</label>
                <input type="text" name="titulo" value="<?php echo $quiz_data['titulo'] ?? ''; ?>" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Descrição do Quiz:</label>
                <textarea name="descricao" class="w-full border p-2 rounded" required><?php echo $quiz_data['descricao'] ?? ''; ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Tema do Quiz:</label>
                <input type="text" name="tema" value="<?php echo $quiz_data['tema'] ?? ''; ?>" class="w-full border p-2 rounded" required>
            </div>
            <div>
                <h3 class="text-xl font-semibold mb-2">Perguntas</h3>
                <div id="perguntas-container">
                    <?php $idx=0; foreach ($perguntas_organizacao as $pergunta): ?>
                        <div class="pergunta border p-4 rounded shadow mb-4">
                            <label class="block font-semibold mb-1">Texto da Pergunta:</label>
                            <input type="text" name="perguntas[<?php echo $idx; ?>][texto]" value="<?php echo htmlspecialchars($pergunta['texto']); ?>" class="w-full border p-2 rounded mb-4" required>
                            <h4 class="font-semibold mb-2">Alternativas:</h4>
                            <?php foreach ($pergunta['alternativas'] as $altIndex=>$alt): ?>
                                <label class="block">Alternativa <?php echo $altIndex+1; ?>:</label>
                                <input type="text" name="perguntas[<?php echo $idx; ?>][alternativas][<?php echo $altIndex; ?>][texto]" value="<?php echo htmlspecialchars($alt['texto']); ?>" class="w-full border p-2 rounded mb-1" required>
                                <label class="inline-block mr-2">Correta?</label>
                                <input type="checkbox" name="perguntas[<?php echo $idx; ?>][alternativas][<?php echo $altIndex; ?>][correta]" value="1" <?php echo $alt['correta']?'checked':''; ?> class="mb-4">
                            <?php endforeach; ?>
                            <button type="button" class="remover-pergunta bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" onclick="this.closest('.pergunta').remove();">Remover Pergunta</button>
                        </div>
                    <?php $idx++; endforeach; ?>
                </div>
                <button type="button" id="addPergunta" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Adicionar Pergunta</button>
            </div>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                <?php echo $quiz_id ? "Atualizar Quiz" : "Criar Quiz"; ?>
            </button>
        </form>
    </div>
</body>
</html>
