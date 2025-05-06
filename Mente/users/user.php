<?php
require_once("../class/user.class.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $acao = $_POST['acao'] ?? '';

    $usuario = new Usuario($nome, $email, $senha);

    if ($acao == 'salvar') {
        $resultado = $usuario->inserir();
    
        if ($resultado) {
            header("Location: login.php?sucesso=1");
        } else {
            $erroCadastro = $usuario->getErro();
        }
    }
    
}
?>
