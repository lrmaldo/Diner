<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Calendario de Pagos - Préstamo #{{ $prestamo->id }}</title>
    <style>
        @page {
            size: portrait;
            margin: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }
        .page-break {
            page-break-after: always;
        }
        .logo {
            max-height: 50px;
            vertical-align: middle;
        }
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .header-table td {
            border: none;
            padding: 5px;
            vertical-align: top;
        }
        .title-large {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        .info-section {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-section td {
            padding: 3px 5px;
            font-size: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 35%;
        }
        .info-value {
            width: 65%;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .payments-table th {
            background-color: #c00;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #c00;
        }
        .payments-table td {
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .payments-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin: 30px auto 5px auto;
        }
        .signature-text {
            text-align: center;
            font-size: 10px;
            margin-top: 5px;
        }
        .footer-notes {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #0066cc;
            font-style: italic;
        }
        .footer-notes p {
            margin: 3px 0;
            text-align: center;
        }
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
            $pub = str_replace('\\', '/', public_path('img/logo.JPG'));
            $logoSrc = 'file:///' . ltrim($pub, '/');
        }
    } else {
        $logoSrc = asset('img/logo.JPG');
    }

    // Función para extraer el número de plazo (e.g., "4meses" => 4)
    function extraerPlazoNumerico($plazo) {
        if (is_numeric($plazo)) {
            return (int) $plazo;
        }
        preg_match('/(\d+)/', $plazo, $matches);
        return isset($matches[1]) ? (int) $matches[1] : 1;
    }

    // Función para calcular el calendario de pagos
    function calcularCalendarioPagos($monto, $tasa, $plazo, $periodicidad, $fechaInicio, $ultimoPago) {
        $pagos = [];
        $tasaMensual = $tasa / 100;

        // Extraer el número del plazo (por si viene como "4meses")
        $plazoNumerico = extraerPlazoNumerico($plazo);

        // Calcular el pago según la periodicidad
        if ($periodicidad === 'semanal') {
            $numeroPagos = $plazoNumerico * 4; // 4 semanas por mes
            $diasEntrePagos = 7;
        } elseif ($periodicidad === 'quincenal') {
            $numeroPagos = $plazoNumerico * 2; // 2 quincenas por mes
            $diasEntrePagos = 15;
        } else { // mensual
            $numeroPagos = $plazoNumerico;
            $diasEntrePagos = 30;
        }

        // Calcular pago periódico
        $pagoBase = ceil($monto / $numeroPagos);

        // Generar calendario
        $fecha = clone $fechaInicio;
        for ($i = 1; $i <= $numeroPagos; $i++) {
            $pagos[] = [
                'numero' => $i,
                'fecha' => $fecha->format('d-m-y'),
                'monto' => ($i == $numeroPagos) ? $ultimoPago : $pagoBase
            ];
            $fecha->modify("+{$diasEntrePagos} days");
        }

        return $pagos;
    }
@endphp

@unless($forPdf)
<div class="no-print" style="margin-bottom:12px;text-align:right;padding:10px;">
    <button onclick="window.print()" style="padding:8px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer;margin-right:5px;">Imprimir</button>
    <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'calendario']) }}" style="padding:8px 12px;border-radius:6px;border:1px solid #2563eb;background:#2563eb;color:#fff;text-decoration:none;display:inline-block">Descargar PDF</a>
</div>
@endunless

