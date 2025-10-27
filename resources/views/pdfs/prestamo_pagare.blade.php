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

        <img src="{{ $logoSrc }}" alt="Logo" style="max-height:80px;display:block;margin:0 auto 8px">
        <h1 class="center">Pagaré</h1>
        <p class="center">Préstamo ID: <strong>{{ $prestamo->id }}</strong></p>

        @unless($forPdf)
        <div class="no-print" style="display:flex;gap:8px;margin-bottom:12px;justify-content:flex-end;">
            <button onclick="window.print()" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer">Imprimir</button>
            <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'pagare']) }}" style="padding:8px 12px;border-radius:6px;border:1px solid #2563eb;background:#2563eb;color:#fff;text-decoration:none;display:inline-block">Descargar PDF</a>
        </div>
        @endunless

        <p class="small">En la ciudad y fecha actual, el/los suscrito(s) se obligan a pagar a la orden la cantidad de <strong>${{ number_format($prestamo->monto_total ?? 0, 2) }}</strong> correspondiente al crédito otorgado bajo las condiciones pactadas en este documento.</p>

        <h3>Datos del representante / solicitante</h3>
        @if($prestamo->producto === 'grupal')
            <ul>
                @foreach($prestamo->clientes as $cliente)
                    <li>{{ trim(($cliente->nombres ?? $cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? '') ) }}</li>
                @endforeach
            </ul>
        @else
            @if($prestamo->cliente)
                <p>{{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '')) }}</p>
            @endif
        @endif

        <p class="small">Plazo: {{ $prestamo->plazo }} meses. Tasa de interés: {{ $prestamo->tasa_interes ?? '0' }}%.</p>

        <div style="margin-top: 40px;">
            <p>______________________________</p>
            <p>Firma</p>
        </div>

        <footer style="margin-top: 30px; font-size: 12px; color: #666; text-align: center;">
            Generado: {{ now()->format('d/m/Y h:i a') }}
        </footer>
    </div>
</body>
</html>
