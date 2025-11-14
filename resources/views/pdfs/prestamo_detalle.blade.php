<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detalle préstamo #{{ $prestamo->id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
            font-size: 9px;
            background-color: white;
        }
        /* Estilos para vista HTML (pantalla) - NO para PDF */
        @media screen and (min-width: 1px) {
            body:not(.pdf-mode) {
                background-color: #f3f4f6;
                padding: 20px;
                min-height: 100vh;
            }
            .page-wrapper {
                max-width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background-color: white;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border-radius: 8px;
                overflow: hidden;
            }
        }
        .container {
            background-color: white;
            padding: 8px;
            box-shadow: none;
            border-radius: 0;
        }
        .logo {
            max-height: 40px;
            vertical-align: middle;
        }
        .title-large {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .subtitle-text {
            font-size: 9px;
            color: #666;
            margin: 2px 0 0 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 6px;
            border-bottom: 2px solid #e02424;
            padding-bottom: 6px;
        }
        .header-table td {
            border: none;
            padding: 5px;
            vertical-align: middle;
        }
        .info-label {
            font-size: 8px;
            color: #888;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .info-value {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            display: block;
        }
        .info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 4px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .info-item {
            padding: 6px;
        }
        .divider-vertical {
            border-right: 2px solid #e5e7eb;
        }
        .summary-table {
            width: 100%;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .summary-table td {
            border-right: 1px solid #d1d5db;
        }
        .summary-table td:last-child {
            border-right: none;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin: 6px 0 4px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        .data-table {
            font-size: 8px;
            margin-bottom: 6px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }
        .data-table th {
            padding: 4px 3px;
            border: none;
            background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
            font-weight: 600;
            text-align: center;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .data-table td {
            padding: 4px 3px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            text-align: center;
        }
        .data-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        .total-row {
            background: linear-gradient(to right, #1f2937, #374151) !important;
            color: white !important;
            font-weight: bold;
        }
        .total-row td {
            padding: 6px 5px !important;
            border: none !important;
            vertical-align: middle !important;
        }
        .right { text-align: right !important; }
        .center { text-align: center !important; }
        .left { text-align: left !important; }
        .comments-box {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 6px;
            background-color: #f9fafb;
            min-height: 30px;
            white-space: pre-wrap;
            font-size: 8px;
            color: #4b5563;
            line-height: 1.3;
        }
        .footer-text {
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #e5e7eb;
            font-size: 7px;
            color: #9ca3af;
            text-align: center;
        }
        .btn-group {
            margin-bottom: 20px;
            text-align: right;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        .btn-secondary:hover {
            background-color: #f9fafb;
            border-color: #d1d5db;
        }
        /* Estilos para impresión y PDF */
        @media print {
            body {
                background-color: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-wrapper {
                max-width: none !important;
                min-height: auto !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }
            .container {
                box-shadow: none !important;
                padding: 10px !important;
                margin: 0 !important;
                background-color: white !important;
            }
            .no-print {
                display: none !important;
            }
            .btn-group {
                display: none !important;
            }
        }
        /* Estilos específicos cuando se genera PDF */
        .pdf-mode {
            background-color: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .pdf-mode .page-wrapper {
            max-width: none !important;
            min-height: auto !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background-color: white !important;
        }
    </style>
</head>
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
<body{{ $forPdf ? ' class="pdf-mode"' : '' }}>

    @unless($forPdf)
    <div class="btn-group no-print">
        <button onclick="window.print()" class="btn btn-secondary">
            <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir
        </button>
        <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'detalle']) }}" class="btn btn-primary">
            <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Descargar PDF
        </a>
    </div>
    @endunless

    @unless($forPdf)
    <div class="page-wrapper">
    @endunless

    <div class="container">
    {{-- Encabezado --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 80px; text-align: center;">
                <img src="{{ $logoSrc }}" alt="Logo" class="logo">
            </td>
            <td style="width: 70%;">
                <div class="title-large">Grupo {{ str_pad($prestamo->id, 4, '0', STR_PAD_LEFT) }}</div>
                <div class="subtitle-text">
                    Representante: {{ $prestamo->representante ? trim($prestamo->representante->nombres . ' ' . $prestamo->representante->apellido_paterno . ' ' . $prestamo->representante->apellido_materno) : 'Sin representante' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Información resumida del préstamo --}}
    <div class="info-box">
        <div class="info-grid">
            <div class="info-item divider-vertical">
                <span class="info-label">Representante</span>
                <span class="info-value" style="font-size: 10px;">{{ $prestamo->representante ? strtoupper(trim($prestamo->representante->nombres . ' ' . $prestamo->representante->apellido_paterno . ' ' . $prestamo->representante->apellido_materno)) : 'SIN REPRESENTANTE' }}</span>
            </div>
            <div>

            </div>
        </div>
    </div>

    {{-- Totales principales destacados --}}
    @php
        $garantia = ($prestamo->monto_total ?? 0) * (($prestamo->garantia ?? 0) / 100);
        $comision = ($prestamo->monto_total ?? 0) * 0.02;
        $efectivo = ($prestamo->monto_total ?? 0) - $garantia - $comision;

        // Calcular el total con intereses
        $tasaDecimal = ($prestamo->tasa_interes ?? 0) / 100;
        $montoPorPago = 0;
        $numeroPagos = 0;

        // Determinar número de pagos según plazo
        $plazoTexto = $prestamo->plazo ?? '4meses';
        if (str_contains($plazoTexto, '4meses')) {
            $numeroPagos = 16; // 4 meses semanales
        } elseif (str_contains($plazoTexto, '6meses')) {
            $numeroPagos = 24; // 6 meses semanales
        } elseif (str_contains($plazoTexto, '1ano')) {
            $numeroPagos = 48; // 1 año semanales
        } else {
            $numeroPagos = 16; // default
        }

        if ($prestamo->clientes->count() > 0) {
            foreach($prestamo->clientes as $cliente) {
                $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                $interes = $montoCliente * $tasaDecimal;
                $montoPorPago += $montoCliente + $interes;
            }
            $montoPorPago = $montoPorPago / $numeroPagos;
        }
    @endphp
    {{-- <table class="summary-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="text-align: center; padding: 5px;">
                <div style="font-size: 8px; color: #6b7280; margin-bottom: 2px;">CRÉDITO TOTAL</div>
                <div style="font-size: 11px; font-weight: bold; color: #000;">${{ number_format($prestamo->monto_total ?? 0, 0) }}</div>
            </td>
            <td style="text-align: center; padding: 5px;">
                <div style="font-size: 8px; color: #6b7280; margin-bottom: 2px;">GARANTÍA ({{ $prestamo->garantia ?? 0 }}%)</div>
                <div style="font-size: 11px; font-weight: bold; color: #000;">${{ number_format($garantia, 0) }}</div>
            </td>
            <td style="text-align: center; padding: 5px;">
                <div style="font-size: 8px; color: #6b7280; margin-bottom: 2px;">EFECTIVO A ENTREGAR</div>
                <div style="font-size: 11px; font-weight: bold; color: #10b981;">${{ number_format($efectivo, 0) }}</div>
            </td>
            <td style="text-align: center; padding: 5px;">
                <div style="font-size: 8px; color: #6b7280; margin-bottom: 2px;">PAGO ({{ $numeroPagos }} PAGOS)</div>
                <div style="font-size: 11px; font-weight: bold; color: #000;">${{ number_format($montoPorPago, 0) }}</div>
            </td>
        </tr>
    </table> --}}

    {{-- Tabla de solicitantes --}}
    <div class="section-title">{{ $prestamo->producto === 'grupal' ? 'Integrantes del Grupo' : 'Solicitante' }}</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20%; vertical-align: middle; text-align: left;">Nombre</th>
                    <th colspan="3" style="background: #1f2937; color: white; text-align: center;">Deducciones</th>
                    <th colspan="3" style="background: #1f2937; color: white; text-align: center;">
                        @php
                            // Determinar número de pagos según plazo
                            $plazoTexto = $prestamo->plazo ?? '4meses';
                            if (str_contains($plazoTexto, '4meses')) {
                                $numeroPagos = 16;
                            } elseif (str_contains($plazoTexto, '6meses')) {
                                $numeroPagos = 24;
                            } elseif (str_contains($plazoTexto, '1ano')) {
                                $numeroPagos = 48;
                            } else {
                                $numeroPagos = 16;
                            }
                        @endphp
                        Amortización ({{ $numeroPagos }} Pagos)
                    </th>
                </tr>
                <tr>
                    <th style="width: 13%;">Crédito</th>
                    <th style="width: 13%;">Garantía</th>
                    <th style="width: 13%;">Comisión</th>
                    <th style="width: 14%;">Efectivo</th>
                    <th style="width: 14%;">Pagos</th>
                    <th style="width: 13%;">Último Pago</th>
                </tr>
            </thead>
            <tbody>
                @if($prestamo->producto === 'grupal')
                    @php
                        $totalCredito = 0;
                        $totalGarantia = 0;
                        $totalComision = 0;
                        $totalEfectivo = 0;
                        $totalPagos = 0;
                        $totalUltimoPago = 0;

                        $tasaDecimal = ($prestamo->tasa_interes ?? 0) / 100;
                    @endphp
                    @foreach($prestamo->clientes as $index => $cliente)
                        @php
                            $credito = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                            $garantiaPercent = $prestamo->garantia ?? 0;
                            $garantiaMonto = $credito * ($garantiaPercent / 100);
                            $comisionMonto = $credito * 0.02; // 2% de comisión
                            $efectivo = $credito - $garantiaMonto - $comisionMonto;

                            // Calcular pagos con interés
                            $interes = $credito * $tasaDecimal;
                            $totalConInteres = $credito + $interes;
                            $montoPorPago = $numeroPagos > 0 ? $totalConInteres / $numeroPagos : 0;

                            // Último pago (simulado - en producción vendría de la BD)
                            $ultimoPago = $montoPorPago;

                            $totalCredito += $credito;
                            $totalGarantia += $garantiaMonto;
                            $totalComision += $comisionMonto;
                            $totalEfectivo += $efectivo;
                            $totalPagos += $montoPorPago;
                            $totalUltimoPago += $ultimoPago;
                        @endphp
                        <tr>
                            <td class="left">{{ strtoupper(trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? ''))) }}</td>
                            <td>{{ number_format($credito, 0) }}</td>
                            <td>{{ number_format($garantiaMonto, 0) }}</td>
                            <td>{{ number_format($comisionMonto, 0) }}</td>
                            <td style="font-weight: bold;">{{ number_format($efectivo, 0) }}</td>
                            <td>{{ number_format($montoPorPago, 0) }}</td>
                            <td>{{ number_format($ultimoPago, 0) }}</td>
                        </tr>
                    @endforeach
                        <tr class="total-row">
                        <td class="right">TOTAL:</td>
                        <td>{{ number_format($totalCredito, 0) }}</td>
                        <td>{{ number_format($totalGarantia, 0) }}</td>
                        <td>{{ number_format($totalComision, 0) }}</td>
                        <td style="color: #4ade80;">{{ number_format($totalEfectivo, 0) }}</td>
                        <td>{{ number_format($totalPagos, 0) }}</td>
                        <td>{{ number_format($totalUltimoPago, 0) }}</td>
                    </tr>
                @else
                    @if($prestamo->cliente)
                        @php
                            $credito = $prestamo->monto_total ?? 0;
                            $garantiaPercent = $prestamo->garantia ?? 0;
                            $garantiaMonto = $credito * ($garantiaPercent / 100);
                            $comisionMonto = $credito * 0.02;
                            $efectivo = $credito - $garantiaMonto - $comisionMonto;

                            // Calcular pagos con interés
                            $tasaDecimal = ($prestamo->tasa_interes ?? 0) / 100;
                            $interes = $credito * $tasaDecimal;
                            $totalConInteres = $credito + $interes;
                            $montoPorPago = $numeroPagos > 0 ? $totalConInteres / $numeroPagos : 0;
                            $ultimoPago = $montoPorPago;
                        @endphp
                        <tr>
                            <td class="left">{{ strtoupper(trim(($prestamo->cliente->nombres ?? '') . ' ' . ($prestamo->cliente->apellido_paterno ?? '') . ' ' . ($prestamo->cliente->apellido_materno ?? ''))) }}</td>
                            <td>{{ number_format($credito, 0) }}</td>
                            <td>{{ number_format($garantiaMonto, 0) }}</td>
                            <td>{{ number_format($comisionMonto, 0) }}</td>
                            <td style="font-weight: bold;">{{ number_format($efectivo, 0) }}</td>
                            <td>{{ number_format($montoPorPago, 0) }}</td>
                            <td>{{ number_format($ultimoPago, 0) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
    </table>

    {{-- Comentarios del comité --}}
    {{-- <div class="section-title">Comentarios del Comité</div>
    <div class="comments-box">{{ $prestamo->comentarios_comite ?? 'No hay comentarios del comité.' }}</div>
 --}}
    {{-- Footer --}}
    <div class="footer-text">
        <div style="margin-bottom: 5px;">Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('h:i a') }}</div>
        <div>Sistema de Gestión de Préstamos - Diner</div>
    </div>
    </div> {{-- Cierre del contenedor --}}

    @unless($forPdf)
    </div> {{-- Cierre del page-wrapper --}}
    @endunless
</body>
</html>
