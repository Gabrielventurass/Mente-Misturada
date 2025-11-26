<?php
session_start();
session_unset(); // remove variáveis da sessão
session_destroy(); // destrói a sessão
header("Location: login_adm.php");
exit;
?>
