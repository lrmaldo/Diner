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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="center">Pagaré</h1>
        <p class="center">Préstamo ID: <strong>{{ $prestamo->id }}</strong></p>

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
