<?php

declare(strict_types=1);

session_start();

// Destroi todas as variáveis da sessão
$_SESSION = [];

// Opcional: destruir cookie de sessão
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Finaliza a sessão
session_destroy();

// Redireciona para login
header('Location: login.php');
exit;
