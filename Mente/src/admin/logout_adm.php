<?php

declare(strict_types=1);

session_start();

// Destroi todas as variáveis da sessão
$_SESSION = [];

// Destruir o cookie de sessão (opcional mas recomendado)
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

// Finalmente, destrói a sessão
session_destroy();

// Redireciona para o login
header('Location: login_adm.php');
exit;
