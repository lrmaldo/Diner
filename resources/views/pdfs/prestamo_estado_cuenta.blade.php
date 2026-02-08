<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Estado de Cuenta #{{ $prestamo->id }}</title>
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
            font-size: 10px;
            background-color: white;
        }
        /* Estilos para vista HTML (pantalla) */
        @media screen {
            body {
                background-color: #f3f4f6;
                padding: 20px;
                min-height: 100vh;
            }
            .page-wrapper {
                max-width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background-color: white;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
                overflow: hidden;
                padding: 0;
            }
        }
        .container {
            background-color: white;
            padding: 8px;
            box-shadow: none;
            border-radius: 0;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        /* Membrete */
        .header-logo {
            display: flex;
            align-items: center;
            gap: 65px;
            margin-bottom: 8px;
            padding: 8px 0;
            border-bottom: 2px solid #e02424;
        }
        .logo {
            max-height: 60px;
            width: auto;
            min-width: 80px;
        }
        .header-text {
            flex: 1;
            margin-right: 100px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        .company-info {
            font-size: 9px;
            color: #666;
            margin: 2px 0 0 0;
        }
        .title-section {
            text-align: center;
            margin: 6px 0 8px 0;
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }
        /* Información del préstamo */
        .info-header {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            border: 1px solid #333;
            border-collapse: collapse;
        }
        .info-header-item {
            display: table-cell;
            width: 25%;
            padding: 4px 6px;
            border-right: 1px solid #333;
            font-size: 9px;
            vertical-align: top;
        }
        .info-header-item:last-child {
            border-right: none;
        }
        .info-header-label {
            font-weight: bold;
            margin-bottom: 2px;
            display: block;
        }
        .info-header-value {
            font-size: 10px;
            display: block;
        }
        /* Tabla de detalles del crédito */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        .details-table th {
            background-color: #333;
            color: white;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #333;
            font-weight: bold;
        }
        .details-table td {
            padding: 3px 3px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .details-table td.left {
            text-align: left;
        }
        .details-table tr:first-child td {
            background-color: #f9fafb;
            font-weight: bold;
        }
        /* Tabla de recuperaciones */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin: 6px 0 4px 0;
            padding: 4px 0;
            border-bottom: 1px solid #333;
            color: #1f2937;
        }
        .recuperaciones-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
        }
        .recuperaciones-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 3px 2px;
            text-align: center;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .recuperaciones-table td {
            padding: 3px 2px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .recuperaciones-table td.left {
            text-align: left;
        }
        /* Tabla de saldos totales */
        .saldos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        .saldos-table th {
            background-color: #333;
            color: white;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #333;
            font-weight: bold;
        }
        .saldos-table td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #f9fafb;
            font-weight: bold;
        }
        .saldos-table td.label {
            text-align: left;
            font-weight: bold;
        }
        /* Tabla de detalles de saldos */
        .detalle-saldos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
        }
        .detalle-saldos-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 3px 2px;
            text-align: center;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .detalle-saldos-table td {
            padding: 3px 2px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .detalle-saldos-table td.left {
            text-align: left;
        }
        .no-print {
            display: none;
        }
        .btn-group {
            margin-bottom: 12px;
            text-align: right;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #ccc;
        }
        /* Estilos para impresión y PDF */
        @media print {
            * {
                background: white !important;
                box-shadow: none !important;
            }
            body {
                background-color: white !important;
                padding: 0 !important;
                margin: 0 !important;
                color: black;
            }
            .page-wrapper {
                max-width: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            .container {
                box-shadow: none !important;
                padding: 8px;
                margin: 0 !important;
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
            .btn-group {
                display: none !important;
            }
        }
    </style>
</head>
<body style="{{ ($forPdf ?? false) ? 'background: white !important; padding: 0; margin: 0;' : '' }}">
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

    // Función para calcular el calendario de pagos según las reglas de negocio específicas
    function calcularCalendarioPagos($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $ultimoPago = null, $diaPago = 'martes') {
        $plazoNormalizado = strtolower(trim($plazo));
        $periodicidadNormalizada = strtolower(trim($periodicidad));
        
        $config = determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);
        
        if (!$config) {
            return calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago);
        }

        // Calcular monto total usando la misma lógica que el calendario
        $interes = (($monto / 100) * $tasaInteres) * $config['meses_interes'];
        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
        $iva = ($interes / 100) * $ivaPorcentaje;
        $montoTotal = $interes + $iva + $monto;
        
        $numeroPagos = $config['total_pagos'];
        $montoPorPago = $montoTotal / $numeroPagos;

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        $diasFeriados = \App\Models\Holiday::whereYear('date', $fechaActual->year)
            ->orWhereYear('date', $fechaActual->copy()->addYear()->year)
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Determinar intervalo en días según periodicidad
        $intervaloDias = match(strtolower($periodicidadNormalizada)) {
            'semanal', 'semana', 'weekly' => 7,
            'catorcenal', 'quincenal', 'quincena', 'biweekly' => 14,
            'mensual', 'mes', 'monthly' => 30,
            default => 7
        };

        // Calcular monto por pago usando lógica de decimales (igual que en detalle de crédito y caja)
        $pagoConDecimales = $montoTotal / $numeroPagos;
        $montoPorPagoBase = floor($pagoConDecimales); // Parte entera
        $decimales = $pagoConDecimales - $montoPorPagoBase;
        $montoUltimoPago = $montoPorPagoBase + ($decimales * $numeroPagos);

        for ($i = 1; $i <= $numeroPagos; $i++) {
            if ($i === 1) {
                $fechaPago = $fechaActual->copy();
            } else {
                $fechaPago = $fechaActual->copy()->addDays($intervaloDias);
                
                while (in_array($fechaPago->format('Y-m-d'), $diasFeriados) || $fechaPago->dayOfWeek === Carbon::SUNDAY) {
                    $fechaPago->addDay();
                }
            }

            if ($i === $numeroPagos && $ultimoPago) {
                $fechaPago = Carbon::parse($ultimoPago);
            }

            // El último pago lleva el monto base más los decimales acumulados
            $montoPago = ($i === $numeroPagos) ? $montoUltimoPago : $montoPorPagoBase;

            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaPago->format('d-m-y'),
                'monto' => $montoPago,
            ];

            $fechaActual = $fechaPago->copy();
        }

        return $calendario;
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
            '16 semanas_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '16semanas_semanal' => ['meses_interes' => 4, 'total_pagos' => 16],
            '24 semanas_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '24semanas_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],

            // Caso 2: 4 meses D
            '4 meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4meses d_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4mesesd_semanal' => ['meses_interes' => 4, 'total_pagos' => 14],
            '4 meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_catorcenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4 meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4meses d_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],
            '4mesesd_quincenal' => ['meses_interes' => 4, 'total_pagos' => 7],

            // Caso 3: 5 meses D
            '5 meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5meses d_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5mesesd_semanal' => ['meses_interes' => 5, 'total_pagos' => 18],
            '5 meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_catorcenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5 meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5meses d_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],
            '5mesesd_quincenal' => ['meses_interes' => 5, 'total_pagos' => 9],

            // Caso 4: 6 meses
            '6 meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6meses_semanal' => ['meses_interes' => 6, 'total_pagos' => 24],
            '6 meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_catorcenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6 meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],
            '6meses_quincenal' => ['meses_interes' => 6, 'total_pagos' => 12],

            // Caso 5: 1 año
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

    // Función de fallback para casos no reconocidos
    function calcularCalendarioBasico($monto, $tasaInteres, $plazo, $periodicidad, $fechaPrimerPago, $diaPago = 'martes') {
        $plazoNumerico = extraerPlazoNumerico($plazo);
        $periodicidadNormalizada = strtolower(trim($periodicidad));
        
        $esSemanal = in_array($periodicidadNormalizada, ['semanal', 'semana', 'weekly']);
        $esQuincenal = in_array($periodicidadNormalizada, ['quincenal', 'quincena', 'biweekly']);
        $esMensual = in_array($periodicidadNormalizada, ['mensual', 'mes', 'monthly']);

        if ($esSemanal) {
            $numeroPagos = $plazoNumerico * 4;
            $intervaloDias = 7;
        } elseif ($esQuincenal) {
            $numeroPagos = $plazoNumerico * 2;
            $intervaloDias = 14;
        } elseif ($esMensual) {
            $numeroPagos = $plazoNumerico;
            $intervaloDias = 30;
        } else {
            $numeroPagos = $plazoNumerico;
            $intervaloDias = 30;
        }

        $montoTotal = $monto + ($monto * ($tasaInteres / 100));
        $montoTotal = $montoTotal + ($montoTotal * 0.16);
        $montoPorPago = $montoTotal / $numeroPagos;

        $calendario = [];
        $fechaActual = Carbon::parse($fechaPrimerPago);

        for ($i = 1; $i <= $numeroPagos; $i++) {
            $calendario[] = [
                'numero' => $i,
                'fecha' => $fechaActual->format('d-m-y'),
                'monto' => round($montoPorPago, 2),
            ];
            $fechaActual->addDays($intervaloDias);
        }

        return $calendario;
    }

    // Función para calcular la comisión (seguro) según el monto del crédito
    function calcularComision($monto) {
        if ($monto < 3000) {
            return 0;
        } elseif ($monto >= 3000 && $monto <= 10000) {
            return 50;
        } elseif ($monto > 10000 && $monto <= 20000) {
            return 100;
        } elseif ($monto > 20000 && $monto <= 30000) {
            return 150;
        } elseif ($monto > 30000 && $monto <= 40000) {
            return 200;
        } elseif ($monto > 40000 && $monto <= 50000) {
            return 250;
        } elseif ($monto > 50000 && $monto <= 60000) {
            return 300;
        } elseif ($monto > 60000 && $monto <= 70000) {
            return 350;
        } elseif ($monto > 70000 && $monto <= 80000) {
            return 400;
        } elseif ($monto > 80000 && $monto <= 90000) {
            return 450;
        } elseif ($monto > 90000 && $monto <= 100000) {
            return 500;
        } else {
            return 500; // Para montos mayores a 100,000
        }
    }
