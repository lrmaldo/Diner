<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pagaré préstamo #{{ $prestamo->id }}</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: Arial, Helvetica, sans-serif; color: #222; font-size:14px; }
        .container { max-width: 720px; margin: 0 auto; padding: 0 8px; }
        .center { text-align: center; }
        .small { font-size: 0.95rem; }
        h1 { font-size: 22px; margin:0; }
        p { font-size: 14px; }
        .logo-left { max-height:72px; display:block; }
        @media print {
            .no-print { display: none !important; }
            /* asegurar márgenes en impresión */
            html, body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
@php
    $forPdf = $forPdf ?? false;
    // Prefer a pre-optimized logo for printing (smaller file), fallback to default
    $optimizedRelative = 'img/logo-print.jpg';
    $defaultRelative = 'img/logo.JPG';

    if ($forPdf) {
        // Try optimized logo first
        $optPath = public_path($optimizedRelative);
        if (file_exists($optPath) && is_readable($optPath)) {
            $type = @mime_content_type($optPath) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($optPath));
            $logoSrc = 'data:' . $type . ';base64,' . $data;
        } else {
            $logoPath = public_path($defaultRelative);
            if (file_exists($logoPath) && is_readable($logoPath)) {
                $type = @mime_content_type($logoPath) ?: 'image/jpeg';
                $data = base64_encode(file_get_contents($logoPath));
                $logoSrc = 'data:' . $type . ';base64,' . $data;
            } else {
                // fallback to file path
                $pub = str_replace('\\', '/', public_path($defaultRelative));
                $logoSrc = 'file:///' . ltrim($pub, '/');
            }
        }
    } else {
        // web view uses asset URLs
        if (file_exists(public_path($optimizedRelative))) {
            $logoSrc = asset($optimizedRelative);
        } else {
            $logoSrc = asset($defaultRelative);
        }
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

            @if($prestamo->producto === 'grupal')
                {{-- Préstamo grupal: Representante primero, luego integrantes --}}
                @if($prestamo->representante)
                    @php
                        // Buscar al representante dentro de los clientes para obtener datos del pivot
                        $representanteConPivot = $prestamo->clientes->firstWhere('id', $prestamo->representante->id);
                    @endphp
                    <table style="width:100%;margin-top:12px;font-size:13px;">
                        <tr>
                            <td style="vertical-align:top;">
                                {{ mb_strtoupper(trim(($prestamo->representante->nombres ?? '') . ' ' . ($prestamo->representante->apellido_paterno ?? '') . ' ' . ($prestamo->representante->apellido_materno ?? '')), 'UTF-8') }} (REPRESENTANTE)
                                <div style="margin-top:6px">{{ $prestamo->representante->direccion ?? '' }}</div>
                                <div>{{ $prestamo->representante->telefono ?? $prestamo->representante->cel ?? '' }}</div>
                            </td>
                            <td style="width:220px;text-align:right;vertical-align:top;">
                                <div>${{ number_format($representanteConPivot->pivot->monto_autorizado ?? $representanteConPivot->pivot->monto_solicitado ?? 0, 0) }}&nbsp;__________________</div>
                            </td>
                        </tr>
                    </table>
                @endif

                {{-- Integrantes del grupo --}}
                @foreach($prestamo->clientes as $cliente)
                    @if(!$prestamo->representante || $cliente->id !== $prestamo->representante->id)
                        <table style="width:100%;margin-top:8px;font-size:13px;">
                            <tr>
                                <td style="vertical-align:top;">
                                    {{ mb_strtoupper(trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')), 'UTF-8') }}
                                    <div style="margin-top:6px">{{ $cliente->direccion ?? '' }}</div>
                                    <div>{{ $cliente->telefono ?? $cliente->cel ?? '' }}</div>
                                </td>
                                <td style="width:220px;text-align:right;vertical-align:top;">
                                    <div>${{ number_format($cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0, 0) }}&nbsp;__________________</div>
                                </td>
                            </tr>
                        </table>
                    @endif
                @endforeach
            @else
                {{-- Préstamo individual --}}
                <table style="width:100%;margin-top:12px;font-size:13px;">
                    <tr>
                        <td style="vertical-align:top;">
                            @php
                                $nombre = '';
                                if ($prestamo->cliente) {
                                    $nombre = mb_strtoupper(trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')), 'UTF-8');
                                }
                            @endphp
                            {{ $nombre }}
                            <div style="margin-top:6px">{{ $prestamo->cliente->direccion ?? '' }}</div>
                            <div>{{ $prestamo->cliente->telefono ?? $prestamo->cliente->cel ?? '' }}</div>
                        </td>
                        <td style="width:220px;text-align:right;vertical-align:top;">
                            <div>${{ number_format($prestamo->monto_total ?? 0, 0) }}&nbsp;__________________</div>
                        </td>
                    </tr>
                    @if(!empty($prestamo->cliente->nombre_aval))
                    <tr>
                        <td style="vertical-align:top; padding-top:20px;">
                            <strong>AVAL:</strong> {{ mb_strtoupper($prestamo->cliente->nombre_aval, 'UTF-8') }}
                            <div style="margin-top:6px; font-size:12px;">{{ $prestamo->cliente->direccion_aval ?? '' }}</div>
                            <div style="font-size:12px;">{{ $prestamo->cliente->telefono_aval ?? '' }}</div>
                        </td>
                        <td style="width:220px;text-align:right;vertical-align:top; padding-top:20px;">
                            <div>__________________</div>
                            <div style="font-size:11px; text-align:right; padding-right:10px;">Firma Aval</div>
                        </td>
                    </tr>
                    @endif
                </table>
            @endif
        </div>

        <footer style="margin-top: 30px; font-size: 12px; color: #666; text-align: center;">
            Generado: {{ now()->format('d/m/Y h:i a') }}
        </footer>
    </div>
</body>
</html>
