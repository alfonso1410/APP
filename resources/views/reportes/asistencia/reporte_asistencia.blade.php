<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }
        .logo-izquierda {
            width: 150px;
            text-align: left;
        }
        .logo-izquierda img {
            width: 140px;
        }
        .titulo-centro {
            text-align: center;
            font-weight: bold;
        }
        .titulo-centro .principal {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .titulo-centro .subtitulo {
            font-size: 9px;
            margin-bottom: 5px;
        }
        .titulo-centro .reporte {
            font-size: 11px;
            background-color: #D3D3D3;
            padding: 4px;
            margin-top: 5px;
        }
        .logo-derecha {
            width: 80px;
            text-align: right;
        }
        .logo-derecha img {
            width: 70px;
        }

        /* Información del Grupo */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px;
            font-weight: bold;
        }
        .info-table .label {
            background-color: #E0E0E0;
            width: 12%;
        }

        /* Tabla de Asistencia */
        .asistencia-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .asistencia-table th,
        .asistencia-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        /* Headers */
        .asistencia-table thead th {
            background-color: #4169E1;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }
        .asistencia-table thead th.alumno-header {
            text-align: left;
            width: 30%;
        }
        .asistencia-table thead th.dia-header {
            width: 2.4%;
            font-size: 7px;
        }
        
        /* Columna de alumno */
        .asistencia-table td.alumno {
            text-align: left;
            padding-left: 5px;
            font-weight: bold;
            font-size: 8px;
            background-color: #f8f9fa;
        }
        
        /* Estados de asistencia */
        .asistio {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        .falta {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        .retardo {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
        }
        .sin-dato {
            background-color: #e9ecef;
            color: #6c757d;
        }

        /* Resumen de asistencias */
        .resumen-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            font-size: 9px;
        }
        .resumen-table td {
            padding: 5px;
            text-align: center;
        }
        .resumen-box {
            display: inline-block;
            border: 2px solid #000;
            padding: 5px 15px;
            font-weight: bold;
            margin: 0 10px;
        }
        .resumen-asistencias {
            background-color: #d4edda;
        }
        .resumen-faltas {
            background-color: #f8d7da;
        }
        .resumen-retardos {
            background-color: #fff3cd;
        }

        /* Leyenda */
        .leyenda {
            width: 100%;
            margin-top: 10px;
            font-size: 8px;
            text-align: center;
        }
        .leyenda span {
            margin: 0 10px;
            font-weight: bold;
        }

        /* Firmas */
        .footer-firmas {
            width: 100%;
            margin-top: 30px;
        }
        .footer-firmas table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-firmas td {
            width: 50%;
            text-align: center;
            padding: 0 10px;
        }
        .firma-container hr {
            border: none;
            border-top: 2px solid #000;
            margin: 0 0 5px 0;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
        .firma-texto {
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td class="logo-izquierda">
                @if(file_exists(public_path('Assets/logo-princeton.png')))
                <img src="{{ public_path('Assets/logo-princeton.png') }}" alt="Logo" style="width: 140px;">
                @endif
            </td>
            <td class="titulo-centro">
                <div class="principal">"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</div>
                <div class="subtitulo">SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</div>
                <div class="reporte">REPORTE DE ASISTENCIA - {{ strtoupper($mesNombre) }} {{ $anio }}</div>
            </td>
            <td class="logo-derecha">
                @if(file_exists(public_path('Assets/logo-azul.png')))
                <img src="{{ public_path('Assets/logo-azul.png') }}" alt="Logo Azul" style="width: 70px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- Información del Grupo -->
    <table class="info-table">
        <tr>
            <td class="label">GRUPO:</td>
            <td style="width: 30%;">{{ $grupo->grado->nombre ?? 'N/A' }} - {{ $grupo->nombre_grupo }}</td>
            <td class="label">IDIOMA/MATERIA:</td>
            <td style="width: 30%;">{{ $valor }}</td>
            <td class="label">PERIODO:</td>
            <td style="width: 20%;">{{ $periodo->nombre }}</td>
        </tr>
    </table>

    <!-- Tabla de Asistencia -->
    <table class="asistencia-table">
        <thead>
            <tr>
                <th class="alumno-header">ALUMNO</th>
                @foreach($diasFormato as $index => $dia)
                    <th class="dia-header">
                        {{ $dia }}<br>
                        <span style="font-size: 6px;">{{ $diasSemana[$index] }}</span>
                    </th>
                @endforeach
                <th style="background-color: #d4edda; color: #155724;">A</th>
                <th style="background-color: #f8d7da; color: #721c24;">F</th>
                <th style="background-color: #fff3cd; color: #856404;">R</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $alumno)
                @php
                    $totalAsistencias = 0;
                    $totalFaltas = 0;
                    $totalRetardos = 0;
                @endphp
                <tr>
                    <td class="alumno">
                        {{ strtoupper($alumno->apellido_paterno) }} {{ strtoupper($alumno->apellido_materno) }} {{ strtoupper($alumno->nombres) }}
                    </td>
                    @foreach($diasHabiles as $fecha)
                        @php
                            $tipoAsistencia = $asistencias->get($alumno->alumno_id, [])[$fecha] ?? null;
                            $clase = 'sin-dato';
                            $simbolo = '-';
                            
                            if ($tipoAsistencia === 'PRESENTE') {
                                $clase = 'asistio';
                                $simbolo = '✓';
                                $totalAsistencias++;
                            } elseif ($tipoAsistencia === 'FALTA') {
                                $clase = 'falta';
                                $simbolo = 'F';
                                $totalFaltas++;
                            } elseif ($tipoAsistencia === 'RETARDO') {
                                $clase = 'retardo';
                                $simbolo = 'R';
                                $totalRetardos++;
                            }
                        @endphp
                        <td class="{{ $clase }}">{{ $simbolo }}</td>
                    @endforeach
                    <td style="background-color: #d4edda; font-weight: bold;">{{ $totalAsistencias }}</td>
                    <td style="background-color: #f8d7da; font-weight: bold;">{{ $totalFaltas }}</td>
                    <td style="background-color: #fff3cd; font-weight: bold;">{{ $totalRetardos }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Leyenda -->
    <div class="leyenda">
        <span style="color: #155724;">✓ = Asistencia</span>
        <span style="color: #721c24;">F = Falta</span>
        <span style="color: #856404;">R = Retardo</span>
        <span style="color: #6c757d;">- = Sin registro</span>
    </div>

</body>
</html>