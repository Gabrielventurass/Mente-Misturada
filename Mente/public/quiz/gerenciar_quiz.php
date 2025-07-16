<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Quizzes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">
  <div class="w-full max-w-4xl bg-white shadow rounded p-6">
    <a href="../admin/inicio_adm.php" class="inline-flex items-center text-blue-600 hover:underline mb-4">
      <img src="../img/seta.png" class="h-5 mr-2"> Voltar
    </a>
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Quizzes Cadastrados</h1>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
      <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
        Quiz excluído com sucesso.
      </div>
    <?php endif; ?>

    <?php if (count($quizzes) > 0): ?>
      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Tema</th>
              <th class="px-4 py-2 text-left">Total de Questões</th>
              <th class="px-4 py-2 text-left">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($quizzes as $quiz): ?>
              <tr class="border-t">
                <td class="px-4 py-2"><?= $quiz['id'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($quiz['tema']) ?></td>
                <td class="px-4 py-2"><?= $quiz['total_questoes'] ?></td>
                <td class="px-4 py-2 space-x-2">
                  <a href="editar_quiz.php?id=<?= $quiz['id'] ?>" class="bg-green-600 hover:bg-green-700 text-white_
