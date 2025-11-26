<?php
$mensagem = "";

// Conexão com banco
$pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Quando o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Dados do quiz
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $tema = $_POST['tema'] ?? '';
    $usar_json = isset($_POST['usar_json']); // checkbox opcional

    $perguntas = $_POST['perguntas'] ?? [];

    // 1️⃣ Salva no banco
    $stmt = $pdo->prepare("INSERT INTO quiz (titulo, descricao, tema, origem) VALUES (?, ?, ?, ?)");
    $origem = $usar_json ? 'json' : 'sql';
    $stmt->execute([$titulo, $descricao, $tema, $origem]);
    $quiz_id = $pdo->lastInsertId();
// 2️⃣ Salva perguntas e alternativas no MySQL
foreach ($perguntas as $p) {

    // Inserir pergunta
    $stmt = $pdo->prepare("INSERT INTO pergunta (quiz_id, texto, resposta_correta) VALUES (?, ?, ?)");
    
    // Descobre qual alternativa é correta
    $respCorreta = "";
    if (!empty($p['alternativas'])) {
        foreach ($p['alternativas'] as $alt) {
            if (isset($alt['correta'])) {
                $respCorreta = $alt['texto']; // salva só o texto da correta
            }
        }
    }

    $stmt->execute([$quiz_id, $p['texto'], $respCorreta]);
    $pergunta_id = $pdo->lastInsertId();

    // Inserir alternativas
    if (!empty($p['alternativas'])) {
        foreach ($p['alternativas'] as $alt) {
            $textoAlt = $alt['texto'] ?? '';
            $isCorrect = isset($alt['correta']) ? 1 : 0;

            $stmtAlt = $pdo->prepare("INSERT INTO alternativa (pergunta_id, texto, correta) VALUES (?, ?, ?)");
            $stmtAlt->execute([$pergunta_id, $textoAlt, $isCorrect]);
        }
    }
}

    // 2️⃣ Salva no JSON se marcou a opção (cada quiz em arquivo separado)
    if ($usar_json) {
        $quizzesDir = __DIR__ . '/quizzes';
        if (!is_dir($quizzesDir)) {
            mkdir($quizzesDir, 0755, true);
        }

        $novo_quiz = [
            'id' => $quiz_id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'tema' => $tema,
            'perguntas' => []
        ];

        // Adiciona perguntas
        foreach ($perguntas as $p) {
            $pergunta = [
                'texto' => $p['texto'] ?? '',
                'alternativas' => []
            ];

            if (!empty($p['alternativas'])) {
                foreach ($p['alternativas'] as $alt) {
                    $pergunta['alternativas'][] = [
                        'texto' => $alt['texto'] ?? '',
                        'correta' => isset($alt['correta'])
                    ];
                }
            }

            $novo_quiz['perguntas'][] = $pergunta;
        }

        // Gera nome de ficheiro seguro
        $safeTitle = mb_strtolower($titulo, 'UTF-8');
        $safeTitle = preg_replace('/[^a-z0-9\-]+/i', '_', $safeTitle);
        $filename = sprintf('%s/quiz_%s.json', $quizzesDir, $quiz_id);

        // Evita sobrescrever (apende timestamp se já existir)
        if (file_exists($filename)) {
            $filename = sprintf('%s/quiz_%s_%s.json', $quizzesDir, $quiz_id, time());
        }

        file_put_contents($filename, json_encode($novo_quiz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $mensagem = "✅ Quiz salvo com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Criar Quiz</title>
  <link rel="stylesheet" href="../../public/css/style_admin.css">
</head>

<body>
<?php $h2 = "Criação de quiz"; include 'menu_quiz.php';?>

  <main>
    <h1 class="title" style="margin:18px 0 8px 0">🧠 Criar Novo Quiz</h1>

    <div class="card">
      <?php if ($mensagem): ?>
        <div class="msg"><?= $mensagem ?></div>
      <?php endif; ?>

      <div style="margin-bottom:12px">
        <a href="../admin/gerenciar_quizzes.php" class="btn btn-secondary">← Voltar</a>
      </div>

      <form method="POST" class="form-wrap">
        <div>
          <label for="titulo">Título:</label>
          <input id="titulo" type="text" name="titulo" required>
        </div>

        <div>
          <label for="descricao">Descrição:</label>
          <textarea id="descricao" name="descricao" required></textarea>
        </div>

        <div>
          <label for="tema">Tema:</label>
          <input id="tema" type="text" name="tema" required>
        </div>

        <div id="perguntasContainer"></div>

        <div>
          <button type="button" id="addPergunta" class="btn btn-primary">+ Adicionar Pergunta</button>
        </div>

        <div>
          <label><input type="checkbox" name="usar_json"> Salvar também em JSON</label>
        </div>

        <div class="row" style="margin-top:8px">
          <button type="submit" class="btn btn-primary">💾 Salvar Quiz</button>
        </div>
      </form>
    </div>
  </main>

  <script>
    let perguntasContainer = document.getElementById("perguntasContainer");
    let addPerguntaBtn = document.getElementById("addPergunta");

    addPerguntaBtn.addEventListener("click", () => {
      let index = document.querySelectorAll(".pergunta").length;
      let div = document.createElement("div");
      div.className = "pergunta card";
      div.innerHTML = `
        <label for="pergunta_${index}">Pergunta ${index + 1}:</label>
        <input id="pergunta_${index}" type="text" name="perguntas[${index}][texto]" required>

        <div class="alternativas">
          ${[0,1,2,3].map(i => `
            <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
              <input type="text" name="perguntas[${index}][alternativas][${i}][texto]" placeholder="Alternativa ${i + 1}" required>
              <label style="font-size:13px"><input type="checkbox" name="perguntas[${index}][alternativas][${i}][correta]"> Correta</label>
            </div>
          `).join('')}
        </div>
      `;
      perguntasContainer.appendChild(div);
    });
  </script>

</body>
</html>
