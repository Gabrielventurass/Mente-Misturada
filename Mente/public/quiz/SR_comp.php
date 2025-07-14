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

    // Obter o número de questões no quiz
    $total_questoes = count($gabaritoMap);

    // Calcular a pontuação proporcional para cada questão
    $pontuacao_por_questao = 200 / $total_questoes;
    $pontuacao_total = $acertos * $pontuacao_por_questao;  // Pontuação final

    // Desconto de 1 ponto a cada 2 segundos
    $penalidade_tempo = floor($tempo / 2);  // Penalidade de 1 ponto a cada 2 segundos
    $pontuacao_total -= $penalidade_tempo;  // Desconta a penalidade do tempo

    // Certificar que a pontuação não fique negativa
    if ($pontuacao_total < 0) {
        $pontuacao_total = 0;
    }

    // Atualizar a pontuação do jogador na tabela "usuario"
    $updateStmt = $pdo->prepare("
        UPDATE usuario 
        SET pontuacao = pontuacao + :pontuacao
        WHERE email = :email
    ");
    
    $updateStmt->execute(['pontuacao' => $pontuacao_total, 'email' => $email]);

    // Verificando se o valor foi atualizado corretamente
    if ($updateStmt->rowCount() > 0) {
        $mensagem = "Pontuação atualizada com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar a pontuação ou o usuário não foi encontrado.";
    }

    // Salvar a resposta do usuário na tabela "resposta_usuario" com a pontuação
    $insertStmt = $pdo->prepare("
        INSERT INTO resposta_usuario (usuario_email, quiz_id, acertos, tempo, pontuacao)
        VALUES (:email, :quiz_id, :acertos, :tempo, :pontuacao)
    ");
    $insertStmt->execute([
        'email' => $email, 
        'quiz_id' => $quiz_id, 
        'acertos' => $acertos, 
        'tempo' => $tempo, 
        'pontuacao' => $pontuacao_total
    ]);

    // Exibir o tempo e a pontuação para o usuário
    echo "<h3>Resultado do Quiz</h3>";
    echo "<p><strong>Tempo de Resposta:</strong> {$tempo} segundos</p>";
    echo "<p><strong>Pontuação Final:</strong> {$pontuacao_total} pontos</p>";
    echo "<p>{$mensagem}</p>";

    // Botões para voltar ou continuar
    echo '<br>';
    echo '<a href="../users/inicio_user.php"><button>Voltar</button></a>';
    echo '<a href="comp.php?quiz_id=' . $quiz_id . '"><button>Continuar</button></a>';

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
