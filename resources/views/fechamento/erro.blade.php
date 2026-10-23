<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro no Fechamento</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fef2f2;
            color: #7f1d1d;
            padding: 30px;
        }

        .card {
            background: #fff;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 25px;
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            margin-top: 0;
        }

        pre {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Erro ao processar fechamento</h1>
        <p>Ocorreu um erro durante o processamento.</p>

        <pre>{{ $erro }}</pre>
    </div>
</body>
</html>
