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
    // Conexão com o banco de dados
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

    // Definir a pontuação total
    // Se o quiz tiver 1 questão, ela vale 200 pontos
    // Se o quiz tiver 2 questões, cada questão vale 100 pontos (total de 200 pontos)
    $total_pontos = 200;
    $pontos_por_questao = $total_pontos / count($gabarito);  // Média dos pontos por questão

    // Calcular a pontuação
    $pontuacao_base = $acertos * $pontos_por_questao;  // Pontuação baseada nos acertos

    // Penalidade pelo tempo (1 ponto por segundo)
    $penalidade_tempo = $tempo; // Penalidade de 1 ponto por segundo
    $pontuacao_final = max(0, $pontuacao_base - $penalidade_tempo); // A pontuação não pode ser menor que 0

    // Salvar resultado no banco de dados
    $stmt = $pdo->prepare("
        INSERT INTO resposta_usuario (usuario_email, quiz_id, acertos, tempo, pontuacao)
        VALUES (:email, :quiz_id, :acertos, :tempo, :pontuacao)
    ");
    $stmt->execute([
        'email' => $email,
        'quiz_id' => $quiz_id,
        'acertos' => $acertos,
        'tempo' => intval($tempo),
        'pontuacao' => $pontuacao_final
    ]);

    // Exibe a pontuação final
    echo "<h2>Você acertou <strong>$acertos</strong> pergunta(s)!</h2>";
    echo "<p>Tempo gasto: <strong>" . intval($tempo) . "</strong> segundos</p>";
    echo "<p>Sua pontuação final: <strong>$pontuacao_final</strong> pontos</p>";

    // Opções de navegação
    echo '<form method="post" action="comp.php">';
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
