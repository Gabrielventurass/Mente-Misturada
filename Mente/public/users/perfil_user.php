<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

require_once("../../src/class/user.class.php");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se enviou mudança de tema, primeiro aplica e salva
    if (isset($_POST['tema'])) {
        $temaEscolhido = $_POST['tema'] === 'dark' ? 1 : 0;
        $_SESSION['dark'] = $temaEscolhido;
        $stmt = $pdo->prepare("UPDATE usuario SET cor = :cor WHERE email = :email");
        $stmt->execute(['cor' => $temaEscolhido, 'email' => $_SESSION['email']]);
        $tema_atual = $temaEscolhido; // <- garante que valor correto será usado
        header("Location: perfil_user.php");
        exit;
    }

    // Caso não tenha POST de tema, carregamos do banco
    if (!isset($_POST['tema'])) {
        $stmt = $pdo->prepare("SELECT cor FROM usuario WHERE email = :email");
        $stmt->execute(['email' => $_SESSION['email']]);
        $_SESSION['dark'] = (int) $stmt->fetchColumn();
        $tema_atual = $_SESSION['dark'];
    }
    $tema_atual = $_SESSION['dark'];

    // Carrega pontuação sempre
    $stmtPont = $pdo->prepare("SELECT pontuacao FROM usuario WHERE email = :email");
    $stmtPont->execute(['email' => $_SESSION['email']]);
    $pontuacao = $stmtPont->fetchColumn();

    // Respostas dos quizzes
    $stmt_respostas = $pdo->prepare("
        SELECT r.quiz_id, r.acertos, r.tempo, q.tema, q.total_questoes
        FROM resposta_usuario r
        JOIN quiz q ON r.quiz_id = q.id
        WHERE r.usuario_email = :email
        ORDER BY r.data_resposta DESC
    ");
    $stmt_respostas->execute(['email' => $_SESSION['email']]);
    $respostas = $stmt_respostas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}

$usuario = Usuario::buscarPorEmail($_SESSION['email']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'])) {
    $novoNome = trim($_POST['nome']);
    if (!empty($novoNome)) {
        $usuario->setNome($novoNome);
        if ($usuario->atualizarNome()) {
            $_SESSION['nome'] = $novoNome;
            $mensagem = "Nome atualizado com sucesso!";
        } else {
            $erro = "Erro ao atualizar nome.";
        }
    } else {
        $erro = "O nome não pode ser vazio.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="<?= $tema_atual === 1 ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Perfil do Usuário</h1>
        <form method="post" class="mb-4">
            <label class="mr-2 font-medium">Tema:</label>
            <select name="tema" onchange="this.form.submit()" class="border rounded px-2 py-1">
                <option value="light" <?= $tema_atual == 0 ? 'selected' : '' ?>>Claro</option>
                <option value="dark" <?= $tema_atual == 1 ? 'selected' : '' ?>>Escuro</option>
            </select>
        </form>

        <a href="logout.php" class="inline-block bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mb-6">Deslogar</a>

        <?php if (isset($mensagem)) echo "<p class='text-green-500'>$mensagem</p>"; ?>
        <?php if (isset($erro)) echo "<p class='text-red-500'>$erro</p>"; ?>

        <p class="mb-2"><strong>Email conectado:</strong> <?= htmlspecialchars($usuario->getEmail()); ?></p>

        <div class="mb-4">
            <strong>Nome de usuário:</strong>
            <span id="nomeAtual"><?= htmlspecialchars($usuario->getNome()); ?></span>
            <button id="editarBtn" class="ml-2 bg-blue-500 text-white px-3 py-1 rounded" onclick="mostrarFormulario()">Editar</button>
        </div>

        <form id="formEditar" action="perfil_user.php" method="post" style="display: none;">
            <input type="text" id="nome" name="nome" class="border px-2 py-1 rounded mb-2" value="<?= htmlspecialchars($usuario->getNome()); ?>" required>
            <input type="submit" value="Salvar" class="bg-green-500 text-white px-4 py-1 rounded">
        </form>

        <script>
        function mostrarFormulario() {
            document.getElementById('formEditar').style.display = 'block';
            document.getElementById('editarBtn').style.display = 'none';
        }
        </script>

        <h2 class="text-2xl font-bold mt-8 mb-4">Resultados dos Quizzes Respondidos</h2>

        <?php 
        $totalAcertos = 0; $totalQuestoes = 0; $totalTempo = 0;
        if ($respostas):
            foreach ($respostas as $r){$totalAcertos+=$r['acertos'];$totalQuestoes+=$r['total_questoes'];$totalTempo+=$r['tempo'];}
        ?>
            <div class="mb-4">
                <h3 class="text-xl font-semibold mb-2">Resumo Geral</h3>
                <p><strong>Total de Acertos:</strong> <?= "$totalAcertos/$totalQuestoes"; ?></p>
                <p><strong>Tempo Total Gasto:</strong> <?= "$totalTempo segundos"; ?></p>
                <p><strong>Pontuação Atual:</strong> <?= $pontuacao; ?> pontos</p>
            </div>
            <table class="w-full table-auto border-collapse border border-gray-300 dark:border-gray-700">
                <thead><tr class="bg-gray-200 dark:bg-gray-700"><th class="border px-4 py-2">Quiz</th><th class="border px-4 py-2">Acertos</th><th class="border px-4 py-2">Tempo (segundos)</th></tr></thead>
                <tbody>
                    <?php foreach ($respostas as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800"><td class="border px-4 py-2"><?= htmlspecialchars($r['tema']); ?></td><td class="border px-4 py-2"><?= $r['acertos'].'/'.$r['total_questoes']; ?></td><td class="border px-4 py-2"><?= $r['tempo']; ?> segundos</td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?><p>Você ainda não respondeu a nenhum quiz.</p><?php endif; ?>
    </div>
</body>
</html>
