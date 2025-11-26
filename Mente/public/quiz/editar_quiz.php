<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

$email = $_SESSION['email'];
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100 text-center p-4">
        <div class="bg-white shadow rounded p-6">
            <p class="text-gray-800 font-semibold">ID do quiz não fornecido.</p>
            <a href="meu_quiz.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </div>');
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pegar ID do usuário
    $stmtUser = $pdo->prepare("SELECT id FROM usuario WHERE email = :email");
    $stmtUser->execute(['email' => $email]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) die("Usuário não encontrado!");

    $usuario_id = $usuario['id'];

    // Buscar quiz do usuário
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id AND usuario_id = :usuario_id");
    $stmt->execute(['quiz_id' => $quiz_id, 'usuario_id' => $usuario_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$quiz) die("Quiz não encontrado ou você não tem permissão.");

    // Buscar perguntas e alternativas
    $stmt_perguntas = $pdo->prepare("
        SELECT p.id AS pergunta_id, p.texto AS pergunta_texto,
               a.id AS alternativa_id, a.texto AS alternativa_texto, a.correta
        FROM perguntas_user p
        LEFT JOIN alternativas a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id
        ORDER BY p.id, a.id
    ");
    $stmt_perguntas->execute(['quiz_id' => $quiz_id]);
    $perguntas_raw = $stmt_perguntas->fetchAll(PDO::FETCH_ASSOC);

    $perguntas = [];
    foreach ($perguntas_raw as $row) {
        $pid = $row['pergunta_id'];
        if (!isset($perguntas[$pid])) {
            $perguntas[$pid] = [
                'texto' => $row['pergunta_texto'],
                'alternativas' => []
            ];
        }
        if ($row['alternativa_id']) {
            $perguntas[$pid]['alternativas'][] = [
                'id' => $row['alternativa_id'],
                'texto' => $row['alternativa_texto'],
                'correta' => $row['correta']
            ];
        }
    }

    // Processar atualização
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['excluir_quiz'])) {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $tema = trim($_POST['tema'] ?? $quiz['tema']);
        $novas_perguntas = $_POST['perguntas'] ?? [];

        if ($titulo && $descricao && !empty($novas_perguntas)) {
            // Atualiza quiz
            $stmt = $pdo->prepare("UPDATE quizzes_user SET titulo = :titulo, descricao = :descricao, tema = :tema WHERE id = :quiz_id");
            $stmt->execute([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'tema' => $tema,
                'quiz_id' => $quiz_id
            ]);

            // Apagar perguntas e alternativas antigas
            $pdo->prepare("DELETE FROM alternativas WHERE pergunta_id IN (SELECT id FROM perguntas_user WHERE quiz_id = :quiz_id)")->execute(['quiz_id' => $quiz_id]);
            $pdo->prepare("DELETE FROM perguntas_user WHERE quiz_id = :quiz_id")->execute(['quiz_id' => $quiz_id]);

            // Inserir novas perguntas e alternativas
            foreach ($novas_perguntas as $p) {
                $stmt = $pdo->prepare("INSERT INTO perguntas_user (quiz_id, texto) VALUES (:quiz_id, :texto)");
                $stmt->execute(['quiz_id' => $quiz_id, 'texto' => $p['texto']]);
                $pid = $pdo->lastInsertId();

                foreach ($p['alternativas'] as $alt) {
                    $correta = isset($alt['correta']) ? 1 : 0;
                    $stmt_alt = $pdo->prepare("INSERT INTO alternativas (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)");
                    $stmt_alt->execute([
                        'pergunta_id' => $pid,
                        'texto' => $alt['texto'],
                        'correta' => $correta
                    ]);
                }
            }

            $mensagem = "Quiz atualizado com sucesso!";
            // Atualizar variáveis para mostrar no form
            $quiz['titulo'] = $titulo;
            $quiz['descricao'] = $descricao;
            $quiz['tema'] = $tema;
            $perguntas = $novas_perguntas;
        } else {
            $erro = "Preencha título, descrição e adicione perguntas!";
        }
    }

    // Processar exclusão
    if (isset($_POST['excluir_quiz'])) {
        $pdo->prepare("DELETE FROM alternativas WHERE pergunta_id IN (SELECT id FROM perguntas_user WHERE quiz_id = :quiz_id)")->execute(['quiz_id' => $quiz_id]);
        $pdo->prepare("DELETE FROM perguntas_user WHERE quiz_id = :quiz_id")->execute(['quiz_id' => $quiz_id]);
        $pdo->prepare("DELETE FROM quizzes_user WHERE id = :quiz_id")->execute(['quiz_id' => $quiz_id]);
        header("Location: meu_quiz.php");
        exit;
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Quiz</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let perguntaIndex = <?= count($perguntas) ?>;
    const maxPerguntas = 5;
    const container = document.getElementById('perguntas-container');
    const addBtn = document.getElementById('addPergunta');

    function criarBlocoPergunta(index, texto = '', alternativas = []) {
        const div = document.createElement('div');
        div.className = 'pergunta border p-4 rounded shadow mb-4';
        div.innerHTML = `
            <label class='block font-semibold mb-1'>Texto da Pergunta:</label>
            <input type="text" name="perguntas[${index}][texto]" class="w-full border p-2 rounded mb-4" value="${texto}" required>
            <h4 class="font-semibold mb-2">Alternativas:</h4>
            ${[0,1,2,3].map(i => {
                const alt = alternativas[i] || {texto:'', correta:0};
                return `
                    <label class="block">Alternativa ${i+1}:</label>
                    <input type="text" name="perguntas[${index}][alternativas][${i}][texto]" class="w-full border p-2 rounded mb-1" value="${alt.texto}" required>
                    <label class="inline-block mr-2">Correta?</label>
                    <input type="checkbox" name="perguntas[${index}][alternativas][${i}][correta]" value="1" ${alt.correta?'checked':''} class="mb-4">
                `;
            }).join('')}
            <button type="button" class="remover-pergunta bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Remover Pergunta</button>
        `;
        div.querySelector('.remover-pergunta').addEventListener('click', () => {
            div.remove();
            perguntaIndex--;
            addBtn.disabled = false;
        });
        return div;
    }

    // Renderiza perguntas existentes
    <?php $idx=0; foreach ($perguntas as $p): ?>
        container.appendChild(criarBlocoPergunta(<?=$idx?>, <?= json_encode($p['texto']) ?>, <?= json_encode($p['alternativas']) ?>));
    <?php $idx++; endforeach; ?>

    addBtn.addEventListener('click', () => {
        if (perguntaIndex >= maxPerguntas) return;
        container.appendChild(criarBlocoPergunta(perguntaIndex));
        perguntaIndex++;
        if (perguntaIndex >= maxPerguntas) addBtn.disabled = true;
    });
});
</script>
</head>
<body class="bg-gray-100 min-h-screen py-10">
<div class="max-w-3xl mx-auto bg-white p-6 shadow rounded">
<h1 class="text-2xl font-bold mb-4 text-gray-800">Editar Quiz</h1>

