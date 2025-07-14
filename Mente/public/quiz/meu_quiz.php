<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

include 'menu_quiz.php'; // Ajuste o caminho conforme necessário
require_once("../../src/class/user.class.php");

// Conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultar os quizzes do usuário
    $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE usuario_email = :email");
    $stmt->execute(['email' => $_SESSION['email']]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>

<head>
    <title>Meus Quizzes</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<h1>Meus Quizzes</h1>
<br>
<button class="btEx"><a href="logout.php">Deslogar</a></button>

<!-- Formulário para criar novo quiz -->
<h2>Criar Novo Quiz</h2>
<a href="criar_quiz.php"><button class="btDf">Criar Quiz</button></a>

<!-- Exibição dos quizzes criados -->
<h2>Meus Quizzes Criados</h2>
<?php if (count($quizzes) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
                <tr>
                    <td><?php echo htmlspecialchars($quiz['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($quiz['descricao']); ?></td>
                    <td>
                        <!-- Link para editar o quiz -->
                        <a href="editar_quiz.php?quiz_id=<?php echo $quiz['id']; ?>"><button class="btDf">Editar</button></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Você ainda não criou nenhum quiz.</p>
<?php endif; ?>
