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
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 6px;
            margin-bottom: 6px;
            border: 1px solid #333;
        }
        .info-header-item {
            padding: 4px 6px;
            border-right: 1px solid #333;
            font-size: 9px;
        }
        .info-header-item:last-child {
            border-right: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .info-header-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-header-value {
            font-size: 10px;
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
                <div class="info-header-label">Entregado: {{ $prestamo->fecha_desembolso ? $prestamo->fecha_desembolso->format('d-m-y') : 'N/A' }}</div>
                <div class="info-header-value">Número de pagos: 16</div>
            </div>
            <div class="info-header-item">
                <div class="info-header-label">Garantía devuelta:</div>
                <div class="info-header-value"></div>
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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>${{ number_format($prestamo->monto_total ?? 0, 0) }}</td>
                    <td>${{ number_format(($prestamo->monto_total ?? 0) * (($prestamo->garantia ?? 0) / 100), 0) }}</td>
                    <td>$50</td>
                    <td>${{ number_format(($prestamo->monto_total ?? 0) - (($prestamo->monto_total ?? 0) * (($prestamo->garantia ?? 0) / 100)) - (($prestamo->monto_total ?? 0) * 0.02), 0) }}</td>
                    <td>{{ $prestamo->tasa_interes ?? 0 }}%</td>
                    <td>${{ number_format(($prestamo->monto_total ?? 0) * (($prestamo->tasa_interes ?? 0) / 100), 0) }}</td>
                    <td>16%</td>
                    <td>${{ number_format((($prestamo->monto_total ?? 0) * (($prestamo->tasa_interes ?? 0) / 100)) * 0.16, 0) }}</td>
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
                            $comisionCliente = $montoCliente * 0.02;
                            $efectivoCliente = $montoCliente - $garantiaCliente - $comisionCliente;
                            $tasaCliente = $prestamo->tasa_interes ?? 0;
                            $interesCliente = $montoCliente * ($tasaCliente / 100);
                            $ivaCliente = $interesCliente * 0.16;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($garantiaCliente, 0) }}</td>
                            <td>50</td>
                            <td>{{ number_format($efectivoCliente, 0) }}</td>
                            <td>{{ $tasaCliente }}%</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>16%</td>
                            <td>{{ number_format($ivaCliente, 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    @if($prestamo->cliente)
                        @php
                            $montoCliente = $prestamo->monto_total ?? 0;
                            $garantiaCliente = $montoCliente * (($prestamo->garantia ?? 0) / 100);
                            $comisionCliente = $montoCliente * 0.02;
                            $efectivoCliente = $montoCliente - $garantiaCliente - $comisionCliente;
                            $tasaCliente = $prestamo->tasa_interes ?? 0;
                            $interesCliente = $montoCliente * ($tasaCliente / 100);
                            $ivaCliente = $interesCliente * 0.16;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($garantiaCliente, 0) }}</td>
                            <td>50</td>
                            <td>{{ number_format($efectivoCliente, 0) }}</td>
                            <td>{{ $tasaCliente }}%</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>16%</td>
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
                <tr>
                    <td>15-03-26</td>
                    <td></td>
                    <td>12,288</td>
                    <td>2,265</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
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
                    <th colspan="4">Multas con recuperadas</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 1; $i <= 16; $i++)
                    <tr @if($i % 2 == 0) style="background-color: #f3f4f6;" @endif>
                        <td>{{ $i }}</td>
                        <td></td>
                        <td></td>
                        <td>755</td>
                        <td></td>
                        <td></td>
                        <td colspan="4"></td>
                    </tr>
                @endfor
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
                <tr>
                    <td class="label">10,000</td>
                    <td>1,800</td>
                    <td>288</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>12,088</td>
                    <td>0</td>
                    <td>0</td>
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
                            $tasaDecimal = ($prestamo->tasa_interes ?? 0) / 100;
                            $interesCliente = $montoCliente * $tasaDecimal;
                            $ivaCliente = $interesCliente * 0.16;
                            $totalCliente = $montoCliente + $interesCliente + $ivaCliente;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>{{ number_format($ivaCliente, 0) }}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>{{ number_format($totalCliente, 0) }}</td>
                        </tr>
                    @endforeach
                @else
                    @if($prestamo->cliente)
                        @php
                            $montoCliente = $prestamo->monto_total ?? 0;
                            $tasaDecimal = ($prestamo->tasa_interes ?? 0) / 100;
                            $interesCliente = $montoCliente * $tasaDecimal;
                            $ivaCliente = $interesCliente * 0.16;
                            $totalCliente = $montoCliente + $interesCliente + $ivaCliente;
                        @endphp
                        <tr>
                            <td class="left">{{ mb_strtoupper(trim($prestamo->cliente->nombres . ' ' . $prestamo->cliente->apellido_paterno . ' ' . $prestamo->cliente->apellido_materno)) }}</td>
                            <td>{{ number_format($montoCliente, 0) }}</td>
                            <td>{{ number_format($interesCliente, 0) }}</td>
                            <td>{{ number_format($ivaCliente, 0) }}</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>{{ number_format($totalCliente, 0) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>

    @if(!$forPdf)
    </div> {{-- Cierre del page-wrapper --}}
    @endif
</body>
</html>
