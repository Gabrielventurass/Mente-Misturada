<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder quizzes.";
    exit;
}

$email = $_SESSION['email'];

// Pega o id do quiz via GET
if (!isset($_GET['quiz_id'])) {
    echo "Quiz não encontrado.";
    exit;
}

$quizId = (int)$_GET['quiz_id']; // força número

// Caminho correto para a pasta de quizzes gerados em src/quiz_admin/quizzes
$quizFile = __DIR__ . '/../../src/quiz_admin/quizzes/quiz_' . $quizId . '.json';

if (!file_exists($quizFile)) {
    echo "Quiz não encontrado.";
    exit;
}

// Lê o conteúdo do arquivo JSON
$raw = file_get_contents($quizFile);
$quizData = json_decode($raw, true);
if ($quizData === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "Erro ao carregar o quiz (JSON inválido).";
    exit;
}

// Pega ID do usuário no banco
$usuarioId = null;
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $usuarioId = $user['id'];
    } else {
        echo "Usuário não encontrado.";
        exit;
    }
} catch (PDOException $e) {
    echo "Erro: " . htmlspecialchars($e->getMessage());
    exit;
}

// Pasta para salvar respostas
$respostasDir = __DIR__ . '/../../src/quiz_admin/respostas';
if (!is_dir($respostasDir)) mkdir($respostasDir, 0755, true);

// Salvar respostas (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respostas = $_POST['resposta'] ?? [];
    $saveFile = $respostasDir . "/{$usuarioId}_quiz_{$quizId}.json";
    file_put_contents($saveFile, json_encode([
        'usuario_id' => $usuarioId,
        'quiz_id' => $quizId,
        'respostas' => $respostas,
        'data' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "<p class='text-green-600 font-bold'>Respostas salvas com sucesso!</p>";
    echo "<a href='offline.php' class='text-blue-600 underline'>Voltar aos quizzes</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($quizData['titulo'] ?? 'Quiz') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white shadow-md rounded p-6 w-full max-w-3xl">
    <h1 class="text-2xl font-bold mb-4 text-gray-800"><?= htmlspecialchars($quizData['titulo'] ?? 'Quiz') ?></h1>
    <p class="mb-6 text-gray-600"><?= htmlspecialchars($quizData['descricao'] ?? '') ?></p>

    <form method="post">
        <?php if (!empty($quizData['perguntas']) && is_array($quizData['perguntas'])): ?>
            <?php foreach ($quizData['perguntas'] as $index => $p): ?>
                <div class="mb-4 p-3 border rounded">
                    <div class="font-semibold"><?= ($index+1) ?>. <?= htmlspecialchars($p['texto'] ?? 'Pergunta sem texto') ?></div>

                    <?php if (!empty($p['alternativas']) && is_array($p['alternativas'])): ?>
                        <?php foreach ($p['alternativas'] as $optIndex => $opt): ?>
                            <?php $optText = is_array($opt) ? ($opt['texto'] ?? '') : $opt; ?>
                            <label class="block mt-1">
                                <input type="radio" name="resposta[<?= $index ?>]" value="<?= htmlspecialchars($optText) ?>" required>
                                <?= htmlspecialchars($optText) ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="text" name="resposta[<?= $index ?>]" class="w-full border rounded p-2 mt-1" required>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Enviar Respostas
            </button>
        <?php else: ?>
            <p class="text-gray-500">Nenhuma pergunta encontrada neste quiz.</p>
        <?php endif; ?>
    </form>
</div>
</body>
</html>