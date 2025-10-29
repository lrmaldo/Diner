<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pagaré préstamo #{{ $prestamo->id }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #222; }
        .container { max-width: 800px; margin: 0 auto; }
        .center { text-align: center; }
        .small { font-size: 0.9rem; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container">
@php
    $forPdf = $forPdf ?? false;
    if ($forPdf) {
        $logoPath = public_path('img/logo.JPG');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            $type = @mime_content_type($logoPath) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:' . $type . ';base64,' . $data;
        } else {
            $pub = str_replace('\\', '/', public_path('img/logo.JPG'));
            $logoSrc = 'file:///' . ltrim($pub, '/');
        }
    } else {
        $logoSrc = asset('img/logo.JPG');
    }
@endphp

        <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:8px;">
            <div style="width:120px;flex:0 0 120px;">
                <img src="{{ $logoSrc }}" alt="Logo" style="max-height:80px;display:block;margin:0">
            </div>
            <div style="flex:1;text-align:center;">
                <h1 style="margin:0;font-size:20px;letter-spacing:2px">PAGARE</h1>
                <div style="margin-top:6px;font-size:12px">Motul, Yucatán a {{ now()->format('d/m/Y') }}</div>
            </div>
            <div style="width:120px;flex:0 0 120px;"></div>
        </div>

        @unless($forPdf)
        <div class="no-print" style="display:flex;gap:8px;margin-bottom:12px;justify-content:flex-end;">
            <button onclick="window.print()" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer">Imprimir</button>
            <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'pagare']) }}" style="padding:8px 12px;border-radius:6px;border:1px solid #2563eb;background:#2563eb;color:#fff;text-decoration:none;display:inline-block">Descargar PDF</a>
        </div>
        @endunless

        <div style="margin-top:8px;">
            <p style="text-align:justify;line-height:1.5;font-size:13px;margin:0 0 8px 0">Por este pagare me obligo a pagar incondicionalmente a Diner contigo, representado por sus funcionarios, la cantidad que en la parte inferior de este documento se señala, en las fechas establecidas y por los montos señalados.</p>

            <p style="text-align:justify;line-height:1.5;font-size:13px;margin:0 0 8px 0">De igual manera me obligo a pagar los intereses mensuales pactados con los funcionarios y que se encuentran plasmados en el calendario de pagos que me ha sido entregado, al momento de firmar este documento.</p>

            <p style="text-align:justify;line-height:1.5;font-size:13px;margin:0 0 8px 0">En caso de incumplir con algunas de las fechas establecidas en este pagare, se suma a su adeudo un 5% por concepto de multa por atraso.</p>

            <p style="text-align:justify;line-height:1.5;font-size:13px;margin:0 0 8px 0">En caso de que el adeudo exceda a la última fecha de pago, sin que se haya liquidado su totalidad, este saldo vencido generará una multa por morosidad equivalente al 10% mensual, considerando únicamente los días transcurridos.</p>

            <p style="text-align:justify;line-height:1.5;font-size:13px;margin:0 0 8px 0">Para la interpretación de este pagare, nos sujetamos a las leyes vigentes en la ciudad de Mérida, Yucatán, México, al día de la firma del presente documento.</p>
        </div>

        <div style="margin-top:24px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div><strong>Deudores:</strong></div>
                <div><strong>firma</strong></div>
            </div>

            <table style="width:100%;margin-top:12px;font-size:13px;">
                <tr>
                    <td style="vertical-align:top;">
                        @php
                            $nombre = '';
                            if ($prestamo->producto === 'grupal' && $prestamo->clientes && $prestamo->clientes->count() > 0) {
                                $nombre = trim(($prestamo->clientes->first()->nombres ?? $prestamo->clientes->first()->nombre ?? '') . ' ' . ($prestamo->clientes->first()->apellido_paterno ?? ''));
                            } elseif ($prestamo->cliente) {
                                $nombre = trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? ''));
                            }
                        @endphp
                        {{ $nombre }}
                        <div style="margin-top:6px">{{ $prestamo->cliente->direccion ?? '' }}</div>
                        <div>{{ $prestamo->cliente->telefono ?? $prestamo->cliente->cel ?? '' }}</div>
                    </td>
                    <td style="width:220px;text-align:right;vertical-align:top;">
                        <div>${{ number_format($prestamo->monto_total ?? 0, 2) }}&nbsp;__________________</div>
                    </td>
                </tr>
            </table>
        </div>

        <footer style="margin-top: 30px; font-size: 12px; color: #666; text-align: center;">
            Generado: {{ now()->format('d/m/Y h:i a') }}
        </footer>
    </div>
</body>
</html>
