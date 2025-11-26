<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

function enviarEmailRecuperacao(string $email, string $token): void {
    $mail = new PHPMailer(true);
    try {
        $link = "http://teusite.com/admin/redefinir_senha.php?token=" . urlencode($token);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'thiagoesoaresrsl@gmail.com';
        $mail->Password = '123456'; // senha de app, não a senha da conta
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('teuemail@gmail.com', 'Sistema Mente Misturada');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperação de senha - Mente Misturada';
        $mail->Body = "Clique no link abaixo para redefinir sua senha:<br>
                       <a href='$link'>$link</a><br><br>
                       Este link é válido por 1 hora.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de recuperação: " . $mail->ErrorInfo);
    }
}
?>
