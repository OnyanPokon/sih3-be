<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ganti Password Akun Anda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #ffffff;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .content {
            padding: 10px 0;
        }

        .button {
            display: inline-block;
            background-color: #007bff color: #ffffff !important;
            padding: 12px 25px;
            margin: 20px 0;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">Permintaan Ganti Password</div>

        <div class="content">
            <p>Halo, {{ $user->email }},</p>
            <p>Kami menerima permintaan untuk mengatur ulang password akun Anda. Anda dapat melakukannya dengan mengklik
                Link di bawah ini:</p>

            <div style="margin: 25px 0;">
                <a href="{{ env('FRONTEND_URL') . $user->remember_token }}"
                style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">
                Verifikasi Password
                </a>
            </div>

            <p>Jika tombol tidak muncul, salin link berikut:</p>
            
            <p style="color: #555; word-break: break-all;">
                {{ env('FRONTEND_URL') . $user->remember_token }}
            </p>

            <p>Jika Anda tidak merasa meminta untuk mengganti password, Anda bisa mengabaikan email ini.</p>

            <p>Terima kasih!</p>
        </div>

        <div class="footer">
            <p>Ini adalah email yang dibuat secara otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>

</html>