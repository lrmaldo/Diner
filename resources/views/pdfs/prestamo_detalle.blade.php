<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detalle préstamo #{{ $prestamo->id }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 6px; border: 1px solid #ddd; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detalle de Préstamo</h1>
        <p>Préstamo ID: <strong>{{ $prestamo->id }}</strong></p>
    </div>

    <div class="section">
        <h3>Resumen</h3>
        <table>
            <tr>
                <th>Producto</th>
                <td>{{ ucfirst($prestamo->producto ?? 'N/A') }}</td>
                <th>Monto total</th>
                <td class="right">${{ number_format($prestamo->monto_total ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Plazo</th>
                <td>{{ $prestamo->plazo }} meses</td>
                <th>Fecha de entrega</th>
                <td>{{ $prestamo->fecha_entrega ? $prestamo->fecha_entrega->format('d/m/Y h:i a') : '—' }}</td>
            </tr>
            <tr>
                <th>Tasa interés</th>
                <td>{{ $prestamo->tasa_interes ?? '0' }}%</td>
                <th>Garantía</th>
                <td>{{ $prestamo->garantia ?? '—' }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Solicitantes / Integrantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>CURP</th>
                    <th>Solicitado</th>
                </tr>
            </thead>
            <tbody>
                @if($prestamo->producto === 'grupal')
                    @foreach($prestamo->clientes as $cliente)
                        <tr>
                            <td>{{ trim(($cliente->nombres ?? $cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')) }}</td>
                            <td>{{ $cliente->curp ?? '—' }}</td>
                            <td class="right">${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    @if($prestamo->cliente)
                        <tr>
                            <td>{{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')) }}</td>
                            <td>{{ $prestamo->cliente->curp ?? '—' }}</td>
                            <td class="right">${{ number_format($prestamo->monto_total ?? 0, 2) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Comentarios del Comité</h3>
        <div>{{ $prestamo->comentarios_comite ?? 'No hay comentarios del comité.' }}</div>
    </div>

    <footer style="margin-top: 30px; font-size: 12px; color: #666; text-align: center;">
        Generado: {{ now()->format('d/m/Y h:i a') }}
    </footer>
</body>
</html>
