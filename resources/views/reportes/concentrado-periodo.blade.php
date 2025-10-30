<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Concentrado de Calificaciones</title>
    <style>
        /* Fuentes y estilos generales */
        body {
            font-family: Arial, sans-serif;
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

        /* Pie de página (Firmas) */
        .footer-table {
            width: 100%;
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            border-collapse: collapse; /* Para que no tenga bordes */
        }
        .footer-table td {
            width: 50%;
            padding: 0 100px;
        }
        .firma-linea {
             padding-top: 40px; /* Espacio para la firma */
             width: 80%;
             border-top: 1px solid #000;
             font-weight: bold;
             
             
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
                                    <th>{{ strtoupper($criterio['nombre']) }}</th>
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

        <table class="footer-table">
            <tr>
                <td><div class="firma-linea">FIRMA DOCENTE</div></td>
                <td><div class="firma-linea">Vo.Bo. COORDINACIÓN ACADÉMICA</div></td>
            </tr>
        </table>

    </div>

</body>
</html>