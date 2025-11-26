<?php
require_once "../config/database.php";
require_once "../class/adm.class.php";

$con = conectarPDO();
$msg = "";

// 🔹 Aprovar admin pendente
if (isset($_GET['aprovar'])) {
    $codigo = intval($_GET['aprovar']);
    $stmt = $con->prepare("SELECT * FROM admins_pendentes WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $pendente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pendente) {
        $insert = $con->prepare("INSERT INTO admin (nome, email, senha, pendente) VALUES (?, ?, ?, 0)");
        $insert->execute([$pendente['nome'], $pendente['email'], $pendente['senha']]);

        $del = $con->prepare("DELETE FROM admins_pendentes WHERE codigo = ?");
        $del->execute([$codigo]);

        $msg = "✅ Admin aprovado com sucesso!";
    } else {
        $msg = "❌ Admin não encontrado!";
    }
}

// 🔹 Rejeitar admin pendente
if (isset($_GET['rejeitar'])) {
    $codigo = intval($_GET['rejeitar']);
    $del = $con->prepare("DELETE FROM admins_pendentes WHERE codigo = ?");
    $del->execute([$codigo]);
    $msg = "🗑️ Admin rejeitado e removido!";
}

// 🔹 Excluir admin aprovado
if (isset($_GET['excluir'])) {
    $codigo = intval($_GET['excluir']);
    $stmt = $con->prepare("SELECT email FROM admin WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $email = $stmt->fetchColumn();

    if ($email) {
        admin::excluirPorEmail($email);
        $msg = "🗑️ Admin excluído!";
    }
}

// 🔹 Buscar pendentes e aprovados
$pendentes = $con->query("SELECT * FROM admins_pendentes ORDER BY dt_cr DESC")->fetchAll(PDO::FETCH_ASSOC);
$aprovados = $con->query("SELECT * FROM admin ORDER BY dt_cr DESC")->fetchAll(PDO::FETCH_ASSOC);

$con = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gerenciar Administradores</title>
<style>
:root{
    --bg-1: #0f1724;
    --bg-2: #1b2540;
    --muted: #a8b3c7;
    --accent: #2d9cdb;
    --success: #16a34a;
    --danger: #e74c3c;
    --warning: #f59e0b;
}
*{box-sizing:border-box}
body{font-family: 'Segoe UI', Roboto, Arial, sans-serif; margin:0; color:#e6eef8; background: linear-gradient(180deg,var(--bg-1),var(--bg-2)); -webkit-font-smoothing:antialiased; padding:36px 18px; display:flex; justify-content:center}
main{width:100%;max-width:1100px}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.04); padding:18px; border-radius:12px; margin:20px 0; box-shadow:0 6px 24px rgba(2,6,23,0.6)}
h2{margin:8px 0 14px 0;color:#fff}
.msg{margin:12px 0;padding:10px;border-radius:8px;background:rgba(45,156,219,0.08);color:var(--accent)}
.table-container{overflow:auto;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:720px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.04)}
th{font-size:13px;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px}
tr:hover td{background:rgba(255,255,255,0.01)}
.actions a{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;text-decoration:none;font-weight:600}
.aprovar{background:linear-gradient(90deg,var(--accent),#6ec1ff);color:#062034}
.rejeitar{background:linear-gradient(90deg,var(--danger),#b91c1c);color:#fff}
.editar{background:linear-gradient(90deg,var(--warning),#facc15);color:#062034}
.excluir{background:transparent;border:1px solid rgba(255,255,255,0.06);color:#fff}
.empty{color:var(--muted);padding:12px}
@media (max-width:720px){body{padding:18px}table{min-width:600px}}
</style>
</head>
<body>

<?php
$h2 = "Painel de Administradores"; 
include 'menu.php';
?>

<main>
    <div class="card">
        <div class="msg"><?= htmlspecialchars($msg) ?: '' ?></div>

        <h2>Admins Pendentes</h2>
        <?php if($pendentes): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Nome</th><th>Email</th><th>Data</th><th style="width:220px">Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendentes as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome']) ?></td>
                            <td><?= htmlspecialchars($p['email']) ?></td>
                            <td><?= $p['dt_cr'] ?></td>
                            <td class="actions">
                                <a class="aprovar" href="?aprovar=<?= $p['codigo'] ?>">✅ Aprovar</a>
                                <a class="rejeitar" href="?rejeitar=<?= $p['codigo'] ?>">✖️ Rejeitar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">Nenhum admin pendente</div>
        <?php endif; ?>

        <h2 style="margin-top:20px">Admins Aprovados</h2>
        <?php if($aprovados): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Nome</th><th>Email</th><th>Data</th><th style="width:220px">Ações</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($aprovados as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['nome']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= $a['dt_cr'] ?></td>
                            <td class="actions">
                                <a class="editar" href="editar_admin.php?email=<?= $a['email'] ?>">✏️ Editar</a>
                                <a class="excluir" href="?excluir=<?= $a['codigo'] ?>">🗑️ Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">Nenhum admin aprovado</div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
