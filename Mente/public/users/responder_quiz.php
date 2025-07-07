<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder este quiz.";
    exit;
}

$email = $_SESSION['email'];  // Obtemos o email do usuário logado

// Verificar se o ID do quiz foi passado via GET
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    echo "Quiz não encontrado.";
    exit;
}

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter informações do quiz
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo "Quiz não encontrado.";
        exit;
    }

    // Obter perguntas e alternativas do quiz
    $stmt = $pdo->prepare("
        SELECT p.id AS pergunta_id, p.texto AS pergunta_texto, a.id AS alternativa_id, a.texto AS alternativa_texto
        FROM perguntas_user p
        JOIN alternativas a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $perguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Responder Quiz: <?= htmlspecialchars($quiz['titulo']) ?></title>
</head>
<body>
    <h1>Respondendo o Quiz: <?= htmlspecialchars($quiz['titulo']) ?></h1>
    <h3>Tema: <?= htmlspecialchars($quiz['tema']) ?></h3>
    <form method="POST" action="salvar_resposta.php">
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

        <?php foreach ($perguntas as $pergunta): ?>
            <div>
                <p><strong><?= htmlspecialchars($pergunta['pergunta_texto']) ?></strong></p>
                <?php
                    // Exibir alternativas para cada pergunta
                    $stmt = $pdo->prepare("
                        SELECT a.id, a.texto
                        FROM alternativas a
                        WHERE a.pergunta_id = :pergunta_id
                    ");
                    $stmt->execute(['pergunta_id' => $pergunta['pergunta_id']]);
                    $alternativas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($alternativas as $alternativa): 
                ?>
                    <label>
                        <input type="radio" name="respostas[<?= $pergunta['pergunta_id'] ?>]" value="<?= $alternativa['id'] ?>" required>
                        <?= htmlspecialchars($alternativa['texto']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Enviar Respostas</button>
    </form>
</body>
</html>
