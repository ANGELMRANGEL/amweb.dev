<?php
header('Content-Type: application/json');

$name = isset($_POST['sender_name']) ? trim($_POST['sender_name']) : '';
$email = isset($_POST['sender_email']) ? trim($_POST['sender_email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos.']);
    exit;
}

$to = 'hola@amweb.dev';
$email_subject = "Contacto Dock: " . $subject;

$email_content = "Nombre: $name\n";
$email_content .= "Email: $email\n\n";
$email_content .= "Mensaje:\n$message\n";

$headers = "From: webmaster@amweb.dev\r\n";
$headers .= "Reply-To: $email\r\n";

if (mail($to, $email_subject, $email_content, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al enviar el correo desde el servidor.']);
}
?>
