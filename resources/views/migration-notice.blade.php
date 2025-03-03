<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avviso di Manutenzione - Osm2Cai</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px;
        }

        h1 {
            color: #3b5998;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .notice {
            margin-bottom: 25px;
            text-align: center;
        }

        .notice p {
            margin-bottom: 15px;
        }

        .highlight {
            font-weight: bold;
            color: #e74c3c;
        }

        .contact {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            text-align: center;
        }

        .contact a {
            color: #3b5998;
            text-decoration: none;
            font-weight: bold;
        }

        .contact a:hover {
            text-decoration: underline;
        }

        .button-container {
            text-align: center;
        }

        .button {
            display: inline-block;
            background-color: #3b5998;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #2d4373;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            @include('nova::partials.logo', ['width' => '200', 'height' => '39'])
        </div>

        <h1>Avviso di Manutenzione Programmata</h1>

        <div class="notice">
            <p>Gentile utente,</p>
            <p>Ti informiamo che <span class="highlight">lunedí 17 marzo</span> effettueremo un'importante migrazione del
                sistema.</p>
            <p>La piattaforma sarà <span class="highlight">temporaneamente non disponibile</span> per un massimo di
                <span class="highlight">48 ore</span>.
            </p>
            <p>Ci scusiamo per il disagio e ti ringraziamo per la comprensione.</p>
        </div>

        <div class="contact">
            <p>Per ulteriori informazioni, contattaci all'indirizzo:</p>
            <p><a href="mailto:info@webmapp.it">info@webmapp.it</a></p>
        </div>

        <div class="button-container">
            <a href="{{ route('migration.continue') }}" class="button">Continua la navigazione</a>
        </div>
    </div>
</body>

</html>
