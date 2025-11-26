<?php
// salvar_quiz.php
session_start();
require_once "../class/user.class.php"; // se necessário
require_once "../class/quiz.class.php"; // se tiver funções auxiliares

if (!isset($_SESSION['email'])) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100">
        <p class="text-red-600 font-semibold">Você precisa estar logado para salvar quizzes.</p>
    </div>');
}

$email = $_SESSION['email'];

$quizId = $_POST['id'] ?? null;
if (!$quizId) {
    die("ID do quiz inválido.");
}

// Conexão PDO
$pdo = new PDO("mysql:host=localhost;dbname=mente;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Checar se o quiz existe em SQL
$stmt = $pdo->prepare("SELECT id FROM quiz WHERE id = ?");
$stmt->execute([$quizId]);
$quizSQL = $stmt->fetch(PDO::FETCH_ASSOC);

$titulo = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$tema = $_POST['tema'] ?? '';
$perguntas = $_POST['perguntas'] ?? [];

try {
    if ($quizSQL) {
        // ---------- SALVAR NO SQL ----------
        // Atualizar tabela quiz
        $stmt = $pdo->prepare("UPDATE quiz SET titulo = ?, descricao = ?, tema = ? WHERE id = ?");
        $stmt->execute([$titulo, $descricao, $tema, $quizId]);

        foreach ($perguntas as $p) {
            $perguntaId = $p['id'] ?? null;
            if ($perguntaId) {
                // Atualizar texto da pergunta
                $stmt = $pdo->prepare("UPDATE pergunta SET texto = ? WHERE id = ?");
                $stmt->execute([$p['texto'], $perguntaId]);

                // Atualizar alternativas
                foreach ($p['alternativas'] as $alt) {
                    $altId = $alt['id'] ?? null;
                    if ($altId) {
                        $correta = !empty($alt['correta']) ? 1 : 0;
                        $stmt = $pdo->prepare("UPDATE alternativa SET texto = ?, correta = ? WHERE id = ?");
                        $stmt->execute([$alt['texto'], $correta, $altId]);
                    }
                }
            }
        }

    } else {
        // ---------- SALVAR NO JSON ----------
        $arquivo = __DIR__ . "/quizzes/$quizId.json";

        $jsonData = [
            'id' => $quizId,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'tema' => $tema,
            'perguntas' => []
        ];

        foreach ($perguntas as $p) {
            $perguntaJson = [
                'id' => $p['id'] ?? null,
                'texto' => $p['texto'] ?? '',
                'alternativas' => []
            ];

            foreach ($p['alternativas'] as $alt) {
                $perguntaJson['alternativas'][] = [
                    'id' => $alt['id'] ?? null,
                    'texto' => $alt['texto'] ?? '',
                    'correta' => !empty($alt['correta']) ? true : false
                ];
            }

            $jsonData['perguntas'][] = $perguntaJson;
        }

        file_put_contents($arquivo, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    echo '<div class="min-h-screen flex items-center justify-center bg-gray-100">
            <p class="text-green-600 font-semibold">Quiz salvo com sucesso!</p>
            <a href="editar_quiz.php?editar=' . htmlspecialchars($quizId) . '" class="btn btn-primary mt-4">Voltar ao Quiz</a>
          </div>';

} catch (PDOException $e) {
    die('<div class="min-h-screen flex items-center justify-center bg-gray-100">
            <p class="text-red-600 font-semibold">Erro ao salvar quiz: ' . htmlspecialchars($e->getMessage()) . '</p>
          </div>');
}
?>
