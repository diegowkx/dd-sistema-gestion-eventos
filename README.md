# Sistema de Gestión de Eventos

# Guía de Configuración: Módulo de Certificados y Envío Automatizado por Correo

Esta guía explica detalladamente los pasos necesarios para habilitar el envío automático (tanto individual como masivo) de certificados por correo electrónico utilizando la cuenta corporativa: `certificados@evenrity.com`.

---

## 📋 Requisitos Previos y Pasos de Configuración

### 1. Instalación de PHPMailer vía Composer

Para que el sistema pueda conectarse al servidor SMTP de correo, asegúrese de tener instalada la librería **PHPMailer**.  
Abra una consola de comandos en la raíz del proyecto (`dd-sistema-gestion-eventos`) y ejecute:

```bash
composer require phpmailer/phpmailer
```

*(Esto descargará las dependencias en la carpeta `vendor/` y actualizará automáticamente el autoloader).*

---

### 2. Generar Contraseña de Aplicación en Google Workspace / Gmail

Dado que la cuenta remitente es `certificados@evenrity.com`:

1. Inicie sesión con la cuenta `certificados@evenrity.com` en Google.
2. Vaya a la sección de **Seguridad** de la cuenta ([myaccount.google.com/security](https://myaccount.google.com/security)).
3. Asegúrese de que la **Verificación en 2 pasos** esté activada.
4. En el buscador superior de la cuenta de Google, escriba **"Contraseñas de aplicaciones"** (*App Passwords*).
5. Genere una nueva clave asignándole un nombre identificativo (ej. `Sistema Certificados Evenrity`).
6. Copie la contraseña generada de **16 caracteres** (ejemplo: `abcd efgh ijkl mnop`).

---

### 3. Crear / Configurar las Credenciales SMTP (`helpers/MailerHelper.php`)

Cree o edite el archivo `helpers/MailerHelper.php` e ingrese la clave generada dentro de la función `enviarCertificado()`:

```php

    /*public static function enviarCertificado(
        string $destinatarioEmail, 
        string $destinatarioNombre, 
        string $eventoTitulo, 
        string $pdfBinaryContent
    ): bool {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'certificados@evenrity.com';
            $mail->Password   = 'COLOCAR_AQUI_LA_CONTRASEÑA_DE_APLICACION_DE_16_CARACTERES';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

        ...
    */

---

### 4. Verificar Recurso de Logo en Plantilla PDF

Compruebe que la imagen del logo institucional exista físicamente en la siguiente ruta del proyecto para garantizar su renderizado correcto con Dompdf:

```text
assets/images/logo-image.png
```

---

### 5. Habilitar los Métodos de Envío en el Controlador y Vista JS

Una vez validados los pasos anteriores, proceda a activar los botones de envío en el código:

1. **En el Controlador (`controllers/CertificadosController.php`):**
   * Localice los métodos `enviarIndividual()` y `enviarMasivo()`.
   * Remueva la respuesta de bloqueo temporal (`echo json_encode(['success' => false, 'message' => '...']); exit;`).
   * Descomente los bloques de procesamiento PHP para la generación del PDF con Dompdf y la llamada a `MailerHelper::enviarCertificado()`.

2. **En la Vista JavaScript (`views/certificados/certificado.js`):**
   * Remueva las alertas informativas `mostrarAlertaConfiguracion(...)` en los listeners de los botones `btnEnviarMasivoDisabled` y `.btn-enviar-disabled`.
   * Habilite la llamada AJAX `fetch` apuntando hacia los endpoints `?c=certificados&a=enviarIndividual` y `?c=certificados&a=enviarMasivo`.