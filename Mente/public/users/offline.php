<?php
session_start();

// check online status / login
$usuarioLogado = isset($_SESSION['email']);

// caminho da pasta dos quizzes JSON
$quizPath = __DIR__ . '/../../src/quiz_admin/quizzes';
$files = glob($quizPath . '/*.json');

$quizzes = [];
foreach ($files as $file) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if ($data) {
        // pega só o número depois de "quiz_"
        $id = preg_replace('/^quiz_/', '', basename($file, '.json'));

        $quizzes[] = [
            'id' => $id,
            'titulo' => $data['titulo'] ?? 'Quiz sem título',
            'tema' => $data['tema'] ?? 'Sem tema'
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Quizzes JSON</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white shadow-md rounded p-6 w-full max-w-3xl">
    <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">Quizzes da Comunidade (JSON)</h1>
    <p class="mb-6 text-center text-gray-600">Escolha um quiz para responder:</p>
    <a href="inicio_user.php">voltar</a>
    <?php if (count($quizzes) > 0): ?>
        <ul class="space-y-3">
            <?php foreach ($quizzes as $quiz): ?>
                <li class="flex items-center justify-between bg-gray-50 p-3 rounded">
                    <div>
                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($quiz['titulo']) ?></div>
                        <div class="text-sm text-gray-500">Tema: <?= htmlspecialchars($quiz['tema']) ?></div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="responder_quiz_json.php?quiz_id=<?= $quiz['id'] ?>" 
                           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-3 rounded">
                            Responder
                        </a>

                        <?php if ($usuarioLogado): ?>
                        <?php else: ?>
                        <button
                          class="bg-gray-400 text-white font-semibold py-2 px-3 rounded cursor-not-allowed"
                          title="Comentários só disponíveis online">
                            Comentários
                        </button>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-gray-500 text-center">Nenhum quiz JSON disponível.</p>
    <?php endif; ?>
</div>

<!-- Modal comentários só é útil se o usuário estiver online -->
<?php if ($usuarioLogado): ?>
<div id="comentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <!-- resto do modal igual ao código anterior -->
</div>
<?php endif; ?>

<script>
<?php if ($usuarioLogado): ?>
// JS do modal de comentários igual ao exemplo anterior
<?php endif; ?>
</script>
</body>
</html>
