<?php
// visualizar_quiz.php — versão corrigida e híbrida (SQL + JSON)

// receber id (aceita ?id= ou ?editar=)
$id = $_GET['id'] ?? $_GET['editar'] ?? null;
if (!$id) {
    die("<h2 style='color:white;text-align:center;margin-top:20%'>❌ ID do quiz não informado.</h2>");
}

// normalize
$id = trim(rawurldecode($id));

// -------------------------------------------------
// 1) conectar ao banco (se der erro, segue só com JSON)
// -------------------------------------------------
$pdo = null;
try {
    $dsn = "mysql:host=localhost;dbname=mente;charset=utf8mb4";
    $dbUser = "root";
    $dbPass = "";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // não morre aqui — continuamos tentando JSON
    $pdo = null;
}

// -------------------------------------------------
// helper: carregar do SQL
// -------------------------------------------------
function carregarQuizSQL(PDO $pdo, $id) {
    // só tenta se id for inteiro (evita procurar string que não existe)
    if (!ctype_digit((string)$id)) return null;

    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id = ?");
    $stmt->execute([(int)$id]);
    $quiz = $stmt->fetch();
    if (!$quiz) return null;

    $quizData = [
        "id" => $quiz["id"],
        "titulo" => $quiz["titulo"] ?? '',
        "descricao" => $quiz["descricao"] ?? '',
        "tema" => $quiz["tema"] ?? '',
        "perguntas" => []
    ];

    // carregar perguntas
    $stmtP = $pdo->prepare("SELECT * FROM pergunta WHERE quiz_id = ? ORDER BY id ASC");
    $stmtP->execute([(int)$id]);
    $perguntas = $stmtP->fetchAll();

    foreach ($perguntas as $p) {
        $stmtA = $pdo->prepare("SELECT * FROM alternativa WHERE pergunta_id = ? ORDER BY id ASC");
        $stmtA->execute([$p['id']]);
        $alts = $stmtA->fetchAll();

        $alternativas = [];
        foreach ($alts as $a) {
            $alternativas[] = [
                'texto' => $a['texto'] ?? '',
                'correta' => !empty($a['correta']) ? true : false
            ];
        }

        $quizData['perguntas'][] = [
            'texto' => $p['texto'] ?? '',
            'alternativas' => $alternativas
        ];
    }

    return $quizData;
}

// -------------------------------------------------
// helper: carregar do JSON
// -------------------------------------------------
function carregarQuizJSONById($id) {
    $dir = __DIR__ . '/quizzes';
    $arquivo = $dir . "/$id.json";
    if (file_exists($arquivo)) {
        $json = json_decode(file_get_contents($arquivo), true);
        return $json ?: null;
    }
    return null;
}

// -------------------------------------------------
// 2) tentar SQL primeiro (se puder) — senão JSON
// -------------------------------------------------
$quiz = null;

// se parece número, tentar SQL primeiro
if ($pdo && ctype_digit((string)$id)) {
    $quiz = carregarQuizSQL($pdo, $id);
}

// se não encontrou no SQL (ou id não-numérico), tentar JSON
if (!$quiz) {
    $quiz = carregarQuizJSONById($id);
}

// -------------------------------------------------
// 3) se ainda não encontrou, tentar buscar um match flexível
//    (caso você passe id numérico mas o arquivo JSON exista com outro nome)
// -------------------------------------------------
$arquivo_encontrado = null;
$dir = __DIR__ . '/quizzes';
if (!$quiz) {
    // tentativa: buscar arquivo cujo nome case-insensitive corresponda ao id
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') as $f) {
            $name = pathinfo($f, PATHINFO_FILENAME);
            if (strcasecmp($name, $id) === 0) {
                $arquivo_encontrado = $f;
                break;
            }
        }
    }
    // se achou arquivo via busca, carrega
    if ($arquivo_encontrado) {
        $quiz = json_decode(file_get_contents($arquivo_encontrado), true) ?: null;
    }
}

// -------------------------------------------------
// 4) se ainda não há quiz -> mostrar mensagem amigável com exemplos
// -------------------------------------------------
if (!$quiz) {
    // lista alguns arquivos disponíveis (se houver)
    $lista = [];
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') as $f) {
            $lista[] = pathinfo($f, PATHINFO_FILENAME);
            if (count($lista) >= 50) break;
        }
    }

    $exemplos = $lista ? htmlspecialchars(implode(', ', $lista)) : 'nenhum arquivo JSON encontrado';
    die("<div style='color:white;text-align:center;margin-top:5%'>
            <h2>❌ Quiz não encontrado.</h2>
            <p>ID procurado: <b>" . htmlspecialchars($id) . "</b></p>
            <p>Arquivos disponíveis (exemplos): <small>" . $exemplos . "</small></p>
            <p style='opacity:.8'>Se o quiz estiver no banco, verifique se você passou o ID numérico correto.</p>
         </div>");
}

