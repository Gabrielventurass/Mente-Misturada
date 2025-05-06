<?php
require_once "../class/user.class.php";
require_once "../class/adm.class.php";

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $novoUsuario = new usuario($nome, $email, $senha);
        $sucesso = $novoUsuario->inserir();

        if (!$sucesso) {
            $mensagem = $novoUsuario->getErro();
        } else {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $mensagem = "Todos os campos são obrigatórios para criar um usuário.";
    }
}

// Atualizar nome
if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar' && !empty($_POST['email']) && !empty($_POST['novo_nome'])) {
    $email = $_POST['email'];
    $novoNome = $_POST['novo_nome'];
    $tipo = $_POST['tipo'];

    if ($tipo === 'usuario') {
        usuario::atualizarNomePorEmail($email, $novoNome);
    }
    // Admins não podem ter o nome alterado por esta interface
}

// Excluir conta
if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && !empty($_POST['email'])) {
    $email = $_POST['email'];
    $tipo = $_POST['tipo'];

    if ($tipo === 'usuario') {
        usuario::excluirPorEmail($email);
    } elseif ($tipo === 'admin') {
        admin::excluirPorEmail($email);
    }
}

// Listar todos
$busca = $_GET['busca'] ?? '';
$tipoBusca = $_GET['tipo'] ?? 'nome';

if (!empty($busca)) {
    $todos = usuario::listarTodos();
    $usuarios = array_filter($todos, function($u) use ($busca, $tipoBusca) {
        $busca = strtolower($busca);
        $campo = strtolower($u[$tipoBusca] ?? '');
        return str_contains($campo, $busca);
    });
} else {
    $usuarios = usuario::listarTodos();
}

$admins = admin::listarTodos();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários e Admins</title>
    <style>
        table {
            border: 1px solid black;
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
        }
        h2 {
            text-align: center;
        }
        form {
            display: inline;
        }
        input{
            background-color: rgb(185, 185, 185);;
            border: 1px solid black;
            border-radius: 10px;
            padding-right: 150px;
            margin-right: -150px;
        }
        .input{
            background-color: rgb(185, 185, 185);;
            border: 1px solid black;
            width: 300px;
            margin-left: -10%;
        }
    </style>

    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/style_adm.css">
</head>
<body>
    <a href="perfil_adm.php">
        <img src="../img/seta.png" class="btImg" style="rotate: 180;">
    </a>
    <h2>Administradores cadastrados</h2> 
    <table>
        <tr>
            <th>Nome</th><th>Email</th><th>Ações</th>
        </tr>
        <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?= htmlspecialchars($admin['nome']) ?></td>
            <td><?= htmlspecialchars($admin['email']) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($admin['email']) ?>">
                    <input type="hidden" name="tipo" value="admin">
                    <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este administrador?')" class="btExMini">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Buscar usuários</h2>

    <form method="get" style="margin-bottom: 20px; text-align: center;">
        <center>
    <input type="text" name="busca" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" class="input">
    <br>
    <select name="tipo" style="border: 1px solid black; border-radius: 20px; background-color: rgb(185, 185, 185);">
        <option value="nome" <?= (($_GET['tipo'] ?? '') === 'nome') ? 'selected' : '' ?>>Nome</option>
        <option value="email" <?= (($_GET['tipo'] ?? '') === 'email') ? 'selected' : '' ?>>Email</option>
    </select>
    <button type="submit" class="btDfMini">Buscar</button>
    <?php if (!empty($_GET['busca'])): ?>
        <button type="button" class="btResetMini">
            <a href="<?= $_SERVER['PHP_SELF'] ?>">Limpar</a>
        </button>
    <?php endif; ?>
    </center>
</form>



    <h2>Lista de usuários</h2>
    <table>
        <tr>
            <th>Nome</th><th>Email</th><th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td>
                    <form method="post">
                        <input type="hidden" name="acao" value="atualizar">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
                        <input type="hidden" name="tipo" value="usuario">
                        <input type="text" name="novo_nome" value="<?= htmlspecialchars($usuario['nome']) ?>">
                </td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td>
                <button type="submit" class="btDfMini">Atualizar</button>
                    </form>
                <form method="post" style="margin-top: 5px;">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
                    <input type="hidden" name="tipo" value="usuario">
                    <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este usuário?')" class="btExMini">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Criar Novo Usuário</h2>
    <?php if (!empty($mensagem)): ?>
    <p style="color: red; text-align: center;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form method="post" style="width: 60%; margin: 0 auto; display: flex; flex-direction: column; gap: 10px;">
        <input type="hidden" name="acao" value="criar">
        <label>
        Nome:
        <input type="text" name="nome" required>
    </label>
    <label>
        E-mail:
        <input type="email" name="email" required>
    </label>
    <label>
        Senha:
        <input type="password" name="senha" required>
    </label>
    <button type="submit" class="btDfMini">Criar Usuário</button>
</form>

</body>
</html>