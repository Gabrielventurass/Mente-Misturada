<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}// Ajuste o caminho conforme necessário

// Conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o quiz_id foi passado
    if (isset($_GET['quiz_id'])) {
        $quiz_id = $_GET['quiz_id'];

        // Consultar os dados do quiz
        $stmt = $pdo->prepare("SELECT * FROM quizzes_user WHERE id = :quiz_id AND usuario_email = :email");
        $stmt->execute(['quiz_id' => $quiz_id, 'email' => $_SESSION['email']]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quiz) {
            echo "Quiz não encontrado ou você não tem permissão para editar este quiz.";
            exit;
        }

    } else {
        echo "ID do quiz não fornecido.";
        exit;
    }

    // Editar quiz
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $titulo = trim($_POST['titulo']);
        $descricao = trim($_POST['descricao']);

        if (!empty($titulo) && !empty($descricao)) {
            $stmt = $pdo->prepare("UPDATE quizzes_user SET titulo = :titulo, descricao = :descricao WHERE id = :quiz_id");
            $stmt->execute(['titulo' => $titulo, 'descricao' => $descricao, 'quiz_id' => $quiz_id]);
            $mensagem = "Quiz atualizado com sucesso!";
        } else {
            $erro = "Título e descrição não podem estar vazios.";
        }
    }

    // Excluir quiz
    if (isset($_POST['excluir_quiz'])) {
        $stmt_delete_quiz = $pdo->prepare("DELETE FROM quizzes_user WHERE id = :quiz_id");
        $stmt_delete_quiz->execute(['quiz_id' => $quiz_id]);
        $mensagem = "Quiz excluído com sucesso!";
        header("Location: meu_quiz.php"); // Redireciona de volta para a lista de quizzes
        exit;
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>

<head>
    <title>Editar Quiz</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
</head>

<h1>Editar Quiz</h1>

<?php
if (isset($mensagem)) {
    echo "<p style='color: green;'>$mensagem</p>";
}
if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
?>

    <a href="meu_quiz.php"><img src="../img/seta.png" height="50px"></a>
<form action="editar_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" method="post">
    <label for="titulo">Título do Quiz:</label><br>
    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($quiz['titulo']); ?>" required><br><br>
    <label for="descricao">Descrição do Quiz:</label><br>
    <textarea id="descricao" name="descricao" rows="4" cols="50" required><?php echo htmlspecialchars($quiz['descricao']); ?></textarea><br><br>
    <input type="submit" value="Atualizar Quiz" class="btDf">
</form>

<form action="editar_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" method="post">
    <input type="submit" name="excluir_quiz" value="Excluir Quiz" class="btDf" onclick="return confirm('Tem certeza que deseja excluir este quiz?');">
</form>