// -------------------------------------------------
// 5) normalizar formato (garantir chaves que usamos depois)
// -------------------------------------------------
$quiz['titulo'] = $quiz['titulo'] ?? '';
$quiz['descricao'] = $quiz['descricao'] ?? '';
$quiz['tema'] = $quiz['tema'] ?? '';
$quiz['perguntas'] = $quiz['perguntas'] ?? [];

// helper para normalizar perguntas (reaproveita a versão anterior)
function normalizarPergunta(array $p): array {
    $texto = $p['pergunta'] ?? $p['texto'] ?? $p['question'] ?? '';

    $opcoes = [];
    $corretas = [];

    if (isset($p['opcoes']) && is_array($p['opcoes'])) {
        $opcoes = $p['opcoes'];
        if (isset($p['correta']) && (is_int($p['correta']) || is_numeric($p['correta']))) {
            $ci = (int)$p['correta'];
            foreach ($opcoes as $i => $_) $corretas[$i] = ($i === $ci);
        } else {
            foreach ($opcoes as $i => $_) $corretas[$i] = false;
        }
        return ['texto' => $texto, 'opcoes' => $opcoes, 'corretas' => $corretas];
    }

    if (isset($p['alternativas']) && is_array($p['alternativas'])) {
        foreach ($p['alternativas'] as $alt) {
            if (is_array($alt)) {
                $op = $alt['texto'] ?? ($alt[0] ?? '');
                $opcoes[] = $op;
                $corretas[] = !empty($alt['correta']);
            } else {
                $opcoes[] = (string)$alt;
                $corretas[] = false;
            }
        }
        return ['texto' => $texto, 'opcoes' => $opcoes, 'corretas' => $corretas];
    }

    foreach (['opcoes', 'alternativas', 'choices', 'options'] as $k) {
        if (isset($p[$k]) && is_array($p[$k])) {
            foreach ($p[$k] as $alt) {
                if (is_array($alt)) {
                    $opcoes[] = $alt['texto'] ?? ($alt[0] ?? '');
                    $corretas[] = !empty($alt['correta']);
                } else {
                    $opcoes[] = (string)$alt;
                    $corretas[] = false;
                }
            }
            return ['texto' => $texto, 'opcoes' => $opcoes, 'corretas' => $corretas];
        }
    }

    return ['texto' => $texto, 'opcoes' => [], 'corretas' => []];
}

// -------------------------------------------------
// 6) render HTML (igual ao seu original)
// -------------------------------------------------
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Visualizar Quiz - <?= htmlspecialchars($quiz['titulo'] ?? 'Quiz') ?></title>
  <link rel="stylesheet" href="../../public/css/style_admin.css">
</head>
<body>
  <main>
    <div class="card" style="max-width:900px;margin:32px auto;padding:20px">
      <h1 class="title"><?= htmlspecialchars($quiz['titulo'] ?? 'Sem título') ?></h1>
      <p class="muted" style="margin:6px 0"><b>Tema:</b> <?= htmlspecialchars($quiz['tema'] ?? 'Não especificado') ?></p>
      <p class="muted" style="margin:6px 0 14px 0"><b>Descrição:</b> <?= htmlspecialchars($quiz['descricao'] ?? 'Sem descrição') ?></p>

      <h2 style="margin-top:12px;margin-bottom:8px">Perguntas:</h2>
      <div style="display:flex;flex-direction:column;gap:12px">
      <?php
        $perguntas = $quiz['perguntas'] ?? [];
        if (empty($perguntas)): ?>
          <p class="text-gray-400">Nenhuma pergunta cadastrada ainda.</p>
      <?php else:
          foreach ($perguntas as $i => $p):
              $norm = normalizarPergunta((array)$p);
              $texto = $norm['texto'];
              $opcoes = $norm['opcoes'];
              $corretas = $norm['corretas'];
      ?>
        <div class="bg-gray-800 p-4 rounded">
          <p class="font-semibold text-lg mb-2"><?= ($i+1) . ". " . htmlspecialchars($texto ?: '(sem texto)') ?></p>

          <?php if (empty($opcoes)): ?>
            <p class="text-gray-400">Sem alternativas para esta pergunta.</p>
          <?php else: ?>
            <ul class="list-disc ml-6 text-gray-300">
              <?php foreach ($opcoes as $idx => $opc):
                      $isCorrect = isset($corretas[$idx]) && $corretas[$idx];
              ?>
                <li class="<?= $isCorrect ? 'correct' : '' ?>">
                  <?= htmlspecialchars($opc) ?> <?= $isCorrect ? "✅" : "" ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php
          endforeach;
        endif;
      ?>
    </div>

    <div class="mt-8 flex gap-2">
      <a href="../admin/gerenciar_quizzes.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">⬅️ Voltar</a>
    </div>
  </div>
</body>
</html>
