<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #000000;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header span {
            color: #ff0000;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #666;
            margin-bottom: 30px;
        }

        .code-container {
            margin: 30px 0;
        }

        .verification-code {
            font-size: 42px;
            font-weight: bold;
            color: #ff0000;
            letter-spacing: 8px;
            padding: 15px 30px;
            border: 2px dashed #000;
            display: inline-block;
            background-color: #fff;
        }

        .btn {
            display: inline-block;
            background-color: #000000;
            color: #ffffff;
            text-decoration: none;
            padding: 15px 30px;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #000;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background-color: #ff0000;
            border-color: #ff0000;
        }

        .footer {
            background-color: #1a1a1a;
            color: #888;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Audits<span>APP</span></h1>
        </div>
        <div class="content">
            <h2>Verificación de Cuenta</h2>
            <p>Hola,</p>
            <p>Usa el siguiente código para completar tu inicio de sesión como técnico. Este código expirará en 10 minutos.</p>

            <div class="code-container">
                <span class="verification-code">{{ $code }}</span>
            </div>

            <p>Si no solicitaste este código, puedes ignorar este correo de forma segura.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Audits API. Todos los derechos reservados.</p>
            <p>Este es un mensaje automático, por favor no respondas.</p>
        </div>
    </div>
</body>

</html>