<?php 
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

include 'menu_user.php';
require_once("../../src/class/user.class.php");

$usuario = Usuario::buscarPorEmail($_SESSION['email']);

// Conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consultar a pontuação do usuário
    $stmt = $pdo->prepare("SELECT pontuacao FROM usuario WHERE email = :email");
    $stmt->execute(['email' => $_SESSION['email']]);
    $pontuacao = $stmt->fetchColumn();

    // Consultar as respostas do usuário
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

// Verifica se enviaram o formulário de alteração
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoNome = trim($_POST['nome']);

    if (!empty($novoNome)) {
        $usuario->setNome($novoNome);
        if ($usuario->atualizarNome()) {
            $_SESSION['nome'] = $novoNome; // Atualiza o nome na sessão também!
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
<form id="formEditar" action="perfil_user.php" method="post" style="display: none;">
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

    <!-- Agora calculamos os totais antes de exibir o resumo geral -->
    <?php 
    foreach ($respostas as $resposta): 
        $totalAcertos += $resposta['acertos'];
        $totalQuestoes += $resposta['total_questoes'];
        $totalTempo += $resposta['tempo'];
    endforeach;
    ?>

    <!-- Resumo Geral - agora será exibido corretamente com os valores calculados -->
    <h3>Resumo Geral</h3>
    <p><strong>Total de Acertos:</strong> <br>
        <?php echo "$totalAcertos/$totalQuestoes"; ?></p>
    <p><strong>Tempo Total Gasto:</strong> <br>
        <?php echo "$totalTempo segundos"; ?></p>
    <p><strong>Pontuação Atual:</strong></p>
        <p><?php echo $pontuacao; ?> pontos</p>

    <table>
        <thead>
            <tr>
                <th>Quiz</th>
                <th>Acertos</th>
                <th>Tempo (segundos)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($respostas as $resposta): ?>
                <tr>
                    <td><?php echo htmlspecialchars($resposta['tema']); ?></td>
                    <td><?php echo $resposta['acertos'] . '/' . $resposta['total_questoes']; ?></td>
                    <td><?php echo $resposta['tempo']; ?> segundos</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else: ?>
    <p>Você ainda não respondeu a nenhum quiz.</p>
<?php endif; ?>