@endphp

    @if(!$forPdf)
    <div class="btn-group no-print">
        <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
        <a href="{{ route('prestamos.print.download', ['prestamo' => $prestamo->id, 'type' => 'estado_cuenta']) }}" class="btn btn-primary">Descargar PDF</a>
    </div>
    <div class="page-wrapper">
    @endif

    <div class="container" style="{{ $forPdf ? 'background: white; padding: 8px;' : '' }}">
        {{-- Membrete --}}
        <div class="header-logo">
            <div style="width: 80px; flex: 0 0 80px;">
                <img src="{{ $logoSrc }}" alt="Logo" class="logo">
            </div>
            <div class="header-text">
                <p class="company-name"></p>
                <p class="company-info">Sistema de Gestión de Préstamos</p>
            </div>
            <div style="text-align: right; flex: 1;">
                <p style="font-size: 11px; font-weight: bold; margin: 0 0 2px 0; color: #1f2937;">ESTADO DE CUENTA</p>
                <p style="font-size: 9px; margin: 0 0 2px 0; color: #666;">Préstamo #{{ str_pad($prestamo->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p style="font-size: 8px; margin: 0; color: #999;">Generado: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Información de préstamo --}}
        <div class="info-header">
            <div class="info-header-item">
                <div class="info-header-label">Asesor: {{ $prestamo->asesor->name ?? 'N/A' }}</div>
                <div class="info-header-value">
                    Plazo: 
                    @php
                        $plazoFormateado = $prestamo->plazo;
                        if ($plazoFormateado) {
                            $plazoNormalizado = strtolower(trim($plazoFormateado));
                            $numero = preg_match('/(\d+)/', $plazoFormateado, $matches) ? (int)$matches[1] : 1;
                            $tieneD = stripos($plazoNormalizado, 'd') !== false;

                            if (stripos($plazoNormalizado, 'año') !== false ||
                                stripos($plazoNormalizado, '1año') !== false ||
                                stripos($plazoNormalizado, 'ano') !== false ||
                                stripos($plazoNormalizado, '1ano') !== false) {
                                $plazoFormateado = "1 AÑO";
                            } else {
                                $plazoFormateado = $numero . " MESES" . ($tieneD ? " D" : "");
                            }
                        }
                    @endphp
                    {{ $plazoFormateado ?? 'N/A' }}
                </div>
            </div>
            <div class="info-header-item">
                <div class="info-header-label">Producto: {{ ucfirst($prestamo->producto) }}</div>
                <div class="info-header-value">Período de pago: SEMANAL</div>
            </div>
            <div class="info-header-item">
                <div class="info-header-label">Entregado: {{ $prestamo->fecha_entrega ? $prestamo->fecha_entrega->format('d-m-y') : 'N/A' }}</div>
                <div class="info-header-value">
                    @php
                        $plazoNormHeader = strtolower(trim($prestamo->plazo ?? '4meses'));
                        $periodicidadNormHeader = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                        $configHeader = determinarConfiguracionPago($plazoNormHeader, $periodicidadNormHeader);
                        
                        if ($configHeader) {
                            $numPagosHeader = $configHeader['total_pagos'];
                        } else {
                            $plazoNumHeader = extraerPlazoNumerico($prestamo->plazo);
                            if (in_array($periodicidadNormHeader, ['semanal', 'semana', 'weekly'])) {
                                $numPagosHeader = $plazoNumHeader * 4;
                            } elseif (in_array($periodicidadNormHeader, ['quincenal', 'quincena', 'biweekly', 'catorcenal'])) {
                                $numPagosHeader = $plazoNumHeader * 2;
                            } else {
                                $numPagosHeader = $plazoNumHeader;
                            }
                        }
                    @endphp
                    Número de pagos: {{ $numPagosHeader }}
                </div>
            </div>
            <div class="info-header-item">
                <div class="info-header-label">Garantía devuelta:</div>
                <div class="info-header-value">${{ number_format(abs($prestamo->pagos->where('tipo_pago', 'devolucion_garantia')->sum('monto')), 2) }}</div>
                <div class="info-header-label" style="margin-top: 6px;">Otros ingresos:</div>
                <div class="info-header-value"></div>
            </div>
        </div>

        {{-- Tabla de detalles del crédito --}}
        <table class="details-table">
            <thead>
                <tr>
                    <th>Total Crédito</th>
                    <th>Total Garantía</th>
                    <th>Total seguro</th>
                    <th>Total Efectivo</th>
                    <th>Total Tasa</th>
                    <th>Total Interés</th>
                    <th>Tasa Iva</th>
                    <th>Iva</th>
                    <th>Vencimiento</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Calcular totales
                    $totalCredito = 0;
                    $totalGarantia = 0;
                    $totalSeguro = 0;
                    $totalEfectivo = 0;
                    $totalInteres = 0;
                    $totalIva = 0;
                    
                    if ($prestamo->producto === 'grupal') {
                        foreach ($prestamo->clientes as $cliente) {
                            $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                            $garantiaCliente = $montoCliente * (($prestamo->garantia ?? 0) / 100);
                            $seguroCliente = calcularComision($montoCliente);
                            $efectivoCliente = $montoCliente - $garantiaCliente - $seguroCliente;
                            
                            // Calcular interés usando la misma fórmula del calendario
                            $plazoNormalizado = strtolower(trim($prestamo->plazo ?? '4meses'));
                            $periodicidadNormalizada = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                            $config = determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);
                            
                            if ($config) {
                                $mesesInteres = $config['meses_interes'];
                            } else {
                                $mesesInteres = 4; // fallback
                            }
                            
                            $interesCliente = (($montoCliente / 100) * ($prestamo->tasa_interes ?? 0)) * $mesesInteres;
                            $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
                            $ivaCliente = ($interesCliente / 100) * $ivaPorcentaje;
                            
                            $totalCredito += $montoCliente;
                            $totalGarantia += $garantiaCliente;
                            $totalSeguro += $seguroCliente;
                            $totalEfectivo += $efectivoCliente;
                            $totalInteres += $interesCliente;
                            $totalIva += $ivaCliente;
                        }
                    } else {
                        $montoCliente = $prestamo->monto_total ?? 0;
                        $garantiaCliente = $montoCliente * (($prestamo->garantia ?? 0) / 100);
                        $seguroCliente = calcularComision($montoCliente);
                        $efectivoCliente = $montoCliente - $garantiaCliente - $seguroCliente;
                        
                        // Calcular interés usando la misma fórmula del calendario
                        $plazoNormalizado = strtolower(trim($prestamo->plazo ?? '4meses'));
                        $periodicidadNormalizada = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                        $config = determinarConfiguracionPago($plazoNormalizado, $periodicidadNormalizada);
                        
                        if ($config) {
                            $mesesInteres = $config['meses_interes'];
                        } else {
                            $mesesInteres = 4; // fallback
                        }
                        
                        $interesCliente = (($montoCliente / 100) * ($prestamo->tasa_interes ?? 0)) * $mesesInteres;
                        $ivaPorcentaje = \App\Models\Configuration::get('iva_percentage', 16);
                        $ivaCliente = ($interesCliente / 100) * $ivaPorcentaje;
                        
                        $totalCredito = $montoCliente;
                        $totalGarantia = $garantiaCliente;
                        $totalSeguro = $seguroCliente;
                        $totalEfectivo = $efectivoCliente;
                        $totalInteres = $interesCliente;
                        $totalIva = $ivaCliente;
                    }
                    
                    // Calcular calendario para obtener fecha de vencimiento
                    $montoBaseCalc = $prestamo->monto_total ?? 0;
                    $tasaInteresCalc = $prestamo->tasa_interes ?? 0;
                    $plazoCalc = $prestamo->plazo ?? '4meses';
                    $periodicidadCalc = $prestamo->periodicidad ?? 'semanal';
                    $fechaPrimerPagoCalc = $prestamo->fecha_primer_pago ?? $prestamo->fecha_autorizacion ?? now();
                    
                    $ultimoPagoCalc = null;
                    try {
                        $ultimoPagoCalc = $prestamo->ultimo_pago ?? null;
                    } catch (\Exception $e) {
                        $ultimoPagoCalc = null;
                    }
                    
                    $calendarioTemp = calcularCalendarioPagos(
                        $montoBaseCalc,
                        $tasaInteresCalc,
                        $plazoCalc,
                        $periodicidadCalc,
                        $fechaPrimerPagoCalc,
                        $ultimoPagoCalc,
                        'martes'
                    );
                    
                    // Obtener fecha de vencimiento (último pago del calendario)
                    $fechaVencimiento = '';
                    if (!empty($calendarioTemp)) {
                        $ultimoPagoCalendario = end($calendarioTemp);
                        $fechaVencimiento = $ultimoPagoCalendario['fecha'];
                    }
                @endphp
                <tr>
                    <td>${{ number_format($totalCredito, 0) }}</td>
                    <td>${{ number_format($totalGarantia, 0) }}</td>
                    <td>${{ number_format($totalSeguro, 0) }}</td>
                    <td>${{ number_format($totalEfectivo, 0) }}</td>
                    <td>{{ number_format((float)($prestamo->tasa_interes ?? 0), 1) }}%</td>
                    <td>${{ number_format($totalInteres, 0) }}</td>
                    <td>{{ number_format((float)\App\Models\Configuration::get('iva_percentage', 16), 1) }}%</td>
                    <td>${{ number_format($totalIva, 0) }}</td>
                    <td>{{ $fechaVencimiento }}</td>
                </tr>
             
            </tbody>
        </table>

        {{-- Tabla con detalles por cliente --}}
        <table class="details-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Crédito</th>
                    <th>Garantía</th>
                    <th>seguro</th>
                    <th>Efectivo</th>
                    <th>Tasa</th>
                    <th>Interés</th>
                    <th>Tasa Iva</th>
                    <th>Iva</th>
                </tr>
            </thead>
            <tbody>
                @if($prestamo->producto === 'grupal')
                    @foreach($prestamo->clientes as $cliente)
                        @php
                            $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                            $garantiaCliente = $montoCliente * (($prestamo->garantia ?? 0) / 100);
                            $seguroCliente = calcularComision($montoCliente);
                            $efectivoCliente = $montoCliente - $garantiaCliente - $seguroCliente;
                            $tasaCliente = $prestamo->tasa_interes ?? 0;
                            
                            // Calcular interés usando la misma fórmula del calendario
                            $plazoNorm = strtolower(trim($prestamo->plazo ?? '4meses'));
                            $periodicidadNorm = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                            $configCliente = determinarConfiguracionPago($plazoNorm, $periodicidadNorm);
                            
                            if ($configCliente) {
                                $mesesInteresCliente = $configCliente['meses_interes'];
                            } else {
                                $mesesInteresCliente = 4;
                            }
                            
                            $interesCliente = (($montoCliente / 100) * $tasaCliente) * $mesesInteresCliente;
                            $ivaPorcentajeCliente = \App\Models\Configuration::get('iva_percentage', 16);
                            $ivaCliente = ($interesCliente / 100) * $ivaPorcentajeCliente;
                            
                            // Calcular monto exigible (Monto por pago) usando la misma lógica que detalles
                            if ($configCliente) {
                                $totalConInteresesCliente = $montoCliente + $interesCliente + $ivaCliente;
                                $pagoConDecimalesCliente = $totalConInteresesCliente / $configCliente['total_pagos'];
                                $montoExigibleCliente = floor($pagoConDecimalesCliente);
                            } else {
                                $montoExigibleCliente = 0; // Fallback
                            }
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($garantiaCliente, 0) }}</td>
                            <td>{{ number_format($seguroCliente, 0) }}</td>
                            <td>{{ number_format($efectivoCliente, 0) }}</td>
                            <td>{{ number_format($tasaCliente, 0) }}%</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>{{ number_format((float)$ivaPorcentajeCliente, 1) }}%</td>
                            <td>{{ number_format($ivaCliente, 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    @if($prestamo->cliente)
                        @php
                            $montoCliente = $prestamo->monto_total ?? 0;
                            $garantiaCliente = $montoCliente * (($prestamo->garantia ?? 0) / 100);
                            $seguroCliente = calcularComision($montoCliente);
                            $efectivoCliente = $montoCliente - $garantiaCliente - $seguroCliente;
                            $tasaCliente = $prestamo->tasa_interes ?? 0;
                            
                            // Calcular interés usando la misma fórmula del calendario
                            $plazoNorm = strtolower(trim($prestamo->plazo ?? '4meses'));
                            $periodicidadNorm = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                            $configCliente = determinarConfiguracionPago($plazoNorm, $periodicidadNorm);
                            
                            if ($configCliente) {
                                $mesesInteresCliente = $configCliente['meses_interes'];
                            } else {
                                $mesesInteresCliente = 4;
                            }
                            
                            $interesCliente = (($montoCliente / 100) * $tasaCliente) * $mesesInteresCliente;
                            $ivaPorcentajeCliente = \App\Models\Configuration::get('iva_percentage', 16);
                            $ivaCliente = ($interesCliente / 100) * $ivaPorcentajeCliente;

                            // Calcular monto exigible
                            if ($configCliente) {
                                $totalConInteresesCliente = $montoCliente + $interesCliente + $ivaCliente;
                                $pagoConDecimalesCliente = $totalConInteresesCliente / $configCliente['total_pagos'];
                                $montoExigibleCliente = floor($pagoConDecimalesCliente);
                            } else {
                                $montoExigibleCliente = 0; 
                            }
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($garantiaCliente, 0) }}</td>
                            <td>{{ number_format($seguroCliente, 0) }}</td>
                            <td>{{ number_format($efectivoCliente, 0) }}</td>
                            <td>{{ number_format($tasaCliente, 0) }}%</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>{{ number_format((float)$ivaPorcentajeCliente, 1) }}%</td>
                            <td>{{ number_format($ivaCliente, 0) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>

        {{-- Detalles de recuperaciones --}}
        <div class="section-title">Detalles de recuperaciones</div>
        <table class="recuperaciones-table">
            <thead>
                <tr>
                    <th>Vencimiento</th>
                    <th>Fecha de pago</th>
                    <th colspan="3">Efectivo</th>
                    <th colspan="5">Multas</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Exigible</th>
                    <th>Recuperado</th>
                    <th>Garantía</th>
                    <th>Penalización</th>
                    <th>Moratorio</th>
                    <th>Condonado</th>
                    <th>Recuperado</th>
                    <th>Garantía</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Calcular exigible: capital + interés + iva
                    $exigibleTotal = $totalCredito + $totalInteres + $totalIva;
                    
                    // Calcular recuperado: suma de todos los pagos realizados
                    $recuperadoTotal = $prestamo->pagos()->sum('monto') - $prestamo->pagos()->sum('moratorio_pagado');
                    
                    // Calcular moratorio pagado
                    $moratorioRecuperado = $prestamo->pagos()->sum('moratorio_pagado');
                    
                    // Garantía pendiente para futuro
                    $garantiaRecuperaciones = 0;
                    
                    // Penalización: suma de multas pagadas (futuro)
                    $penalizacionTotal = 0;

                    // Obtener la fecha del último pago si existe
                    $ultimoPagoRealizado = $prestamo->pagos()->latest('fecha_pago')->first();
                    $fechaUltimoPago = $ultimoPagoRealizado ? $ultimoPagoRealizado->fecha_pago->format('d-m-y') : '';
                @endphp
                <tr>
                    <td>{{ $fechaVencimiento }}</td>
                    <td>{{ $fechaUltimoPago }}</td>
                    <td>{{ number_format($exigibleTotal, 0) }}</td>
                    <td>{{ number_format($recuperadoTotal, 0) }}</td>
                    <td>{{ number_format($garantiaRecuperaciones, 0) }}</td>
                    <td>{{ number_format($penalizacionTotal, 0) }}</td>
                    <td>{{ number_format($moratorioRecuperado, 0) }}</td>
                    <td>0</td>
                    <td>{{ number_format($moratorioRecuperado, 0) }}</td>
                    <td>0</td>
                </tr>
            </tbody>
        </table>

        {{-- Tabla de pagos --}}
        <div class="section-title">Número de pagos</div>
        <table class="recuperaciones-table">
            <thead>
                <tr>
                    <th>Número de pago</th>
                    <th>Fecha de vencimiento</th>
                    <th>Fecha de pago</th>
                    <th>Exigible</th>
                    <th>Pagado en efectivo</th>
                    <th>Pagado con garantía</th>
                    <th>Multas recuperadas</th>
                    <th>Multas con garantía</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Calcular calendario de pagos
                    // Para préstamos grupales, usar el monto total del grupo
                    // Para individuales, usar el monto del préstamo
                    $montoBase = $prestamo->monto_total ?? 0;
                    
                    $tasaInteres = $prestamo->tasa_interes ?? 0;
                    $plazo = $prestamo->plazo ?? '4meses';
                    $periodicidad = $prestamo->periodicidad ?? 'semanal';
                    $fechaPrimerPago = $prestamo->fecha_primer_pago ?? $prestamo->fecha_autorizacion ?? now();
                    
                    // Buscar si existe el campo ultimo_pago en el préstamo
                    $ultimoPago = null;
                    try {
                        $ultimoPago = $prestamo->ultimo_pago ?? null;
                    } catch (\Exception $e) {
                        $ultimoPago = null;
                    }
                    
                    $calendarioPagos = calcularCalendarioPagos(
                        $montoBase,
                        $tasaInteres,
                        $plazo,
                        $periodicidad,
                        $fechaPrimerPago,
                        $ultimoPago,
                        'martes'
                    );

                    // Obtener todos los pagos registrados del préstamo ordenados por fecha
                    $todosLosPagosRaw = $prestamo->pagos()
                        ->with(['registradoPor', 'cliente'])
                        ->orderBy('fecha_pago')
                        ->orderBy('id')
                        ->get();

                    // Logic to define cut-off date (Day Zero for guarantees)
                    $fechasRef = array_filter([
                        $prestamo->fecha_autorizacion ? $prestamo->fecha_autorizacion->startOfDay()->timestamp : null,
                        $prestamo->fecha_entrega ? $prestamo->fecha_entrega->startOfDay()->timestamp : null,
                        $prestamo->created_at ? $prestamo->created_at->startOfDay()->timestamp : null
                    ]);
                    $timestampCorte = !empty($fechasRef) ? min($fechasRef) : null;
                    $fechaCorteDate = $timestampCorte ? \Carbon\Carbon::createFromTimestamp($timestampCorte)->startOfDay() : null;
                    $fechaCorteStr = $fechaCorteDate ? $fechaCorteDate->format('Y-m-d') : null;

                    // Filter payments efficiently
                    $todosLosPagos = $todosLosPagosRaw->filter(function($p) use ($fechaCorteStr) {
                         $tipo = strtolower($p->tipo_pago ?? '');
                         $esGarantia = $tipo === 'garantia' || $tipo === 'garantía' || $tipo === 'seguro';
                         
                         $pagoDateStr = $p->fecha_pago->format('Y-m-d');
                         // Solo considerarlo día cero si es estrictamente menor a la fecha de corte
                         // Permitiendo pagos el mismo día de la entrega
                         $esDiaCero = $fechaCorteStr && $pagoDateStr < $fechaCorteStr;
                         
                         return !$esGarantia && !$esDiaCero;
                    });

                    // Función para simular desglose de pagos (Bucket Logic)
                    $distribuirPagos = function($calendario, $pagosDisponibles) {
                        $distribucion = collect();
                        
                        // Crear cola de dinero disponible
                        // Cada entrada es una porción de un pago real
                        $colaPagos = [];
                        foreach($pagosDisponibles as $p) {
                            $colaPagos[] = [
                                'original' => $p,
                                'remanente' => (float)$p->monto
                            ];
                        }
                        
                        foreach($calendario as $cuota) {
                            $montoRequerido = (float)$cuota['monto'];
                            $pagosAsignados = collect();
                            
                            foreach($colaPagos as &$entry) {
                                if ($entry['remanente'] <= 0.001) continue;
                                
                                $tomar = min($entry['remanente'], $montoRequerido);
                                
                                if ($tomar > 0) {
                                    // Clonamos el pago original para preservar fechas e IDs
                                    // pero ajustamos el monto a la porción que cubre esta cuota
                                    $pagoVirtual = clone $entry['original'];
                                    $pagoVirtual->monto = $tomar;
                                    
                                    $pagosAsignados->push($pagoVirtual);
                                    
                                    $entry['remanente'] -= $tomar;
                                    $montoRequerido -= $tomar;
                                }
                                
                                if ($montoRequerido <= 0.001) break;
                            }
                            unset($entry); // Romper referencia

                            if ($pagosAsignados->isNotEmpty()) {
                                $distribucion->put($cuota['numero'], $pagosAsignados);
                            }
                        }
                        
                        return $distribucion;
                    };

                    // Aplicar la distribución
                    $pagosRegistrados = $distribuirPagos($calendarioPagos, $todosLosPagos);

                    // ===================================================================================
                    // AJUSTE PARA PAGOS "SOLO MULTA" (Capital 0, Moratorio > 0)
                    // Estos pagos son ignorados por distribuirPagos porque no tienen "remanente" de capital.
                    // Debemos asignarlos manualmente a una cuota para que aparezcan en el historial.
                    // ===================================================================================
                    $pagosSoloMulta = $todosLosPagos->filter(function($p) {
                         return ((float)$p->monto <= 0.001) && ((float)$p->moratorio_pagado > 0.001);
                    });

                    foreach($pagosSoloMulta as $pagoMulta) {
                        $targetInst = null;
                        
                        // 1. Intentar usar el número de pago registrado en BD
                        if (!empty($pagoMulta->numero_pago)) {
                            $targetInst = (int)$pagoMulta->numero_pago;
                        } 
                        
                        // 2. Si no hay, buscar el último pago de capital realizado antes o en el mismo momento que la multa
                        if (!$targetInst) {
                            $fechaPago = Carbon::parse($pagoMulta->fecha_pago);
                            $targetInst = 1; // Default
                            
                            // Iterar sobre los buckets con actividad (capital)
                            foreach($pagosRegistrados as $instNum => $pagos) {
                                // Obtener la fecha del pago más reciente en este bucket
                                $maxFecha = $pagos->max('fecha_pago');
                                
                                if ($maxFecha && $maxFecha->lte($fechaPago)) {
                                    // Si este bucket se pagó antes de la multa, es candidato.
                                    // Queremos el bucket con el número más alto (el más avanzado en el crédito).
                                    if ($instNum > $targetInst) {
                                        $targetInst = $instNum;
                                    }
                                }
                            }
                        }

                        // Agregar a pagosRegistrados
                        if (!$pagosRegistrados->has($targetInst)) {
                            $pagosRegistrados->put($targetInst, collect());
                        }
                        
                        // Clonamos o usamos directo, no importa porque no tiene remanente que afectar
                        $pagosRegistrados->get($targetInst)->push($pagoMulta);
                    }
                    // ===================================================================================

                    // Mapeo para determinar el último movimiento de cada pago (para mostrar moratorios)
                    // PagoID => MaxInstallmentNumber
                    $maxInstMap = [];
                    foreach ($pagosRegistrados as $num => $pagos) {
                        foreach ($pagos as $p) {
                            $id = $p->id; // ID original del pago
                            if (!isset($maxInstMap[$id])) $maxInstMap[$id] = $num;
                            else $maxInstMap[$id] = max($maxInstMap[$id], $num);
                        }
                    }

                    // Pre-calcular calendarios individuales para préstamos grupales
                    $clientSchedules = [];
                    if ($prestamo->producto === 'grupal') {
                        foreach ($prestamo->clientes as $cliente) {
                            $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                            $clientSchedules[$cliente->id] = calcularCalendarioPagos(
                                $montoCliente,
                                $tasaInteres,
                                $plazo,
                                $periodicidad,
                                $fechaPrimerPago,
                                $ultimoPago,
                                'martes'
                            );
                        }
                    }

                    // Recalcular montos de cuotas para grupos sumando los individuales
                    // Esto asegura que la suma de exigibles coincida con la lógica de prestamo_detalle
                    if ($prestamo->producto === 'grupal') {
                        foreach ($calendarioPagos as $key => $pago) {
                            $sumaExigibles = 0;
                            foreach ($clientSchedules as $schedule) {
                                foreach ($schedule as $item) {
                                    if ($item['numero'] == $pago['numero']) {
                                        $sumaExigibles += $item['monto'];
                                        break;
                                    }
                                }
                            }
                            if ($sumaExigibles > 0) {
                                $calendarioPagos[$key]['monto'] = $sumaExigibles;
                            }
                        }
                    }
                @endphp
                
                @foreach($calendarioPagos as $pago)
                    @php
                        // Buscar si existe un pago registrado (distribuido) para este número
                        $pagoRealizado = $pagosRegistrados->get($pago['numero']);
                        $fechaPagoReal = '';
                        $montoPagado = 0;
                        
                        if ($pagoRealizado && $pagoRealizado->isNotEmpty()) {
                            // Si hay múltiples pagos (varios clientes o abonos parciales), sumar los montos
                            $montoPagado = $pagoRealizado->sum('monto');
                            // Usar la fecha del ÚLTIMO pago que completó esta cuota, o el primero?
                            // El requerimiento dice: "registrando la misma fecha en la que se pago"
                            // Si un solo pago cubre 5 cuotas, todas tendrán esa fecha.
                            // Si múltiples pagos cubren 1 cuota, mostramos la fecha del último (fecha de liquidación de cuota) o listamos?
                            // Lo estándar es mostrar la fecha en que se cubrió.
                            $fechaPagoReal = $pagoRealizado->sortByDesc('fecha_pago')->first()->fecha_pago->format('d-m-y');
                        }
                        // Calcular Moratorios Recuperados para esta fila
                        // Regla: Mostrar el moratorio total del pago SOLO si esta fila es el "último movimiento" de ese pago.
                        $moratorioRow = 0;
                        $moratorioGarantiaRow = 0;

                        if ($pagoRealizado) {
                            // Iterar sobre los pagos únicos que tocaron esta fila
                            // Como $pagoRealizado puede tener fragmentos del mismo pago, usamos unique('id')
                            $pagosUnicos = $pagoRealizado->unique('id');
                            foreach($pagosUnicos as $p) {
                                // Si esta fila es la última cuota que tocó este pago
                                if (isset($maxInstMap[$p->id]) && $maxInstMap[$p->id] == $pago['numero']) {
                                    $tipo = strtolower($p->tipo_pago ?? '');
                                    $esGarantia = $tipo === 'garantia' || $tipo === 'garantía';

                                    if ($esGarantia) {
                                        $moratorioGarantiaRow += $p->moratorio_pagado;
                                    } else {
                                        $moratorioRow += $p->moratorio_pagado;
                                    }
                                }
                            }
                        }
                    @endphp
                    <tr @if($pago['numero'] % 2 == 0) style="background-color: #f3f4f6;" @endif
                        @if(!$forPdf) 
                            onclick="toggleAccordion({{ $pago['numero'] }})" 
                            style="cursor: pointer;" 
                            title="Click para ver historial de pagos"
                        @endif>
                        <td>{{ $pago['numero'] }}</td>
                        <td>{{ $pago['fecha'] }}</td>
                        <td>{{ $fechaPagoReal }}</td>
                        <td>{{ number_format($pago['monto'], 0) }}</td>
                        <td>{{ $montoPagado > 0 ? number_format($montoPagado, 0) : '' }}</td>
                        <td></td>
                        {{-- Columnas de Multas Recuperadas --}}
                        <td style="text-align: center;">{{ $moratorioRow > 0 ? number_format($moratorioRow, 0) : '' }}</td>
                        <td style="text-align: center;">{{ $moratorioGarantiaRow > 0 ? number_format($moratorioGarantiaRow, 0) : '' }}</td>
                    </tr>

                    {{-- Historial de Pagos para esta cuota (Accordion) --}}
                    @if(isset($pagoRealizado) && $pagoRealizado->isNotEmpty() && !$forPdf)
                    <tr class="accordion-row-{{ $pago['numero'] }}" style="display: none;">
                        <td colspan="10" style="padding: 0; border: none;">
                            <div style="background-color: #f8f9fa; padding: 5px 10px; border-bottom: 1px solid #ddd; margin-left: 20px;">
                                <div 8yle="font-weight: bold; font-size: 9px; margin-bottom: 3px; color: #4b5563;">Historial de Pagos Aplicados:</div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 9px; background: white;">
                                    <thead>
                                        <tr style="background-color: #e02424; color: white;">
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: left;">Cliente</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Fecha vencimiento</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Fecha de pago</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Recuperado</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Pagado con garantía</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Moratorio recuperado</th>
                                            <th style="padding: 3px 4px; border: 1px solid #333; text-align: center;">Moratorio con garantia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagoRealizado as $pagoDetalle)
                                        <tr>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: left;">
                                                {{ $pagoDetalle->cliente ? mb_strtoupper($pagoDetalle->cliente->nombre_completo) : 'CLIENTE DESCONOCIDO' }}
                                            </td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">{{ $pago['fecha'] }}</td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">{{ $pagoDetalle->fecha_pago->format('d/m/Y') }}</td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">${{ number_format($pagoDetalle->monto, 2) }}</td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">
                                                @if(in_array(strtolower($pagoDetalle->metodo_pago ?? ''), ['garantia', 'garantía']))
                                                    ${{ number_format($pagoDetalle->monto, 2) }}
                                                @endif
                                            </td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">
                                                @if($pagoDetalle->moratorio_pagado > 0)
                                                    ${{ number_format($pagoDetalle->moratorio_pagado, 2) }}
                                                @endif
                                            </td>
                                            <td style="padding: 3px 4px; border: 1px solid #ccc; text-align: center;">
                                                {{-- Lógica pendiente para Moratorio con Garantía --}}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endif

                    @if($prestamo->producto === 'grupal')
                        @foreach($prestamo->clientes as $cliente)
                            @php
                                $clientSchedule = $clientSchedules[$cliente->id] ?? [];
                                $clientPago = collect($clientSchedule)->firstWhere('numero', $pago['numero']);
                                $montoClientePago = $clientPago['monto'] ?? 0;

                                $pagoCliente = $pagoRealizado ? $pagoRealizado->where('cliente_id', $cliente->id)->sortByDesc('fecha_pago')->first() : null;
                                $fechaPagoCliente = $pagoCliente ? $pagoCliente->fecha_pago->format('d-m-y') : '';
                            @endphp
                            <tr class="accordion-row-{{ $pago['numero'] }} group-detail-row" style="display: none; background-color: #fff;">
                                <td style="border-top: none;"></td>
                                <td style="text-align: left; padding-left: 20px; font-size: 0.9em; color: #555; border-top: none;">
                                    <span style="display:inline-block; width: 10px;">↳</span> 
                                    {{ mb_strtoupper(trim($cliente->nombres . ' ' . $cliente->apellido_paterno)) }}
                                </td>
                                <td style="font-size: 0.9em; color: #555; border-top: none;">{{ $fechaPagoCliente }}</td>
                                <td style="font-size: 0.9em; color: #555; border-top: none;">{{ number_format($montoClientePago, 0) }}</td>
                                <td colspan="6" style="border-top: none;"></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        {{-- Detalles de saldos totales --}}
        <div class="section-title">Detalles de saldos totales</div>
        <table class="saldos-table">
            <thead>
                <tr>
                    <th class="label">Capital vigente</th>
                    <th>Interés vigente</th>
                    <th>Iva vigente</th>
                    <th>Capital vencido</th>
                    <th>Interés vencido</th>
                    <th>Iva vencido</th>
                    <th>Atrasos</th>
                    <th>Saldo total</th>
                    <th>Adeudo total</th>
                    <th>Garantía</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Obtener configuración del préstamo
                    $plazoNormSaldos = strtolower(trim($prestamo->plazo ?? '4meses'));
                    $periodicidadNormSaldos = strtolower(trim($prestamo->periodicidad ?? 'semanal'));
                    $configSaldos = determinarConfiguracionPago($plazoNormSaldos, $periodicidadNormSaldos);
                    
                    if ($configSaldos) {
                        $mesesInteresSaldos = $configSaldos['meses_interes'];
                        $numeroPagosSaldos = $configSaldos['total_pagos'];
                    } else {
                        $mesesInteresSaldos = 4;
                        $numeroPagosSaldos = 16;
                    }
                    
                    // Monto del crédito
                    $montoCredito = $totalCredito;
                    
                    // Calcular pago por mil (monto de cada pago)
                    $pagosPorMil = !empty($calendarioPagos) ? $calendarioPagos[0]['monto'] : 0;
                    
                    // Sumatoria de pagos realizados
                    $sumatoriaPagos = $prestamo->pagos()->sum('monto') - $prestamo->pagos()->sum('moratorio_pagado');
                    
                    // Calcular monto vencido real (sin depender de numero_pago)
                    // Regla: cualquier pago descuenta primero lo ya vencido (FIFO).
                    $pagosTranscurridos = 0;
                    $fechaHoy = now()->startOfDay();
                    foreach ($calendarioPagos as $pagoProg) {
                        $fechaVenc = \Carbon\Carbon::createFromFormat('d-m-y', $pagoProg['fecha'])->startOfDay();
                        if ($fechaVenc->lte($fechaHoy)) {
                            $pagosTranscurridos++;
                        }
                    }

                    // Usar todos los pagos para mantener consistencia con la tabla de movimientos
                    // Anteriormente se filtraba por fecha <= hoy, lo que causaba discrepancias si el pago
                    // tenía fecha posterior a la generación del reporte o timestamps con horas futuras.
                    $pagosHastaHoy = $todosLosPagos;

                    $pagadoPorNumero = $pagosHastaHoy
                        ->whereNotNull('numero_pago')
                        ->groupBy('numero_pago')
                        ->map(fn ($pagos) => (float) $pagos->sum('monto'))
                        ->toArray();

                    $pagosSinNumeroTotal = (float) $pagosHastaHoy
                        ->whereNull('numero_pago')
                        ->sum('monto');

                    $montoVencido = \App\Models\Prestamo::calcularMontoVencidoDesdeCalendario(
                        $calendarioPagos,
                        $fechaHoy,
                        $pagadoPorNumero,
                        $pagosSinNumeroTotal
                    );
                    
                    // Calcular pagos futuros (Vigentes)
                    // $pagosFuturos = count($calendarioPagos) - $pagosTranscurridos;
                    
                    // Calcular interés e IVA base
                    $interesBase = (($montoCredito / 100) * ($prestamo->tasa_interes ?? 0)) * $mesesInteresSaldos;
                    $ivaPorcentajeSaldos = \App\Models\Configuration::get('iva_percentage', 16);
                    $ivaBase = ($interesBase / 100) * $ivaPorcentajeSaldos;

                    $totalDelPrestamo = $montoCredito + $interesBase + $ivaBase;
                    $proporcionCapital = ($totalDelPrestamo > 0) ? $montoCredito / $totalDelPrestamo : 0;
                    $proporcionInteres = ($totalDelPrestamo > 0) ? $interesBase / $totalDelPrestamo : 0;
                    $proporcionIva = ($totalDelPrestamo > 0) ? $ivaBase / $totalDelPrestamo : 0;

                    // Fórmula 4: Capital vencido
                    $capitalVencido = $montoVencido * $proporcionCapital;

                    // Fórmula 5: Interés vencido
                    $interesVencido = $montoVencido * $proporcionInteres;

                    // Fórmula 6: IVA vencido
                    $ivaVencido = $montoVencido * $proporcionIva;

                    // Calcular montos pagados
                    $capitalPagado = $sumatoriaPagos * $proporcionCapital;
                    $interesPagado = $sumatoriaPagos * $proporcionInteres;
                    $ivaPagado = $sumatoriaPagos * $proporcionIva;

                    // Fórmulas 1, 2, 3: Saldos Vigentes
                    $capitalVigente = $montoCredito - $capitalPagado - $capitalVencido;
                    $interesVigente = $interesBase - $interesPagado - $interesVencido;
                    $ivaVigente = $ivaBase - $ivaPagado - $ivaVencido;
                    
                    // Calcular número de pagos atrasados (aplicando pagos acumulados a lo más antiguo)
                    $atrasos = \App\Models\Prestamo::calcularAtrasosDesdeCalendario(
                        $calendarioPagos,
                        $fechaHoy,
                        $pagadoPorNumero,
                        $pagosSinNumeroTotal,
                        1
                    );

                    // Calcular saldo moratorio acumulativo (Histórico de multas - Pagos a moratorio)
                    $multasGeneradasCount = 0;
                    $totalMultasGeneradasMonto = 0;
                    $acumuladoCuotas = 0;
                    $pagosOrdenados = $todosLosPagos->sortBy('fecha_pago')->values();
                    
                    // Crear línea de tiempo de saldo acumulado pagado
                    $timelinePagos = [];
                    $acumuladoPagosDinero = 0;
                    foreach($pagosOrdenados as $p) {
                        $acumuladoPagosDinero += $p->monto;
                        $timelinePagos[] = [
                            'monto_acumulado' => $acumuladoPagosDinero,
                            'fecha' => \Carbon\Carbon::parse($p->fecha_pago),
                            'tipo' => $p->tipo_pago // Para debug
                        ];
                    }

                    foreach ($calendarioPagos as $pagoProg) {
                        $fechaVenc = \Carbon\Carbon::createFromFormat('d-m-y', $pagoProg['fecha'])->endOfDay();
                        
                        // Si la cuota vence en el futuro, no genera multa aún
                        if ($fechaVenc->isFuture()) {
                             continue;
                        }

                        $montoCuota = $pagoProg['monto'];
                        $targetAcumulado = $acumuladoCuotas + $montoCuota; 
                        $acumuladoCuotas += $montoCuota;
                        
                        // Buscar cuándo se cubrió este monto
                        $fechaCobertura = null;
                        foreach ($timelinePagos as $tp) {
                            if ($tp['monto_acumulado'] >= $targetAcumulado - 0.1) { 
                                $fechaCobertura = $tp['fecha'];
                                break;
                            }
                        }
                        
                        // Multa si no se ha cubierto o se cubrió tarde
                        // Comparamos usando strings Y-m-d para evitar problemas de horas/timezones
                        if ($fechaCobertura === null) {
                            $multasGeneradasCount++;
                            $totalMultasGeneradasMonto += ($montoCuota * 0.05);
                        } elseif ($fechaCobertura->format('Y-m-d') > $fechaVenc->format('Y-m-d')) {
                             $multasGeneradasCount++;
                             $totalMultasGeneradasMonto += ($montoCuota * 0.05);
                        }
                    }

                    $moratorioPagadoTotal = $prestamo->pagos()->sum('moratorio_pagado');

                    $saldoTotal = max(0, $totalMultasGeneradasMonto - $moratorioPagadoTotal); // Saldo Moratorio Real

                    // Sobrescribir atrasos con el conteo histórico
                    $atrasos = $multasGeneradasCount;

                    $adeudoTotal = $capitalVigente + $interesVigente + $ivaVigente + $capitalVencido + $interesVencido + $ivaVencido + $saldoTotal;
                    
                    $garantiaSaldos = $totalGarantia;
                @endphp
                <tr>
                    <td class="label">{{ number_format($capitalVigente, 0) }}</td>
                    <td>{{ number_format($interesVigente, 0) }}</td>
                    <td>{{ number_format($ivaVigente, 0) }}</td>
                    <td>{{ $capitalVencido > 0.5 ? number_format($capitalVencido, 0) : '' }}</td>
                    <td>{{ $interesVencido > 0.5 ? number_format($interesVencido, 0) : '' }}</td>
                    <td>{{ $ivaVencido > 0.5 ? number_format($ivaVencido, 0) : '' }}</td>
                    <td>{{ $atrasos }}</td>
                    <td>{{ number_format($saldoTotal, 0) }}</td>
                    <td>{{ number_format($adeudoTotal, 0) }}</td>
                    <td>{{ number_format($garantiaSaldos, 0) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Detalle de saldos por cliente --}}
        <div class="section-title">Detalle de saldos</div>
        <table class="detalle-saldos-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Capital vigente</th>
                    <th>Interés vigente</th>
                    <th>Iva vigente</th>
                    <th>Capital vencido</th>
                    <th>Interés vencido</th>
                    <th>Iva vencido</th>
                    <th>Atrasos</th>
                    <th>Saldo moratorio</th>
                    <th>Deuda total</th>
                </tr>
            </thead>
            <tbody>
                @if($prestamo->producto === 'grupal')
                    @foreach($prestamo->clientes as $cliente)
                        @php
                            $montoCliente = $cliente->pivot->monto_autorizado ?? $cliente->pivot->monto_solicitado ?? 0;
                            
                            // Sumatoria de pagos del cliente
                            $sumatoriaPagosCliente = $prestamo->pagos()->where('cliente_id', $cliente->id)->sum('monto') - $prestamo->pagos()->where('cliente_id', $cliente->id)->sum('moratorio_pagado');
                            
                            // Obtener pago periódico del cliente
                            $clientSchedule = $clientSchedules[$cliente->id] ?? [];
                            $pagoPeriodicoCliente = !empty($clientSchedule) ? $clientSchedule[0]['monto'] : 0;

                            // Calcular monto vencido del cliente
                            $montoVencidoCliente = 0;
                            $pagosTranscurridosCliente = 0;
                            $fechaHoy = now()->startOfDay();

                            foreach ($clientSchedule as $pagoProgCliente) {
                                $fechaVenc = \Carbon\Carbon::createFromFormat('d-m-y', $pagoProgCliente['fecha'])->startOfDay();
                                if ($fechaVenc->lte($fechaHoy)) {
                                    $pagosTranscurridosCliente++;
                                }
                            }

                            // Usar todos los pagos (filtrando por cliente) para consistencia
                            $pagosHastaHoyCliente = $todosLosPagos->where('cliente_id', $cliente->id);

                            $pagadoPorNumeroCliente = $pagosHastaHoyCliente
                                ->whereNotNull('numero_pago')
                                ->groupBy('numero_pago')
                                ->map(fn ($pagos) => (float) $pagos->sum('monto'))
                                ->toArray();

                            $pagosSinNumeroClienteTotal = (float) $pagosHastaHoyCliente
                                ->whereNull('numero_pago')
                                ->sum('monto');

                            $montoVencidoCliente = \App\Models\Prestamo::calcularMontoVencidoDesdeCalendario(
                                $clientSchedule,
                                $fechaHoy,
                                $pagadoPorNumeroCliente,
                                $pagosSinNumeroClienteTotal
                            );
                            
                            // Calcular pagos futuros (Vigentes)
                            // $pagosFuturosCliente = count($clientSchedule) - $pagosTranscurridosCliente;
                            
                            // Capital vigente del cliente
                            // Se calcula usando las proporciones reales abajo, esta lógica anterior era incorrecta y causaba confusión
                            
                            // Interés e IVA base del cliente
                            $interesBaseCliente = (($montoCliente / 100) * ($prestamo->tasa_interes ?? 0)) * $mesesInteresSaldos;
                            $ivaBaseCliente = ($interesBaseCliente / 100) * $ivaPorcentajeSaldos;

                            $totalDelPrestamoCliente = $montoCliente + $interesBaseCliente + $ivaBaseCliente;
                            $proporcionCapitalCliente = ($totalDelPrestamoCliente > 0) ? $montoCliente / $totalDelPrestamoCliente : 0;
                            $proporcionInteresCliente = ($totalDelPrestamoCliente > 0) ? $interesBaseCliente / $totalDelPrestamoCliente : 0;
                            $proporcionIvaCliente = ($totalDelPrestamoCliente > 0) ? $ivaBaseCliente / $totalDelPrestamoCliente : 0;

                            // Monto vencido del cliente
                            $montoVencidoCliente = \App\Models\Prestamo::calcularMontoVencidoDesdeCalendario(
                                $clientSchedule,
                                $fechaHoy,
                                $pagadoPorNumeroCliente,
                                $pagosSinNumeroClienteTotal
                            );

                            // Capital, Interés e IVA vencidos
                            $capitalVencidoCliente = $montoVencidoCliente * $proporcionCapitalCliente;
                            $interesVencidoCliente = $montoVencidoCliente * $proporcionInteresCliente;
                            $ivaVencidoCliente = $montoVencidoCliente * $proporcionIvaCliente;

                            // Montos pagados del cliente
                            $capitalPagadoCliente = $sumatoriaPagosCliente * $proporcionCapitalCliente;
                            $interesPagadoCliente = $sumatoriaPagosCliente * $proporcionInteresCliente;
                            $ivaPagadoCliente = $sumatoriaPagosCliente * $proporcionIvaCliente;

                            // Saldos Vigentes del cliente
                            $capitalVigenteCliente = $montoCliente - $capitalPagadoCliente - $capitalVencidoCliente;
                            $interesVigenteCliente = $interesBaseCliente - $interesPagadoCliente - $interesVencidoCliente;
                            $ivaVigenteCliente = $ivaBaseCliente - $ivaPagadoCliente - $ivaVencidoCliente;
                            
                            // Calcular atrasos del cliente (aplicando pagos acumulados a lo más antiguo)
                            $atrasosCliente = \App\Models\Prestamo::calcularAtrasosDesdeCalendario(
                                $clientSchedule,
                                $fechaHoy,
                                $pagadoPorNumeroCliente,
                                $pagosSinNumeroClienteTotal,
                                1
                            );
                            
                            // NUEVA LÓGICA ACUMULATIVA PARA CLIENTES GRUPALES
                            $multasGeneradasCountCliente = 0;
                            $totalMultasGeneradasMontoCliente = 0;
                            $acumuladoCuotasCliente = 0;
                            
                            $pagosOrdenadosCliente = $todosLosPagos
                                ->where('cliente_id', $cliente->id)
                                ->filter(function($p) use ($fechaCorteStr) {
                                     $tipo = strtolower($p->tipo_pago ?? '');
                                     $esGarantia = $tipo === 'garantia' || $tipo === 'garantía' || $tipo === 'seguro';
                                     
                                     $pagoDateStr = $p->fecha_pago->format('Y-m-d');
                                     $esDiaCero = $fechaCorteStr && $pagoDateStr <= $fechaCorteStr;
                                     
                                     return !$esGarantia && !$esDiaCero;
                                })
                                ->sortBy('fecha_pago')
                                ->values();
                            
                            // Timeline cliente
                            $timelinePagosCliente = [];
                            $acumuladoPagosDineroCliente = 0;
                            foreach($pagosOrdenadosCliente as $p) {
                                $acumuladoPagosDineroCliente += $p->monto;
                                $timelinePagosCliente[] = [
                                    'monto_acumulado' => $acumuladoPagosDineroCliente,
                                    'fecha' => \Carbon\Carbon::parse($p->fecha_pago)
                                ];
                            }
                            
                            foreach ($clientSchedule as $pagoProgCliente) {
                                $fechaVenc = \Carbon\Carbon::createFromFormat('d-m-y', $pagoProgCliente['fecha'])->endOfDay();
                                
                                if ($fechaVenc->isFuture()) continue;

                                $montoCuota = $pagoProgCliente['monto'];
                                $targetAcumulado = $acumuladoCuotasCliente + $montoCuota;
                                $acumuladoCuotasCliente += $montoCuota;
                                
                                $fechaCobertura = null;
                                foreach ($timelinePagosCliente as $tp) {
                                    if ($tp['monto_acumulado'] >= $targetAcumulado - 0.1) {
                                        $fechaCobertura = $tp['fecha'];
                                        break;
                                    }
                                }
                                
                                // Comparamos usando strings Y-m-d para evitar problemas de horas/timezones
                                if ($fechaCobertura === null) {
                                    $multasGeneradasCountCliente++;
                                    $totalMultasGeneradasMontoCliente += ($montoCuota * 0.05);
                                } elseif ($fechaCobertura->format('Y-m-d') > $fechaVenc->format('Y-m-d')) {
                                     $multasGeneradasCountCliente++;
                                     $totalMultasGeneradasMontoCliente += ($montoCuota * 0.05);
                                }
                            }
                            
                            $moratorioPagadoTotalCliente = $prestamo->pagos()->where('cliente_id', $cliente->id)->sum('moratorio_pagado');

                            $saldoMoratorioCliente = max(0, $totalMultasGeneradasMontoCliente - $moratorioPagadoTotalCliente);
                            
                            // Sobrescribir atrasos con el conteo histórico
                            $atrasosCliente = $multasGeneradasCountCliente;

                            $deudaTotalCliente = $capitalVigenteCliente + $interesVigenteCliente + $ivaVigenteCliente + $capitalVencidoCliente + $interesVencidoCliente + $ivaVencidoCliente + $saldoMoratorioCliente;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($capitalVigenteCliente, 0) }}</td>
                            <td>{{ number_format($interesVigenteCliente, 0) }}</td>
                            <td>{{ number_format($ivaVigenteCliente, 0) }}</td>
                            <td>{{ $capitalVencidoCliente > 0.5 ? number_format($capitalVencidoCliente, 0) : '' }}</td>
                            <td>{{ $interesVencidoCliente > 0.5 ? number_format($interesVencidoCliente, 0) : '' }}</td>
                            <td>{{ $ivaVencidoCliente > 0.5 ? number_format($ivaVencidoCliente, 0) : '' }}</td>
                            <td>{{ $atrasosCliente }}</td>
                            <td>{{ number_format($saldoMoratorioCliente, 0) }}</td>
                            <td>{{ number_format($deudaTotalCliente, 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    @if($prestamo->cliente)
                        @php
                            $montoCliente = $prestamo->monto_total ?? 0;
                            
                            // Sumatoria de pagos del cliente
                            $sumatoriaPagosCliente = $prestamo->pagos()->sum('monto') - $prestamo->pagos()->sum('moratorio_pagado');
                            
                            // Calcular monto vencido (reutilizamos el cálculo global ya que es individual)
                            $montoVencidoCliente = $montoVencido;
                            
                            $interesBaseCliente = (($montoCliente / 100) * ($prestamo->tasa_interes ?? 0)) * $mesesInteresSaldos;
                            $ivaBaseCliente = ($interesBaseCliente / 100) * $ivaPorcentajeSaldos;

                            $totalDelPrestamoCliente = $montoCliente + $interesBaseCliente + $ivaBaseCliente;
                            $proporcionCapitalCliente = ($totalDelPrestamoCliente > 0) ? $montoCliente / $totalDelPrestamoCliente : 0;
                            $proporcionInteresCliente = ($totalDelPrestamoCliente > 0) ? $interesBaseCliente / $totalDelPrestamoCliente : 0;
                            $proporcionIvaCliente = ($totalDelPrestamoCliente > 0) ? $ivaBaseCliente / $totalDelPrestamoCliente : 0;

                            // Capital, Interés e IVA vencidos
                            $capitalVencidoCliente = $montoVencidoCliente * $proporcionCapitalCliente;
                            $interesVencidoCliente = $montoVencidoCliente * $proporcionInteresCliente;
                            $ivaVencidoCliente = $montoVencidoCliente * $proporcionIvaCliente;

                            // Montos pagados
                            $capitalPagadoCliente = $sumatoriaPagosCliente * $proporcionCapitalCliente;
                            $interesPagadoCliente = $sumatoriaPagosCliente * $proporcionInteresCliente;
                            $ivaPagadoCliente = $sumatoriaPagosCliente * $proporcionIvaCliente;

                            // Saldos Vigentes
                            $capitalVigenteCliente = $montoCliente - $capitalPagadoCliente - $capitalVencidoCliente;
                            $interesVigenteCliente = $interesBaseCliente - $interesPagadoCliente - $interesVencidoCliente;
                            $ivaVigenteCliente = $ivaBaseCliente - $ivaPagadoCliente - $ivaVencidoCliente;
                            
                            // Calcular atrasos del cliente (individual)
                            $atrasosCliente = $atrasos; // Usamos el cálculo global ya que es individual
                            
                            // calcular multa cliente (individual)
                            // Usamos el saldo moratorio acumulativo global calculado previamente
                            $saldoMoratorioCliente = $saldoTotal;
                            
                            $deudaTotalCliente = $capitalVigenteCliente + $interesVigenteCliente + $ivaVigenteCliente + $capitalVencidoCliente + $interesVencidoCliente + $ivaVencidoCliente + $saldoMoratorioCliente;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($capitalVigenteCliente, 0) }}</td>
                            <td>{{ number_format($interesVigenteCliente, 0) }}</td>
                            <td>{{ number_format($ivaVigenteCliente, 0) }}</td>
                            <td>{{ $capitalVencidoCliente > 0.5 ? number_format($capitalVencidoCliente, 0) : '' }}</td>
                            <td>{{ $interesVencidoCliente > 0.5 ? number_format($interesVencidoCliente, 0) : '' }}</td>
                            <td>{{ $ivaVencidoCliente > 0.5 ? number_format($ivaVencidoCliente, 0) : '' }}</td>
                            <td>{{ $atrasosCliente }}</td>
                            <td>{{ number_format($saldoMoratorioCliente, 0) }}</td>
                            <td>{{ number_format($deudaTotalCliente, 0) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>

    @if(!$forPdf)
    </div> {{-- Cierre del page-wrapper --}}
    @endif

    <script>
        function toggleAccordion(id) {
            var rows = document.getElementsByClassName('accordion-row-' + id);
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row.style.display === 'none') {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
