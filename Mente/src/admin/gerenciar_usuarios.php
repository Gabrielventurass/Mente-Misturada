<?php
session_start();

if (!isset($_SESSION['adm_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Acesso negado.';
    exit;
}

include '../config/config.inc.php';

$dsn = DSN;
$dbUser = USUARIO;
$dbPass = SENHA;

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die('Erro ao conectar ao banco.');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

function e($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== $csrf) {
        $messages[] = ['danger', 'Token inválido.'];
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];
            $adm = !empty($_POST['adm']) ? 1 : 0;

            if ($nome && $email && $senha) {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuario (nome,email,senha,is_admin) VALUES (?,?,?,?)");
                $stmt->execute([$nome,$email,$hash,$adm]);
                $messages[] = ['success','Usuário criado.'];
            } else {
                $messages[] = ['danger','Preencha todos os campos.'];
            }
        }

        if ($action === 'edit') {
            $id = intval($_POST['id']);
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $adm = !empty($_POST['adm']) ? 1 : 0;

            if ($id && $nome && $email) {
                if (!empty($_POST['senha'])) {
                    $hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuario SET nome=?, email=?, senha=?, is_admin=? WHERE id=?");
                    $stmt->execute([$nome,$email,$hash,$adm,$id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuario SET nome=?, email=?, is_admin=? WHERE id=?");
                    $stmt->execute([$nome,$email,$adm,$id]);
                }
                $messages[] = ['success','Usuário atualizado.'];
            }
        }
    }
}

if (!empty($_GET['del']) && !empty($_GET['token']) && hash_equals($_GET['token'],$csrf)) {
    $id = intval($_GET['del']);
    if ($id > 0) {
        $del = $pdo->prepare("DELETE FROM usuario WHERE id=?");
        $del->execute([$id]);
        $messages[] = ['success','Usuário deletado.'];
    }
}

$users = $pdo->query("SELECT * FROM usuario ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="/public/css/style_admin.css">
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

/* RESET */
*{ box-sizing:border-box; }
body{
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    margin:0;
    background:linear-gradient(180deg,var(--bg-1),var(--bg-2));
    color:#e6eef8;
    padding:36px 18px;
    -webkit-font-smoothing:antialiased;
}
main{
    width:100%;
    max-width:1100px;
    margin:0 auto;
}

/* CARD */
.card{
    background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01));
    border:1px solid rgba(255,255,255,0.06);
    padding:18px;
    border-radius:12px;
    margin:20px 0;
    box-shadow:0 6px 24px rgba(2,6,23,0.6);
}

/* TÍTULOS */
h2{
    margin:8px 0 16px 0;
    color:#fff;
    font-weight:600;
}

/* MENSAGENS */
.msg span{
    display:block;
    padding:10px;
    border-radius:8px;
    margin:10px 0;
    background:rgba(45,156,219,0.08);
}
.msg .success{ color:var(--success); }
.msg .danger{ color:var(--danger); }

/* FORM */
.form-wrap input[type="text"],
.form-wrap input[type="email"],
.form-wrap input[type="password"]{
    padding:10px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,0.12);
    background:rgba(255,255,255,0.05);
    color:#fff;
    width:100%;
}
.row{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

/* BOTÕES */
.btn{
    padding:10px 16px;
    border-radius:8px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
    cursor:pointer;
    text-align:center;
}
.btn-primary{
    background:linear-gradient(90deg,var(--accent),#79c7ff);
    color:#062034;
    border:none;
}
.btn-danger{
    background:transparent;
    border:1px solid rgba(255,255,255,0.2);
    color:#fff;
}

/* TABELA */
.table-container{
    overflow:auto;
    border-radius:10px;
}
.styled-table{
    width:100%;
    border-collapse:collapse;
    min-width:720px;
}
.styled-table th,
.styled-table td{
    padding:12px 14px;
    border-bottom:1px solid rgba(255,255,255,0.06);
}
.styled-table th{
    text-transform:uppercase;
    font-size:12px;
    letter-spacing:0.6px;
    color:var(--muted);
}
.styled-table tr:hover td{
    background:rgba(255,255,255,0.02);
}

/* CHECKBOX */
input[type="checkbox"]{
    width:18px;
    height:18px;
}
</style>

</head>
<body class="dark-mode">
<?php 
$h2 = "👤 Gerenciar Usuários";
include '../quiz_admin/menu_quiz.php';
?>

<main>
    <div class="card" style="max-width:1000px;margin:0 auto;">
        <div class="msg" style="margin-bottom:18px;">
            <?php foreach($messages as $m): ?>
                <span class="muted <?php echo e($m[0]); ?>"> <?php echo e($m[1]); ?> </span>
            <?php endforeach; ?>
        </div>

        <h2 style="margin-bottom:18px;color:#fff">Usuários</h2>

        <fieldset class="card" style="margin-bottom:18px;border:1.5px solid rgba(45,156,219,0.18);">
            <legend class="title" style="color:#2d9cdb;font-size:18px;padding:0 8px;">Novo Usuário</legend>
            <form method="post" class="form-wrap">
                <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <input type="text" name="nome" placeholder="Nome" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="senha" placeholder="Senha" required>
                    <label style="display:flex;align-items:center;gap:4px;"><input type="checkbox" name="adm"> Admin</label>
                </div>
                <button class="btn btn-primary" style="margin-top:10px;">Criar</button>
            </form>
        </fieldset>

        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <form method="post">
                            <td><?php echo e($u['id']); ?><input type="hidden" name="id" value="<?php echo e($u['id']); ?>"></td>
                            <td><input type="text" name="nome" value="<?php echo e($u['nome']); ?>" required></td>
                            <td><input type="email" name="email" value="<?php echo e($u['email']); ?>" required></td>
                            <td style="text-align:center"><input type="checkbox" name="adm" <?php echo $u['is_admin'] ? 'checked' : ''; ?>></td>
                            <td style="display:flex;gap:8px;align-items:center;">
                                <input type="password" name="senha" placeholder="Nova senha">
                                <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>">
                                <input type="hidden" name="action" value="edit">
                                <button class="btn btn-primary">Salvar</button>
                                <a class="btn btn-danger" href="?del=<?php echo e($u['id']); ?>&token=<?php echo e($csrf); ?>" onclick="return confirm('Excluir?');">Excluir</a>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