<?php if(isset($mensagem)): ?><p class="text-green-600 mb-4 font-semibold"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
<?php if(isset($erro)): ?><p class="text-red-600 mb-4 font-semibold"><?= htmlspecialchars($erro) ?></p><?php endif; ?>

<form method="POST" class="space-y-4">
    <div>
        <label class="block font-semibold mb-1">Título:</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($quiz['titulo']) ?>" class="w-full border p-2 rounded" required>
    </div>
    <div>
        <label class="block font-semibold mb-1">Descrição:</label>
        <textarea name="descricao" class="w-full border p-2 rounded" required><?= htmlspecialchars($quiz['descricao']) ?></textarea>
    </div>
    <div>
        <label class="block font-semibold mb-1">Tema:</label>
        <input type="text" name="tema" value="<?= htmlspecialchars($quiz['tema']) ?>" class="w-full border p-2 rounded" required>
    </div>
    <div>
        <h3 class="text-xl font-semibold mb-2">Perguntas</h3>
        <div id="perguntas-container"></div>
        <button type="button" id="addPergunta" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Adicionar Pergunta</button>
    </div>
    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 mt-4">Atualizar Quiz</button>
</form>

<form method="POST" class="mt-6">
    <button type="submit" name="excluir_quiz" value="1" onclick="return confirm('Tem certeza que deseja excluir este quiz?');" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold">Excluir Quiz</button>
</form>
</div>
</body>
</html>
