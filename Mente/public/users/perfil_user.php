<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}

require_once("../../src/class/user.class.php");
require_once __DIR__ . '/../../src/config/database.php';
// Caminho do arquivo JSON de preferências
$preferenciasPath = __DIR__ . '/preferencias.json';

// Função para ler preferências do JSON
function lerPreferencias($path) {
    if (!file_exists($path)) return [];
    $json = file_get_contents($path);
    return json_decode($json, true) ?: [];
}

// Função para salvar preferências no JSON
function salvarPreferencias($path, $preferencias) {
    file_put_contents($path, json_encode($preferencias, JSON_PRETTY_PRINT));
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Carrega preferências do JSON
    $preferencias = lerPreferencias($preferenciasPath);

    $usuario = Usuario::buscarPorEmail($_SESSION['email']);
    $usuario_id = $usuario->getId(); // pega o id do usuário

    // Se enviou mudança de tema, aplica e salva no JSON
    if (isset($_POST['tema'])) {
        $temaEscolhido = $_POST['tema'] === 'dark' ? 1 : 0;
        $_SESSION['dark'] = $temaEscolhido;
        $preferencias[$_SESSION['email']]['cor'] = $temaEscolhido;
        salvarPreferencias($preferenciasPath, $preferencias);
        $tema_atual = $temaEscolhido;
        header("Location: perfil_user.php");
        exit;
    }

    // Caso não tenha POST de tema, carrega do JSON
    if (!isset($_POST['tema'])) {
        $tema_atual = isset($preferencias[$_SESSION['email']]['cor']) ? (int)$preferencias[$_SESSION['email']]['cor'] : 0;
        $_SESSION['dark'] = $tema_atual;
    }

    // Carrega pontuação
    $stmtPont = $pdo->prepare("SELECT pontuacao FROM usuario WHERE id = :id");
    $stmtPont->execute(['id' => $usuario_id]);
    $pontuacao = $stmtPont->fetchColumn();

    // Respostas dos quizzes
    $stmt_respostas = $pdo->prepare("
        SELECT r.quiz_id, r.acertos, r.tempo, q.tema, q.total_questoes
        FROM resposta_usuario r
        JOIN quiz q ON r.quiz_id = q.id
        WHERE r.usuario_id = :usuario_id
        ORDER BY r.data_resposta DESC
    ");
    $stmt_respostas->execute(['usuario_id' => $usuario_id]);
    $respostas = $stmt_respostas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}

// Alterar nome do usuário
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
<html lang="pt-br" class="<?= ($tema_atual ?? 0) === 1 ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 text-gray-900 dark:text-gray-100 min-h-screen">
    <div class="max-w-5xl mx-auto p-6">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-extrabold">Perfil do Usuário</h1>
            <div class="flex items-center gap-3">
                <form method="post" class="inline">
                    <select name="tema" onchange="this.form.submit()"
                            class="border rounded px-3 py-2 bg-white dark:bg-gray-800">
                        <option value="light" <?= ($tema_atual ?? 0) == 0 ? 'selected' : '' ?>>Claro</option>
                        <option value="dark" <?= ($tema_atual ?? 0) == 1 ? 'selected' : '' ?>>Escuro</option>
                    </select>
                </form>
                <a href="logout.php" class="inline-block bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow">Deslogar</a>
            </div>
        </header>
        <a href="inicio_user.php">Voltar</a>
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Perfil -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center gap-4">
                    <div class="h-20 w-20 rounded-full bg-gradient-to-tr from-indigo-500 to-pink-500 flex items-center justify-center text-white text-2xl font-bold">
                        <?= htmlspecialchars(substr($usuario->getNome(), 0, 1) ?: 'U') ?>
                    </div>
                    <div>
                        <div class="text-lg font-semibold"><?= htmlspecialchars($usuario->getNome()); ?></div>
                        <div class="text-sm text-gray-500 dark:text-gray-300"><?= htmlspecialchars($usuario->getEmail()); ?></div>
                    </div>
                </div>

                <div class="mt-6">
                    <button id="editarBtn" onclick="mostrarFormulario()" class="w-full text-left bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Editar nome</button>

                    <form id="formEditar" action="perfil_user.php" method="post" class="mt-4 hidden">
                        <input type="text" id="nome" name="nome" class="w-full border rounded px-3 py-2 mb-2 bg-white dark:bg-gray-700" value="<?= htmlspecialchars($usuario->getNome()); ?>" required>
                        <div class="flex gap-2">
                            <input type="submit" value="Salvar" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            <button type="button" onclick="fecharFormulario()" class="flex-1 bg-gray-300 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">Cancelar</button>
                        </div>
                    </form>

                    <?php if (isset($mensagem)): ?>
                        <p class="mt-3 text-sm text-green-600"><?= htmlspecialchars($mensagem) ?></p>
                    <?php endif; ?>
                    <?php if (isset($erro)): ?>
                        <p class="mt-3 text-sm text-red-600"><?= htmlspecialchars($erro) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col">
                    <div class="text-sm text-gray-500">Pontuação</div>
                    <div class="mt-2 text-2xl font-bold"><?= htmlspecialchars($pontuacao ?? 0) ?></div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col">
                    <div class="text-sm text-gray-500">Acertos Totais</div>
                    <div class="mt-2 text-2xl font-bold"><?= htmlspecialchars($totalAcertos ?? 0) ?></div>
                    <div class="text-xs text-gray-400 mt-1"><?= ($totalQuestoes ?? 0) ?> questões</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col">
                    <div class="text-sm text-gray-500">Tempo Total</div>
                    <div class="mt-2 text-2xl font-bold"><?= htmlspecialchars($totalTempo ?? 0) ?>s</div>
                </div>
            </div>
        </section>

        <section class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Resultados dos Quizzes Respondidos</h2>

            <?php if (!empty($respostas)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="text-left text-sm text-gray-500 dark:text-gray-300">
                                <th class="py-3 px-4">Quiz</th>
                                <th class="py-3 px-4">Acertos</th>
                                <th class="py-3 px-4">Tempo</th>
                                <th class="py-3 px-4">Data</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php foreach ($respostas as $r): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="py-3 px-4"><?= htmlspecialchars($r['tema']); ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($r['acertos'].'/'.$r['total_questoes']); ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($r['tempo']); ?>s</td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($r['data_resposta'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Você ainda não respondeu a nenhum quiz.</p>
            <?php endif; ?>
        </section>
    </div>

    <script>
        function mostrarFormulario() {
            document.getElementById('formEditar').classList.remove('hidden');
            document.getElementById('editarBtn').classList.add('hidden');
            document.getElementById('nome').focus();
        }
        function fecharFormulario() {
            document.getElementById('formEditar').classList.add('hidden');
            document.getElementById('editarBtn').classList.remove('hidden');
        }
    </script>

    <style>
        /* small fallback for browsers without Tailwind processing */
        .hidden { display: none; }
        @media (max-width: 768px) {
            .min-w-\[640px\] { min-width: 100%; }
        }
    </style>
</body>
</html>