<?php
require_once("../class/adm.class.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $acao = $_POST['acao'] ?? '';

    $admin = new admin($codigo,$nome, $email, $senha);

    if ($acao == 'salvar') {
        $resultado = $admin->inserir();
    
        if ($resultado) {
            header("Location: login_adm.php?sucesso=1");
        } else {
            $erroCadastro = $admin->getErro();
        }
    }
    
}
?>
