<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Garante sessão ativa
}

// Verificar se o usuário está logado
if (!isset($_SESSION['email'])) {
    header('Location: login_user.php');
    exit;
}
?>

<div class="flex items-center justify-between bg-white shadow p-4 mb-6">
  <div>
    <a href="../users/inicio_user.php" class="inline-flex items-center text-blue-600 hover:underline">
      <img src="../img/casa.png" alt="Início" class="h-6 mr-2"> Início
    </a>
  </div>
  <h1 class="text-sm text-gray-700">
    Usuário logado: <span class="font-semibold"><?= htmlspecialchars($_SESSION['nome']) ?></span>
  </h1>
</div>
