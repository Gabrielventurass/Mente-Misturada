<?php
session_start();

if (!isset($_SESSION['nome_admin']) || !isset($_SESSION['email_admin'])) {
    header('Location: login_adm.php');
    exit;
}

include 'menu_adm.php';
require_once("../class/adm.class.php");

$admin = admin::buscarPorEmail($_SESSION['email_admin']);




// Verifica se enviaram o formulário de alteração
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoNome = trim($_POST['nome']);

    if (!empty($novoNome)) {
        $admin->setNome($novoNome);
        if ($admin->atualizarNome()) {
            $_SESSION['nome_admin'] = $novoNome; // Atualiza o nome na sessão também!
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
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
</head>
<h1>Perfil do Usuário</h1>
<br>
<button class="btDf"><a href="aprovar_admin.php">Gerenciar administradores</a></button>
<br><br>
<button class="btDf"><a href="controle.php">Gerenciar usuários  </a></button>
<br><br>
<button class="btEx"><a href="logout_adm.php">Deslogar</a></button>

<?php
if (isset($mensagem)) {
    echo "<p style='color: greenyellow;'>$mensagem</p>";
}
if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}
?>

<p class="text"><strong>Email conectado:</strong> <?php echo htmlspecialchars($admin->getEmail()); ?></p>

<!-- Mostrar Nome e botão Editar -->
<p class="text">
    <strong>Nome de usuário:</strong> <span id="nomeAtual"><?php echo htmlspecialchars($admin->getNome()); ?></span>
    <button id="editarBtn" class="btDf" onclick="mostrarFormulario()">Editar</button>
</p>

<!-- Formulário oculto -->
<form id="formEditar" action="perfil_adm.php" method="post" style="display: none;">
    <input type="text" id="nome" name="nome" class="input" value="<?php echo htmlspecialchars($admin->getNome()); ?>" required><br><br>
    <input type="submit" value="Salvar" class="btDf">
</form>

<script>
function mostrarFormulario() {
    document.getElementById('formEditar').style.display = 'block'; // Mostra o formulário
    document.getElementById('editarBtn').style.display = 'none'; // Esconde o botão editar
}
</script>
