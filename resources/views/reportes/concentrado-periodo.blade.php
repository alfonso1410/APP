<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Concentrado de Calificaciones</title>
    <style>
        /* Fuentes y estilos generales */
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 98%;
            margin: 1%;
        }

        /* Encabezado */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }
        .logo-izquierda {
            width: 200px;
        }
        .logo-derecha {
            width: 80px;
        }
        .titulo-centro {
            text-align: center;
            font-weight: bold;
        }
        .titulo-centro .principal {
            font-size: 14px;
        }
        .titulo-centro .subtitulo {
            font-size: 12px;
        }
        .titulo-centro .concentrado {
            font-size: 12px;
            background-color: #E0E0E0;
            padding: 2px;
        }

        /* Tabla de Información */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px;
            font-weight: bold;
        }
        .info-table .label {
            background-color: #E0E0E0;
            width: 15%;
        }

        /* --- INICIO DE CORRECCIONES CSS --- */

        /* Tabla Principal de Calificaciones */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed; 
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 3px;
            height: 25px; /* Alto de fila estándar */
        }
        
        /* 1. Estilos del Encabezado (THEAD) - La corrección clave */
        .main-table thead th { 
            font-weight: bold;
            vertical-align: middle;
            font-size: 8px;
            text-align: center;
            word-wrap: break-word;
            padding: 4px;
        }
        
        /* 2. Estilos del Cuerpo (TBODY) - Centrar calificaciones */
        .main-table tbody td {
            text-align: center; /* Centrar números */
            vertical-align: middle;
        }
        
        /* 3. Celdas de Alumno (Excepción para alinear a la izquierda) */
        .main-table tbody td.cell-alumno {
            text-align: left; /* Alinear nombres a la izquierda */
            padding-left: 5px;
            font-weight: bold;
            font-size: 9px;
        }
        .main-table .cell-num {
            width: 3%;
            font-weight: bold;
            text-align: center;
        }
        .main-table .cell-alumno {
            width: 20%;
        }

        /* 4. Celda de Texto Vertical (Trimestre) */
        .cell-vertical-container {
            width: 3%;
            padding: 0;
            margin: 0;
            background-color: #004A99 !important; /* Azul (forzado) */
            color: #FFFFFF !important; /* Blanco (forzado) */
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
        }
        .text-vertical {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding: 5px 0;
        }
        /* --- FIN DE CORRECCIONES CSS --- */

        /* Pie de página (Firmas) - SOLUCIÓN CON HR */
        .footer-firmas {
            width: 100%;
            margin-top: 40px;
            padding: 0;
        }

        .footer-firmas table {
            width: 80%;
            border-collapse: collapse;
        }

        .footer-firmas td {
            width: 50%;
            padding: 0 90px;
            text-align: center;
            vertical-align: top;
        }

        .firma-container hr {
            border: none;
            border-top: 2px solid #000;
            margin: 0 0 8px 0;
            width: 100%;
        }

        .firma-texto {
            font-weight: bold;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <table class="header-table">
            <tr>
                <td class="logo-izquierda" style="text-align: left; width: 200px;">
                    @if(file_exists(public_path('Assets/logo-princeton.png')))
                    <img src="{{ public_path('Assets/logo-princeton.png') }}" alt="Logo" style="width: 200px;">
                    @endif
                </td>
                <td class="titulo-centro">
                    <div class="principal">"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</div>
                    <div class="subtitulo">SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</div>
                    <div class="concentrado">CONCENTRADO DE CALIFICACIONES</div>
                </td>
                <td class="logo-derecha" style="text-align: right; width: 100px; vertical-align: bottom;">
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 5px;">2024-2025</div>
                    @if(file_exists(public_path('Assets/logo-ciclo.png')))
                    <img src="{{ public_path('Assets/logo-ciclo.png') }}" alt="Logo Ciclo" style="width: 80px;">
                    @endif
                </td>
            </tr>
        </table>
        
        <table class="info-table">
               <tr>
<td class="label" style="width: 10%;">MATERIA:</td>
<td colspan="3">{{ $materia->nombre }}</td>
 </tr>
            <tr>
                <td class="label" style="width: 10%;">DOCENTE:</td>
                <td style="width: 50%;">{{ $nombreMaestro ?? 'Marina Emilia Flad' }}</td>
                <td class="label" style="width: 10%;">GRADO:</td>
                <td style="width: 30%;">{{ $grupo->grado->nombre ?? 'PRIMERO' }} {{ $grupo->nombre_grupo ? '- '.$grupo->nombre_grupo : '' }}</td>
            </tr>

         
        </table>

        <table style="width: 100%; border-collapse: collapse; border-spacing: 0;">
            <tr>
                <td style="width: 97%; padding:0; border:none; vertical-align: top;">
                    
                    <table class="main-table">
                        <thead>
                            <tr>
                                <th class="cell-num">#</th>
                                <th class="cell-alumno">ALUMNO</th>
                                @foreach($criterios as $criterio)
                                    <th style="text-transform: uppercase;">{{ $criterio['nombre'] }}</th>

                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                            <tr>
                                <td class="cell-num">{{ $loop->iteration }}</td>
                                <td class="cell-alumno">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}</td>
                                
                                @foreach($criterios as $criterio)
                                    <td>
                                        @php
                                            $calif = $calificaciones->get($alumno->alumno_id)
                                                                    ?->get($criterio['id'])
                                                                    ?->calificacion_obtenida;
                                        @endphp
                                        
                                        @if(is_numeric($calif))
                                            {{ number_format($calif, $criterio['es_promedio'] ? 2 : 1) }}
                                        @else
                                            NP
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </td>
                
                <td class="cell-vertical-container">
                    <div class="text-vertical">{{ strtoupper($periodo->nombre) }}</div>
                </td>
            </tr>
        </table>

        <!-- Resumen de Alumnos y Promedio -->
        <table style="width: 100%; margin-top: 15px; margin-bottom: 10px; border-collapse: collapse;">
            <tr>
                <td style="width: 25%; text-align: right; padding-right: 10px;">
                    <span style="font-weight: bold; font-size: 12px;">TOTAL ALUMNOS EVALUADOS</span>
                </td>
                <td style="width: 10%; text-align: center; padding: 0;">
                    <table style="border: 2px solid #000; margin: 0 auto; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0px 16px; font-weight: bold; font-size: 12px; text-align: center; border: none;">
                                {{ count($alumnos) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; text-align: center;"></td>
                <td style="width: 10%; text-align: right; padding-right: 10px;">
                    <span style="font-weight: bold; font-size: 12px;">PROMEDIO</span>
                </td>
                <td style="width: 10%; text-align: center; padding: 0;">
                    <table style="border: 2px solid #000; margin: 0 auto; border-collapse: collapse; background-color: #C0EA81;">
                        <tr>
                            <td style="padding: 0px 16px; font-weight: bold; font-size: 12px; text-align: center; border: none;">
                                {{ number_format($promedioGrupo ?? 0, 1) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 15%;"></td>
            </tr>
        </table>

        <div class="footer-firmas">
            <table>
                <tr>
                    <td>
                        <div class="firma-container">
                            <hr>
                            <div class="firma-texto">FIRMA DOCENTE</div>
                        </div>
                    </td>
                    <td>
                        <div class="firma-container">
                            <hr>
                            <div class="firma-texto">Vo.Bo. COORDINACIÓN ACADEMICA</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>
</html>