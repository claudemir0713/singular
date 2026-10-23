<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos sem ficha técnica</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, Helvetica, sans-serif; color:#111827;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f6f9; padding:30px 0;">
    <tr>
        <td align="center">

            <table width="960" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e5e7eb;">

                <tr>
                    <td style="padding:28px 30px 18px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="left">
                                    <h1 style="margin:0; font-size:26px; color:#111827; font-weight:bold;">
                                        Fechamento da Ficha Técnica
                                    </h1>
                                </td>

                                <td align="right">
                                    @if(($resultado['total_produtos_sem_ficha'] ?? 0) > 0)
                                        <span style="display:inline-block; background:#fef3c7; color:#92400e; padding:9px 16px; border-radius:20px; font-size:13px; font-weight:bold;">
                                            Pendências encontradas
                                        </span>
                                    @else
                                        <span style="display:inline-block; background:#dcfce7; color:#166534; padding:9px 16px; border-radius:20px; font-size:13px; font-weight:bold;">
                                            Tudo certo
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px;">
                        <div style="height:1px; background:#e5e7eb;"></div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px 30px 10px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="25%" style="padding-right:10px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                        <tr>
                                            <td style="padding:16px;">
                                                <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Período</div>
                                                <div style="font-size:26px; font-weight:bold; color:#111827;">
                                                    {{ str_pad($resultado['mes'] ?? '', 2, '0', STR_PAD_LEFT) }}/{{ $resultado['ano'] ?? '' }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="25%" style="padding-right:10px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                        <tr>
                                            <td style="padding:16px;">
                                                <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Produtos analisados</div>
                                                <div style="font-size:26px; font-weight:bold; color:#111827;">
                                                    {{ $resultado['produtos'] ?? 0 }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="25%" style="padding-right:10px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                        <tr>
                                            <td style="padding:16px;">
                                                <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Linhas de ficha</div>
                                                <div style="font-size:26px; font-weight:bold; color:#111827;">
                                                    {{ $resultado['linhas_ficha'] ?? ($resultado['linhas_ficha_custo'] ?? 0) }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="25%">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                        <tr>
                                            <td style="padding:16px;">
                                                <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">Produtos sem ficha</div>
                                                <div style="font-size:26px; font-weight:bold; color:#111827;">
                                                    {{ $resultado['total_produtos_sem_ficha'] ?? 0 }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px 30px 30px 30px;">

                        @if(($resultado['total_produtos_sem_ficha'] ?? 0) > 0)

                            <h2 style="margin:0 0 18px 0; font-size:22px; color:#1f2937;">
                                Produtos sem ficha técnica
                            </h2>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th align="left" style="background:#111827; color:#ffffff; padding:13px 15px; font-size:14px;">
                                            Código
                                        </th>

                                        <th align="left" style="background:#111827; color:#ffffff; padding:13px 15px; font-size:14px;">
                                            Produto
                                        </th>

                                        <th align="left" style="background:#111827; color:#ffffff; padding:13px 15px; font-size:14px;">
                                            Origem
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(($resultado['produtos_sem_ficha'] ?? []) as $index => $produto)
                                        <tr>
                                            <td style="padding:12px 15px; border-bottom:1px solid #e5e7eb; font-size:14px; font-weight:bold; color:#1d4ed8; background:{{ $index % 2 == 0 ? '#ffffff' : '#f9fafb' }};">
                                                {{ $produto['cod_produto'] ?? '' }}
                                            </td>

                                            <td style="padding:12px 15px; border-bottom:1px solid #e5e7eb; font-size:14px; color:#111827; background:{{ $index % 2 == 0 ? '#ffffff' : '#f9fafb' }};">
                                                {{ $produto['produto'] ?? '' }}
                                            </td>

                                            <td style="padding:12px 15px; border-bottom:1px solid #e5e7eb; font-size:14px; color:#111827; background:{{ $index % 2 == 0 ? '#ffffff' : '#f9fafb' }};">
                                                {{ $produto['origem'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        @else

                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ecfdf5; border:1px solid #bbf7d0; border-radius:8px;">
                                <tr>
                                    <td style="padding:18px; color:#166534; font-weight:bold; font-size:15px;">
                                        Nenhum produto sem ficha técnica encontrado.
                                    </td>
                                </tr>
                            </table>

                        @endif

                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 30px; background:#f9fafb; border-top:1px solid #e5e7eb; color:#6b7280; font-size:12px;" align="right">
                        Processado em {{ date('d/m/Y H:i:s') }}
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
