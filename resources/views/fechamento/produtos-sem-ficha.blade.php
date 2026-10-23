<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fechamento da Ficha Técnica</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }

        .stat .label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .stat .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        thead {
            background: #111827;
            color: #fff;
        }

        th, td {
            padding: 11px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .codigo {
            font-weight: bold;
            color: #1d4ed8;
            width: 160px;
        }

        .empty {
            padding: 20px;
            background: #ecfdf5;
            color: #166534;
            border-radius: 8px;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
            text-align: right;
        }

        @media (max-width: 800px) {
            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 500px) {
            .stats {
                grid-template-columns: 1fr;
            }

            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="card">
        <div class="header">
            <h1>Fechamento da Ficha Técnica</h1>

            @if(($resultado['total_produtos_sem_ficha'] ?? 0) > 0)
                <span class="badge-warning">
                    Pendências encontradas
                </span>
            @else
                <span class="badge-success">
                    Tudo certo
                </span>
            @endif
        </div>

        <div class="stats">
            <div class="stat">
                <div class="label">Período</div>
                <div class="value">
                    {{ str_pad($resultado['mes'] ?? '', 2, '0', STR_PAD_LEFT) }}/{{ $resultado['ano'] ?? '' }}
                </div>
            </div>

            <div class="stat">
                <div class="label">Produtos analisados</div>
                <div class="value">{{ $resultado['produtos'] ?? 0 }}</div>
            </div>

            <div class="stat">
                <div class="label">Linhas de ficha</div>
                <div class="value">{{ $resultado['linhas_ficha'] ?? 0 }}</div>
            </div>

            <div class="stat">
                <div class="label">Produtos sem ficha</div>
                <div class="value">{{ $resultado['total_produtos_sem_ficha'] ?? 0 }}</div>
            </div>
        </div>

        @if(($resultado['total_produtos_sem_ficha'] ?? 0) > 0)
            <h2>Produtos sem ficha técnica</h2>

            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Produto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($resultado['produtos_sem_ficha'] ?? []) as $produto)
                        <tr>
                            <td class="codigo">{{ $produto['cod_produto'] ?? '' }}</td>
                            <td>{{ $produto['produto'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">
                Nenhum produto sem ficha técnica encontrado.
            </div>
        @endif

        <div class="footer">
            Processado em {{ date('d/m/Y H:i:s') }}
        </div>
    </div>

</div>
</body>
</html>
