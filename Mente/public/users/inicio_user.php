<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login_user.php");
    exit();
}
include('menu_user.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Home - Mente Misturada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="../img/cerebro.png" type="image/x-icon">
    <script src="../js/javaS.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-800">Mente Misturada</h1>
        <p class="text-gray-600">Mantenha seu cérebro informado!</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-3xl">
        <button onclick="diario()" class="flex items-center justify-between bg-indigo-500 hover:bg-indigo-600 text-white p-4 rounded shadow">
            <span>Diário</span>
            <img src="../img/diario.png" alt="Diário" class="h-10">
        </button>

        <button onclick="vsTemp()" class="flex items-center justify-between bg-purple-500 hover:bg-purple-600 text-white p-4 rounded shadow">
            <span>Contra o Tempo</span>
            <img src="../img/ampulheta.png" alt="Contra o Tempo" class="h-10">
        </button>

        <button onclick="comp()" class="flex items-center justify-between bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded shadow">
            <span>Dando seu Melhor</span>
            <img src="../img/taça.png" alt="Dando seu Melhor" class="h-12">
        </button>

        <button onclick="casu()" class="flex items-center justify-between bg-pink-500 hover:bg-pink-600 text-white p-4 rounded shadow">
            <span>Casual</span>
            <img src="../img/taça.png" alt="Casual" class="h-12">
        </button>

        <button onclick="tema()" class="flex items-center justify-between bg-red-500 hover:bg-red-600 text-white p-4 rounded shadow">
            <span>Escolha o Tema</span>
            <img src="../img/livro.png" alt="Escolha o Tema" class="h-10">
        </button>

        <button onclick="criar()" class="flex items-center justify-between bg-blue-500 hover:bg-blue-600 text-white p-4 rounded shadow">
            <span>Faça seu Quiz</span>
            <img src="../img/lapis.png" alt="Faça seu Quiz" class="h-10">
        </button>

        <button onclick="comun()" class="flex items-center justify-between bg-green-500 hover:bg-green-600 text-white p-4 rounded shadow md:col-span-2">
            <span>Quizzes da Comunidade</span>
            <img src="../img/comunidade.png" alt="Quizzes da Comunidade" class="h-10">
        </button>
    </div>

    <footer class="mt-10 text-center text-sm text-gray-600 space-y-1">
        <p>Mente Misturada é um projeto que mistura a Wikipédia com jogos diários. <a href="saiba.html" class="text-blue-600 hover:underline">Saiba mais</a></p>
        <p><a href="termos.html" class="text-blue-600 hover:underline">Termos de uso</a> | <a href="politica.html" class="text-blue-600 hover:underline">Política de privacidade</a></p>
        <p>Dúvidas ou sugestões? contato@gmail.com</p>
    </footer>
</body>
</html>
