<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailerHelper {

    /**
     * Envía un correo con el certificado PDF adjunto usando PHPMailer.
     */
    public static function enviarCertificado(
        string $destinatarioEmail, 
        string $destinatarioNombre, 
        string $eventoTitulo, 
        string $pdfBinaryContent
    ): bool {
        $mail = new PHPMailer(true);

        try {
            // ----------------------------------------------------
            // CONFIGURACIÓN DEL SERVIDOR SMTP
            // ----------------------------------------------------
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';             // Servidor SMTP (Ejemplo: Gmail / Google Workspace)
            $mail->SMTPAuth   = true;
            $mail->Username   = 'certificados@evenrity.com';  // Tu cuenta emisora
            $mail->Password   = 'xxxx xxxx xxxx xxxx';         // Contraseña de Aplicación de Google de 16 caracteres
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O PHPMailer::ENCRYPTION_SMTPS si usas puerto 465
            $mail->Port       = 587;                          // 587 para TLS / 465 para SSL
            $mail->CharSet    = 'UTF-8';

            // ----------------------------------------------------
            // REMITENTE Y DESTINATARIO
            // ----------------------------------------------------
            $mail->setFrom('certificados@evenrity.com', 'Evenrity - Certificados');
            $mail->addAddress($destinatarioEmail, $destinatarioNombre);

            // ----------------------------------------------------
            // ADJUNTAR CERTIFICADO PDF
            // ----------------------------------------------------
            $nombreArchivoPdf = 'Certificado_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $destinatarioNombre) . '.pdf';
            $mail->addStringAttachment($pdfBinaryContent, $nombreArchivoPdf, 'base64', 'application/pdf');

            // ----------------------------------------------------
            // CONTENIDO DEL CORREO
            // ----------------------------------------------------
            $mail->isHTML(true);
            $mail->Subject = 'Tu Certificado de Participación - ' . $eventoTitulo;
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;'>
                    <h2 style='color: #1a365d; text-align: center;'>¡Felicitaciones, {$destinatarioNombre}!</h2>
                    <p>Estimado/a participante,</p>
                    <p>Nos complace adjuntar tu certificado digital de participación para el evento: <strong>{$eventoTitulo}</strong>.</p>
                    <p>Agradecemos tu valiosa asistencia y compromiso.</p>
                    <br>
                    <p style='font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px;'>
                        Atentamente,<br>
                        <strong>Equipo de Evenrity</strong><br>
                        <a href='mailto:certificados@evenrity.com' style='color: #2563eb;'>certificados@evenrity.com</a>
                    </p>
                </div>
            ";
            $mail->AltBody = "Estimado/a {$destinatarioNombre}, adjunto encontrarás tu certificado digital de participación para el evento {$eventoTitulo}. Gracias por tu asistencia. Atentamente, Evenrity.";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo PHPMailer: " . $mail->ErrorInfo);
            return false;
        }
    }
}