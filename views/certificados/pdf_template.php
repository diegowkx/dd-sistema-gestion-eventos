<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Participación</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 30px;
            background-color: #fdfdfd;
            color: #2b2b2b;
        }
        .cert-border {
            border: 8px double #1a365d;
            padding: 30px;
            height: 87%;
            position: relative;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        /* --- CONTENEDOR ESPECIALIZADO PARA EL LOGO --- */
        .logo-container {
            width: 100%;
            max-width: 200px;
            height: 60px;
            display: block;
            text-align: left;
        }
        .logo {
            max-width: 180px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
            vertical-align: middle;
        }

        .company-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .company-sub {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        .cert-title {
            text-align: center;
            font-size: 34px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 15px;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .cert-subtitle {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 25px;
        }
        .participant-name {
            text-align: center;
            font-family: 'DejaVu Sans', 'Georgia', cursive;
            font-style: italic;
            font-size: 38px;
            color: #1e3a8a;
            margin: 20px 0;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 10px;
            display: block;
        }
        .cert-text {
            text-align: center;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            margin: 0 40px;
        }
        .event-title {
            font-weight: bold;
            color: #0f172a;
        }
        .footer-table {
            width: 100%;
            margin-top: 50px;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 200px;
            margin: 0 auto 5px auto;
        }
        .signature-text {
            text-align: center;
            font-size: 12px;
            color: #475569;
        }
        .cert-date {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="cert-border">
        <table class="header-table">
            <tr>
                <td style="width: 50%; vertical-align: middle;">
                    <!-- CONTENEDOR ESPECIALIZADO -->
                    <div class="logo-container">
                        <?php if (!empty($logoBase64)): ?>
                            <img src="<?= $logoBase64 ?>" class="logo" alt="Evenrity Logo">
                        <?php else: ?>
                            <!-- Ruta física directa de respaldo -->
                            <img src="<?= __DIR__ . '/../../assets/images/logo-image.png' ?>" class="logo" alt="Evenrity Logo">
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: middle;">
                    <div class="company-title">Evenrity</div>
                    <div class="company-sub">certificados@evenrity.com</div>
                </td>
            </tr>
        </table>

        <div class="cert-title">Certificado de Otorgamiento</div>
        <div class="cert-subtitle">Otorgado con orgullo a</div>

        <div class="participant-name">
            <?= htmlspecialchars(trim(($participante['nombres'] ?? '') . ' ' . ($participante['apellidos'] ?? ''))) ?>
        </div>

        <div class="cert-text">
            Por haber asistido y completado satisfactoriamente el evento académico/corporativo:<br>
            <span class="event-title">"<?= htmlspecialchars($participante['evento_titulo'] ?? 'Evento') ?>"</span>
        </div>

        <table class="footer-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-text"><strong>Evenrity Management</strong><br>Entidad Emisora</div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-text"><strong>Dirección Académica</strong><br>Firma Autorizada</div>
                </td>
            </tr>
        </table>

        <div class="cert-date">
            Documento emitido el <?= date('d/m/Y') ?> | ID Doc: <?= htmlspecialchars($participante['documento_id'] ?? '—') ?>
        </div>
    </div>
</body>
</html>