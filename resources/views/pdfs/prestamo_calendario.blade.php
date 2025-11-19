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
    use Carbon\Carbon;

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

    // Función para mostrar el plazo completo formateado (e.g., "4mesesD" => "4 MESES D")
    function formatearPlazoCompleto($plazo) {
        $plazoNormalizado = strtolower(trim($plazo));

        // Extraer número
        $numero = extraerPlazoNumerico($plazo);

        // Verificar si tiene "D" o "d"
        $tieneD = stripos($plazoNormalizado, 'd') !== false;

        // Casos especiales para un año (maneja tanto "año" como "ano")
        if (stripos($plazoNormalizado, 'año') !== false ||
            stripos($plazoNormalizado, '1año') !== false ||
            stripos($plazoNormalizado, 'ano') !== false ||
            stripos($plazoNormalizado, '1ano') !== false) {
            return "1 AÑO";
        }

        // Formato estándar
        $resultado = $numero . " MESES";

        if ($tieneD) {
            $resultado .= " D";
        }

        return $resultado;
    }

    // Función para calcular el calendario de pagos según las reglas de negocio específicas
    function calcularCalendarioPagos($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $ultimoPago = null, $diaPago = 'martes') {
        $pagos = [];

        // Normalizar plazo y periodicidad
        $plazoNormalizado = strtolower(trim($plazo));
        $periodicidadNormalizada = strtolower(trim($periodicidad));

        // Determinar el caso según el plazo y periodicidad
        $configuracion = determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);

        if (!$configuracion) {
            // Fallback al método anterior si no se reconoce el plazo
            return calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago);
        }

        // Calcular interés e IVA según las reglas de negocio
        $interes = (($monto / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16); // Fallback a 16% si no está configurado
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $monto;

        // Calcular pago regular según el monto total
        $pagoConDecimales = $montoTotal / $configuracion['total_pagos'];
        $pagoRegular = floor($pagoConDecimales); // Parte entera
        $decimales = $pagoConDecimales - $pagoRegular; // Parte decimal

        // Calcular último pago usando la lógica del anexo 1
        // Se suman todos los decimales de los pagos regulares al último pago
        $pagosRegulares = $configuracion['total_pagos'] - 1;
        $ultimoCalculado = $pagoRegular + ($decimales * $configuracion['total_pagos']);        // Mapeo de días en español a inglés para Carbon
        $diasSemana = [
            'lunes' => 'monday',
            'martes' => 'tuesday',
            'miércoles' => 'wednesday',
            'miercoles' => 'wednesday',
            'jueves' => 'thursday',
            'viernes' => 'friday',
            'sábado' => 'saturday',
            'sabado' => 'saturday',
            'domingo' => 'sunday'
        ];

        $diaEnIngles = $diasSemana[strtolower($diaPago)] ?? 'tuesday';

        // Usar la fecha del primer pago como punto de partida (exacta)
        $fecha = clone $fechaPrimerPago;

        for ($i = 1; $i <= $configuracion['total_pagos']; $i++) {
            // Verificar si la fecha cae en día festivo o fin de semana
            $fechaPago = clone $fecha;

            // Si es fin de semana o día festivo, usar el siguiente día hábil
            if ($fechaPago->isWeekend() || \App\Models\Holiday::isHoliday($fechaPago)) {
                $fechaPago = \App\Models\Holiday::getNextBusinessDay($fechaPago);
            }

            // Determinar el monto del pago
            $montoPago = ($i == $configuracion['total_pagos']) ? $ultimoCalculado : $pagoRegular;

            $pagos[] = [
                'numero' => $i,
                'fecha' => $fechaPago->format('d-m-y'),
                'dia_nombre' => $fechaPago->locale('es')->translatedFormat('l'),
                'monto' => round($montoPago)
            ];

            // Avanzar a la siguiente fecha de pago según periodicidad
            if ($periodicidadNormalizada === 'semanal') {
                $fecha->addWeeks(1);
            } elseif ($periodicidadNormalizada === 'catorcenal' || $periodicidadNormalizada === 'quincenal') {
                $fecha->addWeeks(2);
            } else { // mensual
                $fecha->addMonth(1);
                $fecha = $fecha->next($diaEnIngles);
            }
        }

        return $pagos;
    }

    // Función para determinar la configuración de pago según plazo y periodicidad
    function determinarConfiguracionPago($plazo, $periodicidad) {
        $configuraciones = [
            // Caso 1: 4 meses
            '4 meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4meses_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '4 meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4 meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],
            '4meses_quincenal' => ['meses_interes' => 4, 'total_pagos' => 8],

            // Caso 2: 4 meses D (CORREGIDO - agregando variantes sin espacio)
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14], // SIN ESPACIO
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7], // SIN ESPACIO
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7], // SIN ESPACIO

            // Caso 3: 5 meses D
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18], // SIN ESPACIO
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9], // SIN ESPACIO
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9], // SIN ESPACIO

            // Caso 4: 6 meses
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],

            // Caso 5: 1 año (con y sin ñ)
            '1 año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1año_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1ano_semanal' => ['meses_interes' => 12, 'total_pagos' => 48],
            '1 año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_catorcenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1año_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1 ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
            '1ano_quincenal' => ['meses_interes' => 12, 'total_pagos' => 24],
        ];

        $clave = $plazo . '_' . $periodicidad;
        return $configuraciones[$clave] ?? null;
    }

    // Función de fallback para casos no reconocidos (mantiene la lógica anterior)
    function calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago = 'martes') {
        $plazoNumerico = extraerPlazoNumerico($plazo);

        if ($periodicidad === 'semanal') {
            $numeroPagos = $plazoNumerico * 4;
        } elseif ($periodicidad === 'quincenal' || $periodicidad === 'catorcenal') {
            $numeroPagos = $plazoNumerico * 2;
        } else {
            $numeroPagos = $plazoNumerico;
        }

        $pagoBase = ceil($monto / $numeroPagos);
        $pagos = [];

        $diasSemana = [
            'lunes' => 'monday',
            'martes' => 'tuesday',
            'miércoles' => 'wednesday',
            'miercoles' => 'wednesday',
            'jueves' => 'thursday',
            'viernes' => 'friday',
            'sábado' => 'saturday',
            'sabado' => 'saturday',
            'domingo' => 'sunday'
        ];

        $diaEnIngles = $diasSemana[strtolower($diaPago)] ?? 'tuesday';

        // Usar fecha_primer_pago como punto de partida (exacta)
        $fecha = clone $fechaPrimerPago;        for ($i = 1; $i <= $numeroPagos; $i++) {
            $fechaPago = clone $fecha;

            if ($fechaPago->isWeekend() || \App\Models\Holiday::isHoliday($fechaPago)) {
                $fechaPago = \App\Models\Holiday::getNextBusinessDay($fechaPago);
            }

            $pagos[] = [
                'numero' => $i,
                'fecha' => $fechaPago->format('d-m-y'),
                'dia_nombre' => $fechaPago->locale('es')->translatedFormat('l'),
                'monto' => $pagoBase
            ];

            if ($periodicidad === 'semanal') {
                $fecha->addWeeks(1);
            } elseif ($periodicidad === 'quincenal' || $periodicidad === 'catorcenal') {
                $fecha->addWeeks(2);
            } else {
                $fecha->addMonth(1);
                $fecha = $fecha->next($diaEnIngles);
            }
        }

        return $pagos;
    }

    // Función para obtener detalles del cálculo (interés, IVA, monto total)
    function obtenerDetallesCalculo($monto, $tasaInteres, $plazo, $periodicidad) {
        $plazoNormalizado = strtolower(trim($plazo));
        $periodicidadNormalizada = strtolower(trim($periodicidad));

        $configuracion = determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);

        if (!$configuracion) {
            return [
                'interes' => 0,
                'iva' => 0,
                'monto_total' => $monto,
                'configuracion_encontrada' => false
            ];
        }

        $interes = (($monto / 100) * $tasaInteres) * $configuracion['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16); // Fallback a 16% si no está configurado
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $monto;

        return [
            'interes' => round($interes, 2),
            'iva' => round($iva, 2),
            'monto_total' => round($montoTotal, 2),
            'monto_prestamo' => $monto,
            'total_pagos' => $configuracion['total_pagos'],
            'configuracion_encontrada' => true
        ];
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

            $fechaPrimerPago = Carbon::parse($prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega ?? now());
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
                $fechaPrimerPago,
                null, // ya no se usa ultimoPago
                $prestamo->dia_pago ?? 'martes'
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
                <td class="info-value" colspan="3">{{ mb_strtoupper($nombreCompleto, 'UTF-8') }}</td>
            </tr>
            <tr>
                <td class="info-label">ASESOR:</td>
                <td class="info-value">{{ $prestamo->asesor ? mb_strtoupper($prestamo->asesor->name, 'UTF-8') : 'N/A' }}</td>
                <td class="info-label">GARANTÍA:</td>
                <td class="info-value">{{ $prestamo->garantia ?? 0 }}</td>
            </tr>
            <tr>
                <td class="info-label">PLAZO:</td>
                <td class="info-value">{{ formatearPlazoCompleto($prestamo->plazo) }}</td>
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
                    <th style="width: 8%;">N. PAGO</th>
                    <th style="width: 15%;">FECHA</th>
                    <th style="width: 12%;">DIA</th>
                    <th style="width: 15%;">PAGO</th>
                    <th style="width: 18%;">MONTO PAGADO</th>
                    <th style="width: 16%;">FECHA</th>
                    <th style="width: 16%;">FIRMA RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['numero'] }}</td>
                    <td>{{ $pago['fecha'] }}</td>
                    <td>{{ $pago['dia_nombre'] ?? '' }}</td>
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
            $fechaPrimerPago = Carbon::parse($prestamo->fecha_primer_pago ?? $prestamo->fecha_entrega ?? now());

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
                $fechaPrimerPago,
                null, // ya no se usa ultimoPago
                $prestamo->dia_pago ?? 'martes'
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
                <td class="info-value">{{ formatearPlazoCompleto($prestamo->plazo) }}</td>
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
                    <th style="width: 8%;">N. PAGO</th>
                    <th style="width: 15%;">FECHA</th>
                    <th style="width: 12%;">DIA</th>
                    <th style="width: 15%;">PAGO</th>
                    <th style="width: 18%;">MONTO PAGADO</th>
                    <th style="width: 16%;">FECHA</th>
                    <th style="width: 16%;">FIRMA RESPONSABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['numero'] }}</td>
                    <td>{{ $pago['fecha'] }}</td>
                    <td>{{ $pago['dia_nombre'] ?? '' }}</td>
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
