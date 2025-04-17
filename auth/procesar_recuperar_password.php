<!-- filepath: c:\Users\juana\Desktop\semestre 7\Inge software\el_agreval\auth\procesar_recuperar_password.php -->
<?php
require_once '../config/config.php';
require '../vendor/autoload.php'; // Asegúrate de incluir PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM EMPLEADOS WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $correo, $token, $expires_at);
        $stmt->execute();

        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Cambia esto por tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'tu_correo@example.com'; // Tu correo
            $mail->Password = 'tu_contraseña'; // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tu_correo@example.com', 'El Agreval');
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: 
                <a href='http://tu-dominio.com/auth/restablecer_password.php?token=$token'>Restablecer Contraseña</a>";
            $mail->send();

            header("Location: ../recuperar_password.php?mensaje=Se ha enviado un enlace de recuperación a tu correo.");
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        header("Location: ../recuperar_password.php?mensaje=El correo no está registrado.");
    }

    $stmt->close();
    $conn->close();
}
?>