<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            /* CAMBIO 1: Quitamos el padding de abajo (20px 20px 0 20px) para evitar la hoja extra */
            padding: 20px 20px 0 20px; 
        }
        
        /* --- ENCABEZADO Y DATOS ALUMNO --- */
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
        
        /* --- MAQUETACIÓN DE COLUMNAS --- */
        .main-container {
            width: 100%;
        }
        
        .main-columns-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            page-break-inside: avoid;
        }

        /* Columna Izquierda (60%) */
        .main-left-td {
            width: 60%; 
            padding-right: 10px;
            vertical-align: top;
            border: none;
        }

        /* Columna Derecha (40%) */
        .main-right-td {
            width: 40%;
            padding-left: 10px;
            vertical-align: top;
            border: none;
        }

        /* --- ESTILOS DE TABLAS DE CALIFICACIONES --- */
        .boleta-v2 {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            text-align: center;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .boleta-v2 th,
        .boleta-v2 td {
            border: 1px solid #000;
            padding: 2px;
            height: 16px; 
        }
        .boleta-v2 thead .header-row-periodos th {
            background-color: #E0E0E0;
            font-weight: bold;
            font-size: 7px;
            padding: 3px;
        }
        .boleta-v2 thead .header-row-titulo th {
            background-color: #DDEBF7;
            font-weight: bold;
            font-size: 9px;
            text-align: left;
            padding-left: 5px;
        }
        .boleta-v2 thead .header-row-gray th {
            background-color: #E0E0E0;
            font-weight: bold;
            font-size: 8px;
            padding: 3px;
            text-align: center; 
        }
        .boleta-v2 thead .header-row-gray .header-materia {
            text-align: left;
            padding-left: 5px;
            font-size: 9px;
        }
        .boleta-v2 .materia-sep {
            background-color: #F5F5F5;
            text-align: left;
            font-weight: bold;
            padding-left: 10px;
        }
        .boleta-v2 .criterio-pas {
            text-align: left;
            font-weight: bold;
            padding-left: 5px;
        }
        .boleta-v2 .promedio-bloque-pas {
            background-color: #F3F3F3;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .boleta-v2 .promedio-final-combinado {
            background-color: #D9D9D9;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .boleta-v2 .cal-pas { font-weight: normal; }
        .boleta-v2 .cal-sep { 
            background-color: #E6E6FA;
            font-weight: bold;
            vertical-align: middle; 
        }
        .boleta-v2 .cal-prom-sep { 
            background-color: #E6E6FA;
            font-weight: bold;
            vertical-align: middle; 
        }
        .boleta-v2 .cal-prom-pas { background-color: #F3F3F3; font-weight: bold; }
        .asistencias-table { font-size: 7px; } 
        .asistencias-table .label { text-align: left; padding-left: 5px; }
        .asistencias-table .header-row-titulo th { background-color: #D9D9D9; } 
        .asistencias-table .header-row-periodos th { background-color: #F3F3F3; }
        
        .tutor-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            text-align: center;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .tutor-table th, .tutor-table td {
            border: 1px solid #000;
            padding: 2px;
            height: 25px; 
        }
        .tutor-table th {
            background-color: #E0E0E0;
            font-size: 8px;
        }

        /* --- ESTILOS NUEVOS PARA FIRMAS ABAJO --- */
        .signatures-bottom-container {
            width: 100%;
            /* CAMBIO 2: Reduje de 40px a 20px para ganar espacio y no saltar de hoja */
            margin-top: 20px; 
            page-break-inside: avoid;
        }
        
        .sig-col-table {
            width: 90%; 
            margin: 0 auto;
            border-collapse: collapse;
            text-align: center;
        }
        .sig-line-cell {
            border-bottom: 1px solid #000; 
            height: 1px; 
            width: 100%;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8px;
            padding-top: 3px;
        }
        .sig-title {
            font-size: 7px;
        }
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
            <table class="main-columns-table">
                <tr>
                    <td class="main-left-td">

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

                    </td>
                    
                    <td class="main-right-td">

                        @if(!empty($datosBloques['HÁBITOS']))
                            @php 
                                $bloque = $datosBloques['HÁBITOS'];
                                $colFinal = 'TOTAL';
                                $headerColor = '#FCE4D6';
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
                                </tbody>
                            </table>
                        @endif
                        
                        @if(!empty($datosBloques['HABITS']))
                            @php 
                                $bloque = $datosBloques['HABITS'];
                                $colFinal = 'TOTAL';
                                $headerColor = '#FFF2CC';
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

                    </td>
                </tr>
            </table>
            
            <div class="signatures-bottom-container">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr>
                                    <td class="sig-line-cell">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="sig-name">LIC. JULIETA YEE GONZALEZ M.ED</td>
                                </tr>
                                <tr>
                                    <td class="sig-title">DIRECTORA</td>
                                </tr>
                            </table>
                        </td>

                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr>
                                    <td class="sig-line-cell">&nbsp;</td> 
                                </tr>
                                <tr>
                                    <td class="sig-name">{{ isset($maestroEspanol) ? $maestroEspanol : 'LIC. [MAESTRO ESPAÑOL]' }}</td>
                                </tr>
                                <tr>
                                    <td class="sig-title">NOMBRE Y FIRMA DEL MAESTRO</td>
                                </tr>
                            </table>
                        </td>

                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr>
                                    <td class="sig-line-cell">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="sig-name">{{ isset($maestroIngles) ? $maestroIngles : 'LIC. [TEACHER\'S NAME]' }}</td>
                                </tr>
                                <tr>
                                    <td class="sig-title">TEACHER'S NAME AND SIGNATURE</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

        </div> 
    </div> 
</body>
</html>