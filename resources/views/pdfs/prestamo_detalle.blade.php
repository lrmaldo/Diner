<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detalle préstamo #{{ $prestamo->id }}</title>
    <style>
        @page {
            size: landscape;
            margin: 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }
        .logo {
            max-height: 55px;
            vertical-align: middle;
        }
        .title-large {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        .subtitle-text {
            font-size: 10px;
            color: #666;
            margin: 0;
            padding: 2px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 5px;
            vertical-align: middle;
        }
        .info-table {
            margin-bottom: 15px;
        }
        .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            background-color: #f9f9f9;
            vertical-align: top;
        }
        .info-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            display: block;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            display: block;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .data-table {
            font-size: 9px;
            margin-bottom: 15px;
        }
        .data-table th {
            padding: 7px 5px;
            border: 1px solid #ddd;
            background-color: #e5e5e5;
            font-weight: bold;
            text-align: left;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .comments-box {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            min-height: 50px;
            white-space: pre-wrap;
            font-size: 10px;
        }
        .footer-text {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
        /* ocultar elementos con clase .no-print en la impresión del navegador */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
@php
    $forPdf = $forPdf ?? false;
    if ($forPdf) {
        $logoPath = public_path('img/logo.JPG');
        if (file_exists($logoPath) && is_readable($logoPath)) {
            $type = @mime_content_type($logoPath) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:' . $type . ';base64,' . $data;
        } else {
            // fallback a file:// si no se puede leer
            $pub = str_replace('\\', '/', public_path('img/logo.JPG'));
            $logoSrc = 'file:///' . ltrim($pub, '/');
        }
    } else {
        $logoSrc = asset('img/logo.JPG');
    }
@endphp

    {{-- Encabezado --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 60px;">
                <img src="{{ $logoSrc }}" alt="Logo" class="logo">
            </td>
            <td style="width: 60%;">
                <div class="title-large">DETALLES DEL CRÉDITO</div>
                <div class="subtitle-text">Folio: {{ str_pad($prestamo->id, 4, '0', STR_PAD_LEFT) }} | Grupo: {{ $prestamo->producto === 'grupal' ? ($prestamo->representante ? $prestamo->representante->nombres : 'Sin nombre') : 'Individual' }}</div>
            </td>
            <td style="text-align: right; width: 30%;">
                <div style="font-size: 13px; font-weight: bold;">{{ now()->format('d/m/Y') }}</div>
                <div class="subtitle-text">FECHA DE IMPRESIÓN</div>
            </td>
        </tr>
    </table>

    @unless($forPdf)
    <div class="no-print" style="margin-bottom:12px;text-align:right;">
        <button onclick="window.print()" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer;margin-right:5px;">Imprimir</button>
        <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'detalle']) }}" style="padding:8px 12px;border-radius:6px;border:1px solid #2563eb;background:#2563eb;color:#fff;text-decoration:none;display:inline-block">Descargar PDF</a>
    </div>
    @endunless

    {{-- Información del préstamo --}}
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 33.33%;">
                <span class="info-label">MONTO SOLICITADO</span>
                <span class="info-value" style="color: #16a34a;">${{ number_format($prestamo->monto_total ?? 0, 2) }}</span>
            </td>
            <td style="width: 33.33%;">
                <span class="info-label">PRODUCTO</span>
                <span class="info-value">{{ ucfirst($prestamo->producto ?? 'N/A') }}</span>
            </td>
            <td style="width: 33.33%;">
                <span class="info-label">PLAZO</span>
                <span class="info-value">{{ $prestamo->plazo }} meses</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">PERIODICIDAD</span>
                <span class="info-value">{{ ucfirst($prestamo->periodicidad ?? '—') }}</span>
            </td>
            <td>
                <span class="info-label">TASA DE INTERÉS</span>
                <span class="info-value">{{ $prestamo->tasa_interes ?? '0' }}%</span>
            </td>
            <td>
                <span class="info-label">GARANTÍA</span>
                <span class="info-value">{{ $prestamo->garantia ?? '—' }}%</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="info-label">FECHA DE ENTREGA</span>
                <span class="info-value">{{ $prestamo->fecha_entrega ? $prestamo->fecha_entrega->format('d/m/Y') : '—' }}</span>
            </td>
            <td>
                <span class="info-label">DÍA DE PAGO</span>
                <span class="info-value">{{ ucfirst($prestamo->dia_pago ?? '—') }}</span>
            </td>
            <td>
                <span class="info-label">ESTADO</span>
                <span class="info-value" style="color: {{ $prestamo->estado === 'autorizado' ? '#16a34a' : ($prestamo->estado === 'rechazado' ? '#dc2626' : '#ca8a04') }};">
                    {{ ucfirst($prestamo->estado === 'en_comite' ? 'En Comité' : ($prestamo->estado === 'en_curso' ? 'En Curso' : $prestamo->estado)) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Tabla de solicitantes --}}
    <div class="section-title">{{ $prestamo->producto === 'grupal' ? 'Integrantes del Grupo' : 'Solicitante' }}</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 5%;" class="center">#</th>
                    <th style="width: 40%;">Nombre Completo</th>
                    <th style="width: 20%;">CURP</th>
                    <th style="width: 15%;" class="right">Solicitado</th>
                    <th style="width: 15%;" class="right">Sugerido</th>
                    <th style="width: 15%;" class="right">Autorizado</th>
                    @if($prestamo->producto === 'grupal')
                        <th style="width: 10%;" class="center">Rol</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if($prestamo->producto === 'grupal')
                    @foreach($prestamo->clientes as $index => $cliente)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ trim(($cliente->nombres ?? $cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? '')) }}</td>
                            <td>{{ $cliente->curp ?? '—' }}</td>
                            <td class="right">${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}</td>
                            <td class="right">{{ $cliente->pivot->monto_sugerido ? '$' . number_format($cliente->pivot->monto_sugerido, 2) : '—' }}</td>
                            <td class="right" style="font-weight: bold; color: #2563eb;">{{ $cliente->pivot->monto_autorizado ? '$' . number_format($cliente->pivot->monto_autorizado, 2) : '—' }}</td>
                            <td class="center">{{ $prestamo->representante_id == $cliente->id ? 'Representante' : 'Integrante' }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="3" class="right">TOTAL:</td>
                        <td class="right">${{ number_format($prestamo->clientes->sum(fn($c) => $c->pivot->monto_solicitado ?? 0), 2) }}</td>
                        <td class="right">{{ $prestamo->clientes->sum(fn($c) => $c->pivot->monto_sugerido ?? 0) > 0 ? '$' . number_format($prestamo->clientes->sum(fn($c) => $c->pivot->monto_sugerido ?? 0), 2) : '—' }}</td>
                        <td class="right" style="color: #2563eb;">{{ $prestamo->clientes->sum(fn($c) => $c->pivot->monto_autorizado ?? 0) > 0 ? '$' . number_format($prestamo->clientes->sum(fn($c) => $c->pivot->monto_autorizado ?? 0), 2) : '—' }}</td>
                        <td></td>
                    </tr>
                @else
                    @if($prestamo->cliente)
                        @php
                            $clienteEnPivot = $prestamo->clientes->firstWhere('id', $prestamo->cliente_id);
                            $montoAutorizado = $clienteEnPivot->pivot->monto_autorizado ?? null;
                        @endphp
                        <tr>
                            <td class="center">1</td>
                            <td>{{ trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? '')) }}</td>
                            <td>{{ $prestamo->cliente->curp ?? '—' }}</td>
                            <td class="right">${{ number_format($prestamo->monto_total ?? 0, 2) }}</td>
                            <td class="right">—</td>
                            <td class="right" style="font-weight: bold; color: #2563eb;">{{ $montoAutorizado ? '$' . number_format($montoAutorizado, 2) : '—' }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
    </table>

    {{-- Comentarios del comité --}}
    <div class="section-title">Comentarios del Comité</div>
    <div class="comments-box">{{ $prestamo->comentarios_comite ?? 'No hay comentarios del comité.' }}</div>

    {{-- Footer --}}
    <div class="footer-text">
        <div>Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('h:i a') }}</div>
        <div>Sistema de Gestión de Préstamos - Diner</div>
    </div>
</body>
</html>
