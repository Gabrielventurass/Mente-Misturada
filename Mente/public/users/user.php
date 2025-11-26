<?php

declare(strict_types=1);

require_once '../class/user.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $acao = $_POST['acao'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erroCadastro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erroCadastro = "E-mail inválido.";
    } else {
        $usuario = new Usuario($nome, $email, $senha);

        if ($acao === 'salvar') {
            try {
                $resultado = $usuario->inserir();

                if ($resultado) {
                    header('Location: login.php?sucesso=1');
                    exit;
                } else {
                    $erroCadastro = $usuario->getErro() ?: "Erro ao cadastrar usuário.";
                }
            } catch (PDOException $e) {
                $erroCadastro = "Erro no banco de dados: " . $e->getMessage();
            }
        }
    }
}
