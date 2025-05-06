<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || !isset($_SESSION['email_usuario'])) {
    header('Location: login.php');
    exit;
}

include 'menu.php';
require_once("../class/user.class.php");

$usuario = Usuario::buscarPorEmail($_SESSION['email_usuario']);

// Conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consultar respostas do usuário
    $stmt = $pdo->prepare("
        SELECT r.quiz_id, r.acertos, r.tempo, r.data_resposta, q.tema, q.total_questoes
        FROM resposta_usuario r
        JOIN quiz q ON r.quiz_id = q.id
        WHERE r.usuario_email = :email
        ORDER BY r.data_resposta DESC
    ");
    $stmt->execute(['email' => $_SESSION['email_usuario']]);
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}

// Verifica se enviaram o formulário de alteração
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoNome = trim($_POST['nome']);

    if (!empty($novoNome)) {
        $usuario->setNome($novoNome);
        if ($usuario->atualizarNome()) {
            $_SESSION['nome_usuario'] = $novoNome; // Atualiza o nome na sessão também!
            $mensagem = "Nome atualizado com sucesso!";
        } else {
            $erro = "Erro ao atualizar nome.";
        }
    } else {
        $erro = "O nome não pode ser vazio.";
    }
}
?>

<head>
    <title>Perfil</title>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
</head>
<h1>Perfil do Usuário</h1>
<br>
<button class="btEx"><a href="logout.php">Deslogar</a></button>

<?php
if (isset($mensagem)) {
    echo "<p style='color: green;'>$mensagem</p>";
}
if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
?>

<p class="text"><strong>Email conectado:</strong> <?php echo htmlspecialchars($usuario->getEmail()); ?></p>

<!-- Mostrar Nome e botão Editar -->
<p class="text">
    <strong>Nome de usuário:</strong> <span id="nomeAtual"><?php echo htmlspecialchars($usuario->getNome()); ?></span>
    <button id="editarBtn" class="btDf" onclick="mostrarFormulario()">Editar</button>
</p>

<!-- Formulário oculto -->
<form id="formEditar" action="perfil.php" method="post" style="display: none;">
    <input type="text" id="nome" name="nome" class="input" value="<?php echo htmlspecialchars($usuario->getNome()); ?>" required><br><br>
    <input type="submit" value="Salvar" class="btDf">
</form>

<script>
function mostrarFormulario() {
    document.getElementById('formEditar').style.display = 'block'; // Mostra o formulário
    document.getElementById('editarBtn').style.display = 'none'; // Esconde o botão editar
}
</script>

<h2>Resultados dos Quizzes Respondidos</h2>

<?php
$totalAcertos = 0;
$totalQuestoes = 0;
$totalTempo = 0;

if (count($respostas) > 0): ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Quiz</th>
                <th>Acertos</th>
                <th>Tempo (segundos)</th>
                <th>Data da Resposta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($respostas as $resposta): 
                $totalAcertos += $resposta['acertos'];
                $totalQuestoes += $resposta['total_questoes'];
                $totalTempo += $resposta['tempo'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($resposta['tema']); ?></td>
                    <td><?php echo $resposta['acertos'] . '/' . $resposta['total_questoes']; ?></td>
                    <td><?php echo $resposta['tempo']; ?> segundos</td>
                    <td><?php echo $resposta['data_resposta']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Resumo Geral</h3>
    <p><strong>Total de Acertos:</strong> <?php echo "$totalAcertos/$totalQuestoes"; ?></p>
    <p><strong>Tempo Total Gasto:</strong> <?php echo "$totalTempo segundos"; ?></p>

<?php else: ?>
    <p>Você ainda não respondeu a nenhum quiz.</p>
<?php endif; ?>
