<?php
require_once "../../src/class/quiz.class.php";
session_start();

echo "<center>";
if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para enviar respostas.";
    exit;
}

$email = $_SESSION['email'];
$quiz_id = $_POST['quiz_id'] ?? null;
$respostas = $_POST['respostas'] ?? [];
$tempo = $_POST['tempo'] ?? 0;

if (!$quiz_id || empty($respostas)) {
    echo "Respostas incompletas.";
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obter o gabarito correto
    $stmt = $pdo->prepare("
        SELECT p.id AS pergunta_id, a.texto AS resposta_correta
        FROM pergunta p
        JOIN alternativa a ON p.id = a.pergunta_id
        WHERE p.quiz_id = :quiz_id AND a.correta = 1
    ");
    $stmt->execute(['quiz_id' => $quiz_id]);
    $gabarito = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapear gabarito para fácil comparação
    $gabaritoMap = [];
    foreach ($gabarito as $item) {
        $gabaritoMap[$item['pergunta_id']] = $item['resposta_correta'];
    }

    // Comparar respostas
    $acertos = 0;
    foreach ($respostas as $pergunta_id => $resposta_usuario) {
        if (isset($gabaritoMap[$pergunta_id]) && $gabaritoMap[$pergunta_id] === $resposta_usuario) {
            $acertos++;
        }
    }

    // Salvar resultado
    $stmt = $pdo->prepare("
        INSERT INTO resposta_usuario (usuario_email, quiz_id, acertos, tempo)
        VALUES (:email, :quiz_id, :acertos, :tempo)
    ");
    $stmt->execute([
        'email' => $email,
        'quiz_id' => $quiz_id,
        'acertos' => $acertos,
        'tempo' => intval($tempo)
    ]);

    echo "<h2>Você acertou <strong>$acertos</strong> pergunta(s)!</h2>";
    echo "<p>Tempo gasto: <strong>" . intval($tempo) . "</strong> segundos</p>";

    echo '<form method="post" action="vsTemp.php">';
    echo '    <button type="submit" class="btDf">Próxima rodada</button>';
    echo '</form>';

    echo '<form method="get" action="../users/inicio_user.php">';
    echo '    <button type="submit" class="btEx">Voltar</button>';
    echo '</form>';

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

echo "</center>";

?>