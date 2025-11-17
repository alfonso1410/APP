<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 8px; /* Reducido para formato horizontal */
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* --- ENCABEZADO Y DATOS ALUMNO (Estilo original) --- */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .header-table td { vertical-align: middle; padding: 2px; }
        .logo-izquierda { width: 170px; }
        .logo-derecha { width: 60px; }
        .titulo-centro { text-align: center; font-weight: bold; }
        .titulo-centro .principal { font-size: 11px; }
        .titulo-centro .subtitulo { font-size: 9px; }
        .titulo-centro .concentrado { font-size: 9px; background-color: #E0E0E0; padding: 1px; }

        .info-alumno-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
        .info-alumno-table td { border: 1px solid #000; padding: 3px 6px; }
        .info-alumno-table .label { background-color: #eee; font-weight: bold; width: 15%; }
        
        /* --- CÓDIGO CRÍTICO MODIFICADO: ELIMINANDO FLOAT Y FORZANDO STACK VERTICAL --- */
        .main-container {
            width: 100%;
            overflow: auto; /* Clearfix */
            page-break-inside: avoid;
        }
        .main-left-column {
            width: 100%; /* Ahora ocupa el 100% */
            float: none; /* ELIMINADO EL FLOAT TÓXICO */
            display: block; 
            padding-right: 0; /* Eliminado el padding de separación */
            box-sizing: border-box;
        }
        .main-right-column {
            width: 100%; /* Ahora ocupa el 100% */
            float: none; /* ELIMINADO EL FLOAT TÓXICO */
            display: block; 
            padding-left: 0; /* Eliminado el padding de separación */
            margin-top: 15px; /* Separación entre el contenido izquierdo y derecho apilado */
            box-sizing: border-box;
        }

        /* --- NUEVO ESTILO BOLETA V2 (Tablas de Mosaico) --- */
        .boleta-v2 {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px; /* Letra pequeña para que quepa */
            text-align: center;
            margin-bottom: 8px; /* Espacio entre tablas */
            page-break-inside: avoid;
        }
        .boleta-v2 th,
        .boleta-v2 td {
            border: 1px solid #000;
            padding: 2px; /* Padding reducido */
            height: 16px; 
        }
        
        /* Encabezado de Periodos (1ER, 2DO, 3ER) - USADO EN BLOQUES DE CRITERIOS */
        .boleta-v2 thead .header-row-periodos th {
            background-color: #E0E0E0;
            font-weight: bold;
            font-size: 7px; /* Aún más pequeño */
            padding: 3px;
        }
        
        /* Encabezado del Bloque (ej. LENGUAJES) - AHORA AZUL */
        .boleta-v2 thead .header-row-titulo th {
            background-color: #DDEBF7; /* ¡¡AZUL!! */
            font-weight: bold;
            font-size: 9px;
            text-align: left;
            padding-left: 5px;
        }
        
        /* Filas de cabecera de periodos (ahora GRIS) */
        .boleta-v2 thead .header-row-gray th {
            background-color: #E0E0E0; /* ¡¡GRIS!! */
            font-weight: bold;
            font-size: 8px;
            padding: 3px;
            text-align: center; /* Asegurar centrado */
        }
         .boleta-v2 thead .header-row-gray .header-materia {
            text-align: left;
            padding-left: 5px;
            font-size: 9px; /* Un poco más grande */
         }

        /* Estilos de Celdas de Materias/Criterios */
        .boleta-v2 .materia-sep {
            background-color: #F5F5F5;
            text-align: left;
            font-weight: bold;
            padding-left: 10px; /* Indentación */
        }
        .boleta-v2 .criterio-pas {
            text-align: left;
            font-weight: bold;
            padding-left: 5px;
        }
        
        /* Fila de Campo Formativo (AHORA INVISIBLE) */
        .boleta-v2 .campo-sep-row { 
            display: none; /* Esta fila ya no se usa */
        }

        .boleta-v2 .promedio-bloque-pas {
            background-color: #F3F3F3;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .boleta-v2 .promedio-final-combinado {
            background-color: #D9D9D9; /* Gris */
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }

        /* Celdas de Calificaciones */
        .boleta-v2 .cal-pas { font-weight: normal; }
        
        .boleta-v2 .cal-sep { 
            background-color: #E6E6FA; /* Color unificado (Lavanda) */
            font-weight: bold;
            vertical-align: middle; /* Centrar con rowspan */
        }
        .boleta-v2 .cal-prom-sep { 
            background-color: #E6E6FA; /* Color unificado (Lavanda) */
            font-weight: bold;
            vertical-align: middle; /* Para centrar con rowspan */
        }
        
        .boleta-v2 .cal-prom-pas { background-color: #F3F3F3; font-weight: bold; }

        .empty-cell { background-color: #ffffff; border: 1px solid #000; }
        .empty-cell-light { border: 1px solid #ccc; }
        
        /* --- Títulos especiales de Bloque (Col. Derecha) --- */
        .header-habits {
            background-color: #C6E0B4; /* Verde oscuro */
            font-size: 10px;
            font-weight: bold;
            padding: 4px;
            text-align: center;
            border: 1px solid #000;
        }
        .header-english {
            background-color: #DDEBF7; /* Azul claro */
            font-size: 10px;
            font-weight: bold;
            padding: 4px;
            text-align: center;
            border: 1px solid #000;
        }

        /* --- Tabla de Asistencias --- */
        .asistencias-table { font-size: 7px; } /* Letra muy pequeña */
        .asistencias-table .label { text-align: left; padding-left: 5px; }
        .asistencias-table .header-row-titulo th { background-color: #D9D9D9; } /* Gris */
        .asistencias-table .header-row-periodos th { background-color: #F3F3F3; }

        /* --- NUEVOS ESTILOS PARA FIRMAS --- */
        /* Eliminamos float interno y usamos inline-block */
        .footer-container {
            width: 100%;
            overflow: auto; /* Clearfix */
            margin-top: 20px; 
            page-break-inside: avoid;
        }
        .footer-left {
            width: 54%;
            float: none; 
            display: inline-block;
            vertical-align: top;
            padding-right: 8px;
            box-sizing: border-box;
        }
        .footer-right {
            width: 46%;
            float: none; 
            display: inline-block;
            vertical-align: top;
            padding-left: 8px;
            box-sizing: border-box;
        }
        .tutor-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            text-align: center;
            margin-top: 20px; /* AJUSTE: Añadido para bajar la tabla */
        }
        .tutor-table th, .tutor-table td {
            border: 1px solid #000;
            padding: 3px;
            height: 35px; /* AJUSTE: Aumentado para más espacio */
        }
        .tutor-table th {
            background-color: #E0E0E0;
            font-size: 9px;
        }
        .signature-block {
            text-align: center;
            font-size: 9px;
            margin-top: 50px; /* AJUSTE: Aumentado para más espacio */
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto; /* Centrar línea */
        }
        .signature-name {
            font-weight: bold;
        }
        .signature-title {
            font-size: 8px;
        }
        /* --- FIN NUEVOS ESTILOS --- */
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td class="logo-izquierda" style="text-align: left;">
                    <img src="{{ public_path('Assets/logo-princeton.png') }}" alt="Logo" style="width: 170px;">
                </td>
                <td class="titulo-centro">
                    <div class="principal">"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</div>
                    <div class="subtitulo">SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</div>
                    <div class="concentrado">BOLETA DE EVALUACIÓN CICLO ESCOLAR: {{ $ciclo->nombre }}</div>
                </td>
                <td class="logo-derecha" style="text-align: right; vertical-align: bottom;">
                    <img src="{{ public_path('Assets/logo-azul.png') }}" alt="Logo Azul" style="width: 60px;"> 
                </td>
            </tr>
        </table>
        
        <table class="info-alumno-table">
            <tr>
                <td class="label">ALUMNO(A):</td>
                <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}</td>
                <td class="label">GRADO Y GRUPO:</td>
                <td>{{ $grupo->grado->nombre }} {{ $grupo->nombre_grupo }}</td>
            </tr>
            <tr>
                <td class="label">CURP:</td>
                <td>{{ $alumno->curp }}</td>
                <td class="label">NIVEL:</td>
                <td>{{ $grupo->grado->nivel->nombre }}</td>
            </tr>
        </table>
        

        <div class="main-container">

            <div class="main-left-column">

                @foreach($dataCamposSEP as $campo)
                    @php
                        $rowCountForSEP = (isset($campo['materias']) && is_array($campo['materias'])) ? count($campo['materias']) : 0; 
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo">
                                <th colspan="{{ 3 + (count($periodos) * 2) }}">{{ isset($campo['nombre']) ? $campo['nombre'] : 'Campo Formativo' }}</th>
                            </tr>
                            
                            <tr class="header-row-gray">
                                <th style="width: 30%;" class="header-materia">MATERIAS</th>
                                @foreach($periodos as $periodo)
                                    <th colspan="2">{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th colspan="2">PROMEDIO</th>
                            </tr>
                            <tr class="header-row-gray">
                                <th></th>
                                @foreach($periodos as $periodo)
                                    <th>PAS</th>
                                    <th>SEP</th>
                                @endforeach
                                <th>PAS</th>
                                <th>SEP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($campo['materias']) && is_array($campo['materias']))
                                @foreach($campo['materias'] as $materia)
                                    <tr>
                                        <td class="materia-sep">{{ isset($materia['nombre']) ? $materia['nombre'] : '' }}</td>
                                        
                                        @if ($loop->first)
                                            @foreach($periodos as $periodo)
                                                <td class="cal-pas">
                                                    {{ isset($materia['calificaciones_pas'][$periodo->periodo_id]) ? $materia['calificaciones_pas'][$periodo->periodo_id] : '' }}
                                                </td>
                                                <td class="cal-sep" rowspan="{{ $rowCountForSEP }}">
                                                    {{ isset($campo['calificaciones_sep'][$periodo->periodo_id]) ? $campo['calificaciones_sep'][$periodo->periodo_id] : '' }}
                                                </td>
                                            @endforeach
                                            <td class="cal-pas">
                                                {{ isset($materia['promedio_pas']) ? $materia['promedio_pas'] : '' }}
                                            </td>
                                            <td class="cal-prom-sep" rowspan="{{ $rowCountForSEP }}">
                                                {{ isset($campo['promedio_final_sep']) ? $campo['promedio_final_sep'] : '' }}
                                            </td>
                                        @else
                                            @foreach($periodos as $periodo)
                                                <td class="cal-pas">
                                                    {{ isset($materia['calificaciones_pas'][$periodo->periodo_id]) ? $materia['calificaciones_pas'][$periodo->periodo_id] : '' }}
                                                </td>
                                                @endforeach
                                            <td class="cal-pas">
                                                {{ isset($materia['promedio_pas']) ? $materia['promedio_pas'] : '' }}
                                            </td>
                                            @endif
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                @endforeach


                @if(!empty($datosBloques['PROGRAMA ACADEMICO']))
                    @php 
                        $bloque = $datosBloques['PROGRAMA ACADEMICO'];
                        $colFinal = 'PROMEDIO';
                        $headerColor = '#DDEBF7';
                        $showAverageRow = true;
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;"></th>
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if ($showAverageRow)
                                <tr class="promedio-bloque-pas">
                                    <td>PROMEDIO</td>
                                    @foreach($periodos as $periodo)
                                        <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                    @endforeach
                                    <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif

                @if(!empty($datosBloques['PROGRAMA PRINCETON']))
                    @php 
                        $bloque = $datosBloques['PROGRAMA PRINCETON'];
                        $colFinal = 'PROMEDIO';
                        $headerColor = '#DDEBF7';
                        $showAverageRow = true;
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;"></th>
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if ($showAverageRow)
                                <tr class="promedio-bloque-pas">
                                    <td>PROMEDIO</td>
                                    @foreach($periodos as $periodo)
                                        <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                    @endforeach
                                    <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif

                @if(!empty($promediosCombinadosAcademico))
                    <table class="boleta-v2">
                        <tbody>
                            <tr class="promedio-final-combinado">
                                <td style="width: 35%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO FINAL</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($promediosCombinadosAcademico[$periodo->periodo_id]) ? $promediosCombinadosAcademico[$periodo->periodo_id] : '' }}</td>
                                @endforeach
                                <td>{{ isset($promediosCombinadosAcademico['promedio']) ? $promediosCombinadosAcademico['promedio'] : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                @if(!empty($datosAsistencias))
                    <table class="boleta-v2 asistencias-table">
                        <thead>
                            <tr class="header-row-titulo">
                                <th colspan="{{ 2 + count($periodos) + 1 }}">CONTROL DE ASISTENCIAS // ATTENDANCE CONTROL</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 25%;">TRIMESTRE ---></th>
                                <th style="width: 10%;"></th> @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3" class="label" style="font-weight: bold;">ASISTENCIAS / ATTENDANCES</td>
                                <td class="label">ESP</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ESP_asistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_asistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ESP_asistencias']) ? $datosAsistencias['totales']['ESP_asistencias'] : '0' }}</td>
                            </tr>
                            <tr>
                                <td class="label">ENG</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ENG_asistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_asistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ENG_asistencias']) ? $datosAsistencias['totales']['ENG_asistencias'] : '0' }}</td>
                            </tr>
                            <tr style="font-weight: bold;">
                                <td class="label">Total</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_asistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_asistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['TOTAL_asistencias']) ? $datosAsistencias['totales']['TOTAL_asistencias'] : '0' }}</td>
                            </tr>
                            
                            <tr>
                                <td rowspan="3" class="label" style="font-weight: bold;">INASISTENCIAS / ABSENCES</td>
                                <td class="label">ESP</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ESP_inasistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_inasistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ESP_inasistencias']) ? $datosAsistencias['totales']['ESP_inasistencias'] : '0' }}</td>
                            </tr>
                            <tr>
                                <td class="label">ENG</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ENG_inasistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_inasistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ENG_inasistencias']) ? $datosAsistencias['totales']['ENG_inasistencias'] : '0' }}</td>
                            </tr>
                            <tr style="font-weight: bold;">
                                <td class="label">Total</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_inasistencias']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_inasistencias'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['TOTAL_inasistencias']) ? $datosAsistencias['totales']['TOTAL_inasistencias'] : '0' }}</td>
                            </tr>

                            <tr>
                                <td rowspan="3" class="label" style="font-weight: bold;">RETARDOS / DELAYS</td>
                                <td class="label">ESP</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ESP_retardos']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_retardos'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ESP_retardos']) ? $datosAsistencias['totales']['ESP_retardos'] : '0' }}</td>
                            </tr>
                            <tr>
                                <td class="label">ENG</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['ENG_retardos']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_retardos'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['ENG_retardos']) ? $datosAsistencias['totales']['ENG_retardos'] : '0' }}</td>
                            </tr>
                            <tr style="font-weight: bold;">
                                <td class="label">Total</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_retardos']) ? $datosAsistencias['periodos'][$periodo->periodo_id]['TOTAL_retardos'] : '0' }}</td>
                                @endforeach
                                <td>{{ isset($datosAsistencias['totales']['TOTAL_retardos']) ? $datosAsistencias['totales']['TOTAL_retardos'] : '0' }}</td>
                            </tr>

                        </tbody>
                    </table>
                @endif

            </div> <div class="main-right-column">

                @if(!empty($datosBloques['HÁBITOS']))
                    @php 
                        $bloque = $datosBloques['HÁBITOS'];
                        $colFinal = 'TOTAL';
                        $headerColor = '#FCE4D6';
                        $showAverageRow = false; 
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;"></th>
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if ($showAverageRow)
                                <tr class="promedio-bloque-pas">
                                    <td>PROMEDIO</td>
                                    @foreach($periodos as $periodo)
                                        <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                    @endforeach
                                    <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif
                
                @if(!empty($datosBloques['ENGLISH']))
                    @php
                        $bloque = $datosBloques['ENGLISH'];
                        $colFinal = 'FINAL';
                        $headerColor = '#DDEBF7';
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;">TRIMESTER</th> 
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr class="promedio-bloque-pas" style="background-color: #E2F0D9;"> 
                                <td>SEP AVERAGE</td> 
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                @endforeach
                                <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif


                @if(!empty($datosBloques['READING PROGRAM']))
                    @php 
                        $bloque = $datosBloques['READING PROGRAM'];
                        $colFinal = 'TOTAL';
                        $headerColor = '#FFF2CC';
                        $showAverageRow = false; 
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;"></th>
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if ($showAverageRow)
                                <tr class="promedio-bloque-pas">
                                    <td>PROMEDIO</td>
                                    @foreach($periodos as $periodo)
                                        <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                    @endforeach
                                    <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif
                
                @if(!empty($datosBloques['HABITS']))
                    @php 
                        $bloque = $datosBloques['HABITS'];
                        $colFinal = 'TOTAL';
                        $headerColor = '#FFF2CC';
                        $showAverageRow = false; // <-- Sin fila de promedio
                    @endphp
                    <table class="boleta-v2">
                        <thead>
                            <tr class="header-row-titulo" style="background-color: {{ $headerColor }};">
                                <th colspan="{{ 1 + count($periodos) + 1 }}">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                            </tr>
                            <tr class="header-row-periodos">
                                <th style="width: 35%;"></th>
                                @foreach($periodos as $periodo)
                                    <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th>
                                @endforeach
                                <th>{{ $colFinal }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($bloque['criterios']) && is_array($bloque['criterios']))
                                @foreach($bloque['criterios'] as $criterio)
                                    <tr>
                                        <td class="criterio-pas">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-pas">{{ isset($criterio['calificaciones'][$periodo->periodo_id]) ? $criterio['calificaciones'][$periodo->periodo_id] : '' }}</td>
                                        @endforeach
                                        <td class="cal-prom-pas">{{ isset($criterio['promedio']) ? $criterio['promedio'] : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if ($showAverageRow)
                                <tr class="promedio-bloque-pas">
                                    <td>PROMEDIO</td>
                                    @foreach($periodos as $periodo)
                                        <td>{{ isset($bloque['promedios_bloque'][$periodo->periodo_id]) ? $bloque['promedios_bloque'][$periodo->periodo_id] : '' }}</td>
                                    @endforeach
                                    <td>{{ isset($bloque['promedios_bloque']['promedio']) ? $bloque['promedios_bloque']['promedio'] : '' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif


                @if(!empty($promediosCombinadosHabits))
                    <table class="boleta-v2">
                        <tbody>
                            <tr class="promedio-bloque-pas" style="background-color: #F3F3F3;">
                                <td style="width: 35%; text-align: left; padding-left: 5px;">AVERAGE</td>
                                @foreach($periodos as $periodo)
                                    <td>{{ isset($promediosCombinadosHabits[$periodo->periodo_id]) ? $promediosCombinadosHabits[$periodo->periodo_id] : '' }}</td>
                                @endforeach
                                <td>{{ isset($promediosCombinadosHabits['promedio']) ? $promediosCombinadosHabits['promedio'] : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <div class="footer-left">
                    <table class="tutor-table">
                        <thead>
                            <tr>
                                <th colspan="4">FIRMA DEL PADRE O TUTOR</th>
                            </tr>
                            <tr>
                                <th>PERIODO</th>
                                <th>NOMBRE</th>
                                <th>FIRMA</th>
                                <th>FECHA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1ER</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>2DO</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>3RO</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="footer-right">
                    
                    <div class="signature-block"> 
                        <div class="signature-line"></div>
                        <div class="signature-name">LIC. JULIETA YEE GONZALEZ M.ED</div>
                        <div class="signature-title">DIRECTORA</div>
                    </div>

                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ isset($maestroEspanol) ? $maestroEspanol : 'LIC. [MAESTRO ESPAÑOL]' }}</div>
                        <div class="signature-title">NOMBRE Y FIRMA DEL MAESTRO</div>
                    </div>

                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ isset($maestroIngles) ? $maestroIngles : 'LIC. [TEACHER\'S NAME]' }}</div>
                        <div class="signature-title">TEACHER'S NAME AND SIGNATURE</div>
                    </div>

                </div>

            </div> </div> </div> </body>
</html>