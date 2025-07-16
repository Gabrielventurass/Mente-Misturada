<div class="bg-white shadow flex items-center justify-between p-4 mb-6">
  <div class="flex space-x-4">
    <a href="inicio_user.php" class="hover:scale-105 transition">
      <img src="../img/casa.png" alt="Início" class="h-8 w-8">
    </a>
    <a href="perfil_user.php" class="hover:scale-105 transition">
      <img src="../img/perfil.png" alt="Perfil" class="h-8 w-8">
    </a>
  </div>
  <h1 class="text-sm text-gray-700">
    Usuário logado: <span class="font-semibold"><?= htmlspecialchars($_SESSION['nome']) ?></span>
  </h1>
</div>
