<?php
session_start();
require_once("../class/quiz.class.php");

// Apenas administradores devem acessar
// (adicione sua verificação aqui, se necessário)

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar todos os quizzes
    $stmt = $pdo->query("SELECT id, tema, total_questoes FROM quiz ORDER BY id DESC");
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Quizzes</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .bt {
            padding: 4px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btEditar { background-color: #4CAF50; color: white; }
        .btExcluir { background-color: #f44336; color: white; }
    </style>
</head>
<body>
    <a href="../admin/inicio_adm.php"><img src="../img/seta.png" alt="" class="btImg"></a>
    <h1>Quizzes Cadastrados</h1>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
        <p style="color: green;"><strong>Quiz excluído com sucesso.</strong></p>
    <?php endif; ?>

    <?php if (count($quizzes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tema</th>
                    <th>Total de Questões</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?= $quiz['id'] ?></td>
                        <td><?= htmlspecialchars($quiz['tema']) ?></td>
                        <td><?= $quiz['total_questoes'] ?></td>
                        <td>
                            <a href="editar_quiz.php?id=<?= $quiz['id'] ?>" class="bt btEditar">Editar</a>
                            <a href="excluir_quiz.php?id=<?= $quiz['id'] ?>" class="bt btExcluir" onclick="return confirm('Tem certeza que deseja excluir este quiz?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum quiz cadastrado ainda.</p>
    <?php endif; ?>
</body>
</html>
