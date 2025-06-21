<?php

declare(strict_types=1);

require_once '../config/config.inc.php';

session_start();

// Aqui você pode proteger com um login de "super admin" se quiser
// if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] !== 'super') die("Acesso negado");

try {
    $pdo = new PDO(DSN, ADMIN, SENHA);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = $_POST['codigo'];
        $acao = $_POST['acao'];

        $stmt = $pdo->prepare('SELECT * FROM admins_pendentes WHERE codigo = ?');
        $stmt->execute([$codigo]);
        $pendente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pendente) {
            if ($acao === 'aprovar') {
                $inserir = $pdo->prepare(
                    'INSERT INTO admin (nome, email, senha) VALUES (?, ?, ?)'
                );
                $inserir->execute([
                    $pendente['nome'],
                    $pendente['email'],
                    $pendente['senha']
                ]);
            }

            $excluir = $pdo->prepare('DELETE FROM admins_pendentes WHERE codigo = ?');
            $excluir->execute([$codigo]);
        }
    }

    $lista = $pdo
        ->query('SELECT * FROM admins_pendentes ORDER BY dt_cr DESC')
        ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro no banco de dados: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Aprovação de Administradores</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        form {
            display: inline;
        }
    </style>
</head>
<body>
    <a href="perfil_adm.php"><img src="../img/seta.png" alt="" class="btImg"></a>
    <h2 style="text-align: center;">Administradores Pendentes de Aprovação</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Data de Cadastro</th>
            <th>Ações</th>
        </tr>

        <?php foreach ($lista as $admin): ?>
            <tr>
                <td><?= htmlspecialchars($admin['codigo']) ?></td>
                <td><?= htmlspecialchars($admin['nome']) ?></td>
                <td><?= htmlspecialchars($admin['email']) ?></td>
                <td><?= htmlspecialchars($admin['dt_cr']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="codigo" value="<?= $admin['codigo'] ?>">
                        <input type="hidden" name="acao" value="aprovar">
                        <button type="submit">Aprovar</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="codigo" value="<?= $admin['codigo'] ?>">
                        <input type="hidden" name="acao" value="rejeitar">
                        <button type="submit" onclick="return confirm('Deseja rejeitar?')">Rejeitar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