@if($prestamo->producto === 'grupal')
    @foreach($prestamo->clientes as $index => $cliente)
        @php
            $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
            if ($montoCliente == 0) continue;

            $fechaInicio = $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega ?? now();
            $ultimoPagoNum = null;

            // Extraer el número del plazo
            $plazoNumerico = extraerPlazoNumerico($prestamo->plazo);

            // Calcular el último pago basado en el último número de pago del préstamo
            if ($prestamo->periodicidad === 'semanal') {
                $numeroPagos = $plazoNumerico * 4;
            } elseif ($prestamo->periodicidad === 'quincenal') {
                $numeroPagos = $plazoNumerico * 2;
            } else {
                $numeroPagos = $plazoNumerico;
            }

            // Obtener el último pago de la tabla (si existe)
            $ultimoPagoNum = $numeroPagos;

            $pagos = calcularCalendarioPagos(
                $montoCliente,
                $prestamo->tasa_interes ?? 0,
                $prestamo->plazo,
                $prestamo->periodicidad ?? 'semanal',
                $fechaInicio,
                ceil($montoCliente / $numeroPagos) // último pago
            );

            $nombreCompleto = trim(($cliente->nombres ?? $cliente->nombre ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? ''));
        @endphp

        {{-- Header con logo --}}
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                </td>
            </tr>
        </table>

        <div class="title-large">CALENDARIO DE PAGOS</div>

        {{-- Información del grupo y cliente --}}
        <table class="info-section" cellpadding="0" cellspacing="0">
            <tr>
                <td class="info-label">GRUPO:</td>
                <td class="info-value">{{ $prestamo->id }}</td>
                <td class="info-label">MONTO:</td>
                <td class="info-value">${{ number_format($montoCliente, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">NOMBRE:</td>
                <td class="info-value" colspan="3">{{ strtoupper($nombreCompleto) }}</td>
            </tr>
            <tr>
                <td class="info-label">ASESOR:</td>
                <td class="info-value">{{ $prestamo->asesor ? strtoupper($prestamo->asesor->name) : 'N/A' }}</td>
                <td class="info-label">GARANTÍA:</td>
                <td class="info-value">{{ $prestamo->garantia ?? 0 }}</td>
            </tr>
            <tr>
                <td class="info-label">PLAZO:</td>
                <td class="info-value">{{ extraerPlazoNumerico($prestamo->plazo) }} MESES</td>
                <td class="info-label">SEGURO DEL CRÉDITO:</td>
                <td class="info-value">{{ number_format(($montoCliente * 0.01), 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">PERIODO DE PAGOS:</td>
                <td class="info-value">{{ strtoupper($prestamo->periodicidad ?? 'SEMANAL') }}</td>
                <td class="info-label">EFECTIVO:</td>
                <td class="info-value">${{ number_format($montoCliente - ($montoCliente * 0.01), 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">NÚMERO DE PAGOS:</td>
                <td class="info-value">{{ count($pagos) }}</td>
                <td class="info-label">PAGO:</td>
                <td class="info-value">${{ number_format($pagos[0]['monto'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label"></td>
                <td class="info-value"></td>
                <td class="info-label">ÚLTIMO PAGO:</td>
                <td class="info-value">${{ number_format($pagos[count($pagos)-1]['monto'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label"></td>
                <td class="info-value"></td>
                <td class="info-label">DÍAS DE PAGO:</td>
                <td class="info-value">{{ strtoupper($prestamo->dia_pago ?? 'MARTES') }}</td>
            </tr>
        </table>

        {{-- Tabla de pagos --}}
        <table class="payments-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 10%;">N. PAGO</th>
                    <th style="width: 20%;">FECHA</th>
                    <th style="width: 20%;">PAGO</th>
                    <th style="width: 20%;">MONTO PAGADO</th>
                    <th style="width: 15%;">FECHA</th>
                    <th style="width: 15%;">FIRMA RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['numero'] }}</td>
                    <td>{{ $pago['fecha'] }}</td>
                    <td>{{ $pago['monto'] }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endforeach
            </tbody>
        </table>



        {{-- Footer con notas --}}
        <div class="footer-notes">
            <p><strong>GRACIAS POR SER CLIENTE DINER</strong></p>
            <p><strong>CUIDA TU HISTORIAL PAGANDO PUNTUALMENTE</strong></p>
            <p><strong>PARA QUEJAS, ESCRÍBENOS AL: 9991097214, EN DINER NOS INTERESA ESTÁ BIEN CONTIGO</strong></p>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@else
    {{-- Para préstamos individuales --}}
    @php
        $cliente = $prestamo->cliente;
        $montoCliente = $prestamo->monto_total ?? 0;

        if ($montoCliente > 0 && $cliente) {
            $fechaInicio = $prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega ?? now();

            // Extraer el número del plazo
            $plazoNumerico = extraerPlazoNumerico($prestamo->plazo);

            if ($prestamo->periodicidad === 'semanal') {
                $numeroPagos = $plazoNumerico * 4;
            } elseif ($prestamo->periodicidad === 'quincenal') {
                $numeroPagos = $plazoNumerico * 2;
            } else {
                $numeroPagos = $plazoNumerico;
            }

            $pagos = calcularCalendarioPagos(
                $montoCliente,
                $prestamo->tasa_interes ?? 0,
                $prestamo->plazo,
                $prestamo->periodicidad ?? 'semanal',
                $fechaInicio,
                ceil($montoCliente / $numeroPagos)
            );

            $nombreCompleto = trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellido_paterno ?? '') . ' ' . ($cliente->apellido_materno ?? ''));
        }
    @endphp

    @if($montoCliente > 0 && $cliente)
        {{-- Header con logo --}}
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                </td>
            </tr>
        </table>

        <div class="title-large">CALENDARIO DE PAGOS</div>

        {{-- Información del préstamo individual --}}
        <table class="info-section" cellpadding="0" cellspacing="0">
            <tr>
                <td class="info-label">GRUPO:</td>
                <td class="info-value">{{ $prestamo->id }}</td>
                <td class="info-label">MONTO:</td>
                <td class="info-value">${{ number_format($montoCliente, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">NOMBRE:</td>
                <td class="info-value" colspan="3">{{ strtoupper($nombreCompleto) }}</td>
            </tr>
            <tr>
                <td class="info-label">ASESOR:</td>
                <td class="info-value">{{ $prestamo->asesor ? strtoupper($prestamo->asesor->name) : 'N/A' }}</td>
                <td class="info-label">GARANTÍA:</td>
                <td class="info-value">{{ $prestamo->garantia ?? 0 }}</td>
            </tr>
            <tr>
                <td class="info-label">PLAZO:</td>
                <td class="info-value">{{ extraerPlazoNumerico($prestamo->plazo) }} MESES</td>
                <td class="info-label">SEGURO DEL CRÉDITO:</td>
                <td class="info-value">{{ number_format(($montoCliente * 0.01), 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">PERIODO DE PAGOS:</td>
                <td class="info-value">{{ strtoupper($prestamo->periodicidad ?? 'SEMANAL') }}</td>
                <td class="info-label">EFECTIVO:</td>
                <td class="info-value">${{ number_format($montoCliente - ($montoCliente * 0.01), 0) }}</td>
            </tr>
            <tr>
                <td class="info-label">NÚMERO DE PAGOS:</td>
                <td class="info-value">{{ count($pagos) }}</td>
                <td class="info-label">PAGO:</td>
                <td class="info-value">${{ number_format($pagos[0]['monto'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label"></td>
                <td class="info-value"></td>
                <td class="info-label">ÚLTIMO PAGO:</td>
                <td class="info-value">${{ number_format($pagos[count($pagos)-1]['monto'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td class="info-label"></td>
                <td class="info-value"></td>
                <td class="info-label">DÍAS DE PAGO:</td>
                <td class="info-value">{{ strtoupper($prestamo->dia_pago ?? 'MARTES') }}</td>
            </tr>
        </table>

        {{-- Tabla de pagos --}}
        <table class="payments-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 10%;">N. PAGO</th>
                    <th style="width: 20%;">FECHA</th>
                    <th style="width: 20%;">PAGO</th>
                    <th style="width: 20%;">MONTO PAGADO</th>
                    <th style="width: 15%;">FECHA</th>
                    <th style="width: 15%;">FIRMA RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['numero'] }}</td>
                    <td>{{ $pago['fecha'] }}</td>
                    <td>{{ $pago['monto'] }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endforeach
            </tbody>
        </table>




        {{-- Footer con notas --}}
        <div class="footer-notes">
            <p><strong>GRACIAS POR SER CLIENTE DINER</strong></p>
            <p><strong>CUIDA TU HISTORIAL PAGANDO PUNTUALMENTE</strong></p>
            <p><strong>PARA QUEJAS, ESCRÍBENOS AL: 9991097214, EN DINER NOS INTERESA ESTÁ BIEN CONTIGO</strong></p>
        </div>
    @endif
@endif

</body>
</html>
