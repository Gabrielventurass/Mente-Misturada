<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Você precisa estar logado para responder quizzes.";
    exit;
}

$email = $_SESSION['email'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca quizzes e conta de comentários
    $stmt = $pdo->prepare("
      SELECT q.*, 
        (SELECT COUNT(*) FROM comentario c WHERE c.quiz_id = q.id) AS comentarios_count
      FROM quizzes_user q
      ORDER BY q.id DESC
    ");
    $stmt->execute();
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Quizzes da Comunidade</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white shadow-md rounded p-6 w-full max-w-3xl">
        <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">Quizzes da Comunidade</h1>
        <p class="mb-6 text-center text-gray-600">Escolha um quiz para responder:</p>
        <a href="inicio_user.php">Voltar</a>
        <?php if (count($quizzes) > 0): ?>
            <ul class="space-y-3">
                <?php foreach ($quizzes as $quiz): ?>
                    <li class="flex items-center justify-between bg-gray-50 p-3 rounded">
                        <div>
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($quiz['titulo']) ?></div>
                            <div class="text-sm text-gray-500">Tema: <?= htmlspecialchars($quiz['tema']) ?></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="responder_quiz.php?quiz_id=<?= $quiz['id'] ?>" 
                               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-3 rounded">
                                Responder
                            </a>

                            <button
                              class="ver-comentarios-btn bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 rounded"
                              data-quiz-id="<?= $quiz['id'] ?>"
                              data-quiz-titulo="<?= htmlspecialchars($quiz['titulo'], ENT_QUOTES) ?>">
                                Ver comentários (<?= $quiz['comentarios_count'] ?>)
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 text-center">Não há quizzes disponíveis no momento.</p>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="comentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50"></div>
      <div class="bg-white w-full max-w-2xl rounded shadow-lg z-10 overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
          <h2 id="modalTitulo" class="text-lg font-semibold"></h2>
          <button id="fecharModal" class="text-gray-600 hover:text-gray-900">&times;</button>
        </div>

        <div class="p-4 space-y-4">
          <div id="comentList" class="max-h-64 overflow-auto space-y-2">
            <!-- comments will be injected here -->
            <p class="text-gray-500">Carregando comentários...</p>
          </div>

          <form id="comentForm" class="space-y-2">
            <textarea id="comentText" name="coment" rows="3" placeholder="Escreva seu comentário..." required
                      class="w-full border rounded p-2"></textarea>
            <input type="hidden" id="comentQuizId" name="quiz_id" value="">
            <div class="flex justify-end gap-2">
              <button type="button" id="btnFechar" class="px-4 py-2 rounded border">Fechar</button>
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enviar comentário</button>
            </div>
          </form>
        </div>
      </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('comentModal');
  const modalTitulo = document.getElementById('modalTitulo');
  const fecharModal = document.getElementById('fecharModal');
  const btnFechar = document.getElementById('btnFechar');
  const comentList = document.getElementById('comentList');
  const comentForm = document.getElementById('comentForm');
  const comentQuizId = document.getElementById('comentQuizId');
  const comentText = document.getElementById('comentText');

  function openModal(quizId, titulo) {
    comentQuizId.value = quizId;
    modalTitulo.textContent = "Comentários — " + titulo;
    modal.classList.remove('hidden');
    loadComments(quizId);
  }

  function closeModal() {
    modal.classList.add('hidden');
    comentList.innerHTML = '';
    comentText.value = '';
  }

  function sanitizeText(text) {
    // cria textNode para evitar XSS
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }

  async function loadComments(quizId) {
    comentList.innerHTML = '<p class="text-gray-500">Carregando comentários...</p>';
    try {
      const resp = await fetch('comments_fetch.php?quiz_id=' + encodeURIComponent(quizId), {
        credentials: 'same-origin'
      });
      if (!resp.ok) throw new Error('Erro ao carregar comentários');
      const data = await resp.json();

      if (!Array.isArray(data)) {
        comentList.innerHTML = '<p class="text-red-500">Erro no formato dos dados.</p>';
        return;
      }

      if (data.length === 0) {
        comentList.innerHTML = '<p class="text-gray-500">Nenhum comentário ainda. Seja o primeiro!</p>';
        return;
      }

      comentList.innerHTML = '';
      data.forEach(c => {
        const el = document.createElement('div');
        el.className = 'p-2 border rounded';
        const nome = c.nome ? sanitizeText(c.nome) : 'Usuário';
        const hora = c.criado_em ? sanitizeText(c.criado_em) : '';
        const texto = sanitizeText(c.coment);
        el.innerHTML = `<div class="text-sm font-semibold">${nome} <span class="text-xs text-gray-500">• ${hora}</span></div>
                        <div class="text-sm text-gray-700 mt-1">${texto}</div>`;
        comentList.appendChild(el);
      });

    } catch (err) {
      comentList.innerHTML = '<p class="text-red-500">Erro ao carregar comentários.</p>';
      console.error(err);
    }
  }

  // delegação de eventos para os botões "Ver comentários"
  document.body.addEventListener('click', function(e) {
    const btn = e.target.closest('.ver-comentarios-btn');
    if (btn) {
      const quizId = btn.dataset.quizId;
      const titulo = btn.dataset.quizTitulo || 'Quiz';
      openModal(quizId, titulo);
    }
  });

  fecharModal.addEventListener('click', closeModal);
  btnFechar.addEventListener('click', closeModal);
  window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
  });

  comentForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const quizId = comentQuizId.value;
    const coment = comentText.value.trim();
    if (!coment) return;

    const fd = new FormData();
    fd.append('quiz_id', quizId);
    fd.append('coment', coment);

    try {
      const resp = await fetch('comments_save.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const result = await resp.json();
      if (resp.ok && result.success) {
        // adiciona novo comentário no topo
        const c = result.comment;
        const el = document.createElement('div');
        el.className = 'p-2 border rounded';
        const nome = c.nome ? sanitizeText(c.nome) : 'Você';
        const hora = c.criado_em ? sanitizeText(c.criado_em) : '';
        const texto = sanitizeText(c.coment);
        el.innerHTML = `<div class="text-sm font-semibold">${nome} <span class="text-xs text-gray-500">• ${hora}</span></div>
                        <div class="text-sm text-gray-700 mt-1">${texto}</div>`;
        // insere no topo
        if (comentList.querySelector('p') && comentList.querySelector('p').textContent.includes('Nenhum comentário')) {
          comentList.innerHTML = '';
        }
        comentList.prepend(el);
        comentText.value = '';
      } else {
        alert(result.message || 'Erro ao enviar comentário');
      }
    } catch (err) {
      console.error(err);
      alert('Erro ao enviar comentário');
    }
  });

});
</script>
</body>
</html>
