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
    <script src="../js/javaS.js" defer></script>
</head>
<body class="bg-gradient-to-b from-gray-100 to-gray-50 min-h-screen">
    <header class="w-full bg-white/60 backdrop-blur sticky top-0 z-20 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="../img/cerebro.png" alt="Mente" class="h-10 w-10">
                <div>
                    <h1 class="text-xl font-extrabold text-gray-800">Mente Misturada</h1>
                    <p class="text-sm text-gray-500">Mantenha seu cérebro informado</p>
                </div>
            </div>

            <nav class="flex items-center gap-3">
                <a href="perfil_user.php" class="text-sm text-gray-700 hover:text-indigo-600 px-3 py-1 rounded-md">Perfil</a>
                <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 px-3 py-1 rounded-md">Sair</a>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-10">
        <section class="text-center mb-8">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Bem-vindo(a)</h2>
            <p class="mt-2 text-gray-600">Escolha uma modalidade e comece a treinar sua mente.</p>
        </section>

               <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <button onclick="diario()" class="group card-action btn-diario">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Diário</h3>
                        <p class="text-sm text-gray-50/90">Exercícios diários rápidos</p>
                    </div>
                    <img src="../img/diario.png" alt="Diário" class="h-12 w-12 opacity-90">
                </div>
            </button>

            <button onclick="vsTemp()" class="group card-action btn-vstemp">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Contra o Tempo</h3>
                        <p class="text-sm text-gray-50/90">Prove sua rapidez</p>
                    </div>
                    <img src="../img/ampulheta.png" alt="Contra o Tempo" class="h-12 w-12">
                </div>
            </button>

            <button onclick="comp()" class="group card-action btn-comp">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Dando seu Melhor</h3>
                        <p class="text-sm text-gray-50/90">Modo competitivo</p>
                    </div>
                    <img src="../img/taça.png" alt="Dando seu Melhor" class="h-12 w-12">
                </div>
            </button>

            <button onclick="casu()" class="group card-action btn-casu">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Casual</h3>
                        <p class="text-sm text-gray-50/90">Sem pressão, só diversão</p>
                    </div>
                    <img src="../img/taça.png" alt="Casual" class="h-12 w-12">
                </div>
            </button>

            <button onclick="tema()" class="group card-action btn-tema">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Escolha o Tema</h3>
                        <p class="text-sm text-gray-50/90">Personalize suas perguntas</p>
                    </div>
                    <img src="../img/livro.png" alt="Escolha o Tema" class="h-12 w-12">
                </div>
            </button>

            <button onclick="criar()" class="group card-action btn-criar">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Faça seu Quiz</h3>
                        <p class="text-sm text-gray-50/90">Crie e compartilhe com a comunidade</p>
                    </div>
                    <img src="../img/lapis.png" alt="Faça seu Quiz" class="h-12 w-12">
                </div>
            </button>

            <button onclick="comun()" class="group card-action btn-comun">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Quizzes da Comunidade</h3>
                        <p class="text-sm text-gray-50/90">Explore conteúdos criados por outros</p>
                    </div>
                    <img src="../img/comunidade.png" alt="Comunidade" class="h-12 w-12">
                </div>
            </button>

            <button onclick="offline()" class="group card-action btn-offline">
                <div class="flex items-center justify-between">
                    <div class="text-left">
                        <h3 class="font-semibold text-lg">Quizzes offline</h3>
                        <p class="text-sm text-gray-50/90">Baixe e jogue sem internet</p>
                    </div>
                    <img src="../img/comunidade.png" alt="Offline" class="h-12 w-12">
                </div>
            </button>
        </section>

        <section class="mt-10 bg-white rounded-lg shadow p-5">
            <h4 class="text-lg font-semibold text-gray-800">Dicas rápidas</h4>
            <ul class="mt-3 list-disc list-inside text-gray-600 space-y-2">
                <li>Use o modo Diário para criar uma rotina.</li>
                <li>Pratique no modo Contra o Tempo para melhorar reflexos.</li>
                <li>Crie quizzes para a comunidade e ganhe feedback.</li>
            </ul>
        </section>
    </main>

    <footer class="mt-12 py-6 bg-white/60 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-600">
            <p>Mente Misturada é um projeto que mistura a Wikipédia com jogos diários. <a href="saiba.html" class="text-blue-600 hover:underline">Saiba mais</a></p>
            <p class="mt-1"><a href="termos.html" class="text-blue-600 hover:underline">Termos de uso</a> | <a href="politica.html" class="text-blue-600 hover:underline">Política de privacidade</a></p>
            <p class="mt-2">Dúvidas ou sugestões? thiagoesoaresrsl@gmail.com</p>
        </div>
    </footer>

       <style>
        /* small helper for card buttons */
        .card-action {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(15,23,42,0.06);
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
            cursor: pointer;
            color: #0f172a;
        }
        .card-action:focus,
        .card-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(15,23,42,0.12);
            outline: none;
        }

        /* Colored variants */
        .btn-diario {
            background: linear-gradient(90deg,#06b6d4,#06b6d4cc);
            color: white;
        }
        .btn-vstemp {
            background: linear-gradient(90deg,#7c3aed,#5b21b6);
            color: white;
        }
        .btn-comp {
            background: linear-gradient(90deg,#f59e0b,#f97316);
            color: white;
        }
        .btn-casu {
            background: linear-gradient(90deg,#ec4899,#f472b6);
            color: white;
        }
        .btn-tema {
            background: linear-gradient(90deg,#ef4444,#dc2626);
            color: white;
        }
        .btn-criar {
            background: linear-gradient(90deg,#3b82f6,#2563eb);
            color: white;
        }
        .btn-comun {
            background: linear-gradient(90deg,#10b981,#059669);
            color: white;
        }
        .btn-offline {
            background: linear-gradient(90deg,#64748b,#475569);
            color: white;
        }

        /* icon contrast */
        .card-action img { filter: brightness(0) invert(1) opacity(.95); }

        /* small responsive tweaks */
        @media (max-width:640px) {
            .card-action { padding: .75rem; }
            .card-action img { height:44px; width:44px; }
        }
    </style>
</body>
</html>