<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Issues Chronology</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        h3 {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 70px;
            font-style: italic;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Hiking Route: {{ $hikingRoute->name }}</h1>

    <h3>Cronologia aggiornamenti percorribilitá</h3>

    <table>
        <tr>
            <th>Stato</th>
            <th>Descrizione</th>
            <th>Ultimo Aggiornamento</th>
            <th>Utente</th>
        </tr>
        @php
            $issuesChronology = json_decode($hikingRoute->issues_chronology, true);
        @endphp
        @if ($issuesChronology)
            @foreach ($issuesChronology as $issue)
                <tr>
                    <td>{{ $issue['issues_status'] }}</td>
                    <td>{{ $issue['issues_description'] }}</td>
                    <td>{{ date('d/m/Y H:i:s', strtotime($issue['issues_last_update'])) }}</td>
                    <td>{{ $issue['issues_user'] }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="4">Nessuna segnalazione.</td>
            </tr>
        @endif
    </table>

</body>

</html>
