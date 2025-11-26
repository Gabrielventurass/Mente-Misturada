<?php
session_start();

// Verifica se usuário está logado
if (!isset($_SESSION['email'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100">
        <p class="text-red-600 font-semibold">Você precisa estar logado.</p>
    </div>');
}

$email = $_SESSION['email'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recebe filtro por tema
    $temaSelecionado = $_GET['tema'] ?? '';

    if ($temaSelecionado) {
        $stmt = $pdo->prepare("SELECT * FROM quiz WHERE tema = :tema ORDER BY id DESC");
        $stmt->execute(['tema' => $temaSelecionado]);
    } else {
        $stmt = $pdo->query("SELECT * FROM quiz ORDER BY id DESC");
    }
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar todos os temas para o dropdown
    $stmt2 = $pdo->query("SELECT DISTINCT tema FROM quiz");
    $temas = $stmt2->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Início - Quizzes</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">

<div class="w-full max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Quizzes Disponíveis</h1>
<a href="inicio_user.php">vortar</a>
    <!-- Filtro por tema -->
    <form method="GET" class="mb-6 flex items-center space-x-2 justify-center">
        <label for="tema" class="font-semibold text-gray-700">Filtrar por tema:</label>
        <select name="tema" id="tema" class="border rounded px-3 py-2">
            <option value="">Todos</option>
            <?php foreach ($temas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $t == $temaSelecionado ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filtrar</button>
        <?php if ($temaSelecionado): ?>
            <a href="inicio_user.php" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Limpar</a>
        <?php endif; ?>
    </form>

    <!-- Lista de quizzes -->
    <div class="space-y-4">
        <?php if ($quizzes): ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="bg-white p-4 shadow rounded flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg"><?= htmlspecialchars($quiz['art']) ?></h3>
                        <p class="text-gray-600">Tema: <?= htmlspecialchars($quiz['tema']) ?></p>
                    </div>
                    <a href="responder_quiz.php?quiz_id=<?= $quiz['id'] ?>" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">
                       Responder
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-600">Nenhum quiz encontrado para este tema.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
