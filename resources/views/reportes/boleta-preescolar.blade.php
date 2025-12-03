<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones - Preescolar</title>
    <style>
        /* Estilos CSS compatibles con mPDF */
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #000;
            margin: 0; 
            padding: 0;
        }
        .container {
            width: 195mm; 
            margin: 0 auto;
            padding: 0; 
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
        .main-container { width: 100%; }
        .main-columns-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: none; 
            page-break-inside: auto; 
        }
        
        .main-left-td { 
            width: 50%; 
            padding-right: 5px; 
            vertical-align: top; 
            border: none;
        } 
        .main-right-td { 
            width: 50%; 
            padding-left: 5px; 
            vertical-align: top; 
            border: none;
        }

        /* --- ESTILOS DE TABLAS --- */
        .boleta-v2 { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 8px; 
            text-align: center; 
            margin-bottom: 8px; 
            page-break-inside: avoid; 
        }
        .boleta-v2 th, .boleta-v2 td { border: 1px solid #000; padding: 2px; height: 16px; }
        
        .boleta-v2 th,
        .asistencias-table th,
        .tutor-table th {
            text-transform: uppercase;
        }
        
        .boleta-v2 thead .header-row-periodos th { background-color: #E0E0E0; font-weight: bold; font-size: 7px; padding: 3px; }
        .boleta-v2 thead .header-row-titulo th { font-weight: bold; font-size: 9px; text-align: center; padding-left: 5px; }
        
        .boleta-v2 .materia-sep { background-color: #ffffff; text-align: left; font-weight: bold; padding-left: 5px; }
        .boleta-v2 .criterio-pas { text-align: left; font-weight: bold; padding-left: 5px; }
        .boleta-v2 .promedio-bloque-pas { background-color: #F3F3F3; font-weight: bold; text-align: left; padding-left: 5px; }
        .boleta-v2 .promedio-final-combinado { background-color: #D9D9D9; font-weight: bold; text-align: left; padding-left: 5px; }
        .boleta-v2 .promedio-final-combinado td { font-weight: bold; } 

        .boleta-v2 .cal-pas { font-weight: normal; }
        .boleta-v2 .cal-total-col { font-weight: bold; } 
        .boleta-v2 .promedio-bloque-pas td { font-weight: bold; }
        
        .promedio-label-row td:first-child { font-weight: bold; }

        .promedio-general-preescolar td {
            font-weight: bold;
            background-color: #E0E0E0;
            text-align: center;
        }

        .asistencias-table { font-size: 7px; page-break-inside: avoid; } 
        .asistencias-table .label { text-align: left; padding: 2px 5px; }
        .asistencias-table .header-row-titulo th { background-color: #D9D9D9; } 
        .asistencias-table .header-row-periodos th { background-color: #F3F3F3; }
        
        .tutor-table { width: 100%; border-collapse: collapse; font-size: 7px; text-align: center; page-break-inside: avoid; }
        .tutor-table th, .tutor-table td { border: 1px solid #000; padding: 2px; height: 25px; }
        .tutor-table th { background-color: #E0E0E0; font-size: 8px; }

        .signatures-bottom-container { width: 100%; margin-top: 60px; page-break-inside: avoid; }
        .sig-col-table { width: 90%; margin: 0 auto; border-collapse: collapse; text-align: center; }
        .sig-line-cell { border-bottom: 1px solid #000; height: 1px; width: 100%; }
        .sig-name { font-weight: bold; font-size: 8px; padding-top: 3px; }
        .sig-title { font-size: 7px; }
        
        .equivalencias-table { width: 100%; border-collapse: collapse; font-size: 6px; text-align: center; margin-bottom: 10px; page-break-inside: avoid; }
        .equivalencias-table td { border: 1px solid #000; padding: 1px; }
        .equivalencias-table .head { background-color: #E0E0E0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- ENCABEZADO --}}
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
                    {{-- COLUMNA IZQUIERDA (Ancho 50%) --}}
                    <td class="main-left-td">

                        {{-- ==================== PREESCOLAR IZQUIERDA ==================== --}}
                        @php $totalCampos = count($dataCamposSEP); @endphp
                        @foreach($dataCamposSEP as $campo)
                            @php
                                $bgTitle = '#E0E0E0'; 
                                $n = strtoupper($campo['nombre']);
                                if(str_contains($n, 'LENGUAJES')) $bgTitle = '#FFE699'; 
                                elseif(str_contains($n, 'SABERES')) $bgTitle = '#BDD7EE'; 
                                elseif(str_contains($n, 'ÉTICA') || str_contains($n, 'ETICA')) $bgTitle = '#C6E0B4'; 
                                elseif(str_contains($n, 'HUMANO')) $bgTitle = '#E6D9EB'; 
                            @endphp

                            <table class="boleta-v2">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">
                                            {{ isset($campo['nombre']) ? $campo['nombre'] : 'Campo Formativo' }}
                                        </th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        {{-- AJUSTE DE ANCHOS PARA QUE SEAN IGUALES: 40% Nombre + 15% cada periodo (asumiendo 3) + 15% Final --}}
                                        <th style="width: 40%; text-align: left; padding-left: 5px;">MOMENTOS --></th>
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> 
                                        @endforeach
                                        <th style="width: 15%;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($campo['materias']) && is_array($campo['materias']))
                                        @foreach($campo['materias'] as $materia)
                                            <tr>
                                                <td class="materia-sep" style="background-color: #FFF;">{{ isset($materia['nombre']) ? $materia['nombre'] : '' }}</td>
                                                @foreach($periodos as $periodo)
                                                    <td class="cal-pas">{{ $materia['calificaciones_pas'][$periodo->periodo_id] ?? '' }}</td>
                                                @endforeach
                                                <td class="cal-pas cal-total-col">{{ $materia['promedio_pas'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @endforeach
                        
                        {{-- INICIO: TABLA PROMEDIO GENERAL (SEP) PREESCOLAR --}}
                        @if($totalCampos > 0 && !empty($promediosGeneralesPreescolar))
                            <table class="boleta-v2">
                                <tbody>
                                    <tr class="promedio-final-combinado promedio-label-row">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO GENERAL S.E.P.</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-total-col">{{ $promediosGeneralesPreescolar[$periodo->periodo_id] ?? '' }}</td>
                                        @endforeach
                                        <td class="cal-total-col">{{ $promediosGeneralesPreescolar['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        {{-- INICIO: TABLA PROGRAMA ACADEMICO / PROGRAMA DE LECTURA (Preescolar) --}}
                        @if(!empty($datosBloques[$bloqueAcademicoKey]))
                            @php
                                $bloque = $datosBloques[$bloqueAcademicoKey];
                                $bgTitle = '#F8CBAD';
                            @endphp
                            <table class="boleta-v2">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">
                                            {{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}
                                        </th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        <th style="width: 40%; text-align: left; padding-left: 5px;">MOMENTOS --></th>
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> 
                                        @endforeach
                                        <th style="width: 15%;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($bloque['criterios']))
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas" style="width: 40%;">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                <td class="cal-pas cal-total-col">{{ $criterio['promedio'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="promedio-bloque-pas">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO GENERAL P.A.</td>
                                        @foreach($periodos as $periodo) <td>{{ $bloque['promedios_bloque'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                        <td class="cal-total-col">{{ $bloque['promedios_bloque']['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        @if(!empty($dataPrinceton))
                            @foreach($dataPrinceton as $campo)
                                <table class="boleta-v2" style="font-size: 7px;">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #BDD7EE; text-align: center;">
                                                {{ isset($campo['nombre']) ? $campo['nombre'] : 'PROGRAMA PRINCETON' }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="header-row-periodos">
                                            <th style="width: 40%; text-align: left; padding-left: 5px;">MOMENTOS --></th>
                                            @foreach($periodos as $periodo) 
                                                <th style="width: 15%;">{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> 
                                            @endforeach
                                            <th style="width: 15%;">TOTAL</th>
                                        </tr>
                                        @if(isset($campo['materias']))
                                            @foreach($campo['materias'] as $materia)
                                                <tr>
                                                    <td class="criterio-pas" style="width: 40%;">{{ isset($materia['nombre']) ? $materia['nombre'] : '' }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ $materia['calificaciones_pas'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                    <td class="cal-pas cal-total-col">{{ $materia['promedio_pas'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                            {{-- FILA DE PROMEDIO PARA PREESCOLAR PRINCETON --}}
                                            <tr class="promedio-bloque-pas">
                                                <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO GENERAL P.P.</td>
                                                @foreach($periodos as $periodo) <td class="cal-total-col">{{ $promediosPrinceton[$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                <td class="cal-total-col">{{ $promediosPrinceton['promedio'] ?? '' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endforeach
                        @endif
                        
                        {{-- INICIO: TABLA PROMEDIO FINAL (Combinado) PREESCOLAR --}}
                        @if(!empty($promediosCombinadosAcademico))
                            <table class="boleta-v2">
                                <tbody>
                                    <tr class="promedio-final-combinado promedio-label-row">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO FINAL</td>
                                        @foreach($periodos as $periodo) <td class="cal-total-col">{{ $promediosCombinadosAcademico[$periodo->periodo_id] ?? '' }}</td> @endforeach
                                        <td class="cal-total-col">{{ $promediosCombinadosAcademico['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        {{-- PREESCOLAR: TABLA DE FIRMAS --}}
                        <table class="tutor-table">
                            <thead>
                                <tr><th colspan="4">FIRMA DEL PADRE O TUTOR</th></tr>
                                <tr>
                                    <th style="width: 13%;">PERIODO</th> 
                                    <th style="width: 45%;">NOMBRE</th> 
                                    <th style="width: 22%;">FIRMA</th>
                                    <th style="width: 20%;">FECHA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1ER</td><td></td><td></td><td></td></tr>
                                <tr><td>2DO</td><td></td><td></td><td></td></tr>
                                <tr><td>3RO</td><td></td><td></td><td></td></tr>
                            </tbody>
                        </table>

                    </td>
                    
                    {{-- COLUMNA DERECHA (Ancho 50%) --}}
                    <td class="main-right-td">

                        {{-- ==================== PREESCOLAR DERECHA ==================== --}}
                        
                        {{-- TABLA HÁBITOS (ESPAÑOL) --}}
                        @if(!empty($datosBloques['HÁBITOS']))
                            @php 
                                $bloque = $datosBloques['HÁBITOS'];
                                $bgTitle = '#FFE699'; 
                            @endphp
                            <table class="boleta-v2">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">{{ isset($bloque['titulo']) ? $bloque['titulo'] : 'HÁBITOS' }}</th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        {{-- ANCHOS: 40% Nombre, 15% Periodos, 15% Total --}}
                                        <th style="width: 40%;">MOMENTOS --></th> 
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">{{ $periodo->nombre }}</th> 
                                        @endforeach 
                                        <th style="width: 15%;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($bloque['criterios']))
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                <td class="cal-prom-pas cal-total-col">{{ $criterio['promedio'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="promedio-bloque-pas">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">"PROMEDIO G.H.</td>
                                        @foreach($periodos as $periodo) <td class="cal-total-col">{{ $bloque['promedios_bloque'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                        <td class="cal-total-col">{{ $bloque['promedios_bloque']['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        {{-- NUEVA SECCIÓN: ESCUELA PARA PADRES --}}
                        @if(!empty($dataEscuelaPadres))
                            <table class="boleta-v2">
                                <tbody>
                                    <tr class="promedio-final-combinado promedio-label-row">
                                        <td style="width: 40%; text-align: left; padding-left: 5px;">{{ isset($dataEscuelaPadres['nombre']) ? $dataEscuelaPadres['nombre'] : 'Escuela para Padres' }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="cal-total-col">{{ $dataEscuelaPadres['calificaciones'][$periodo->periodo_id] ?? '' }}</td>
                                        @endforeach
                                        <td class="cal-total-col">{{ $dataEscuelaPadres['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        {{-- TABLA ENGLISH --}}
                        @if(!empty($datosBloques['ENGLISH']))
                            @php 
                                $bloque = $datosBloques['ENGLISH'];
                                $bgTitle = '#C6E0B4'; 
                            @endphp
                            <table class="boleta-v2">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">ENGLISH</th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        <th style="width: 40%;">MOMENTS</th> 
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">{{ $periodo->nombre }}</th> 
                                        @endforeach 
                                        <th style="width: 15%;">FINAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($bloque['criterios']))
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                <td class="cal-prom-pas cal-total-col">{{ $criterio['promedio'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="promedio-bloque-pas">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">SEP AVERAGE</td>
                                        @foreach($periodos as $periodo) <td class="cal-total-col">{{ $bloque['promedios_bloque'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                        <td class="cal-total-col">{{ $bloque['promedios_bloque']['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        {{-- TABLA HABITS (INGLÉS) --}}
                        @if(!empty($datosBloques['HABITS']))
                            @php 
                                $bloque = $datosBloques['HABITS'];
                                $bgTitle = '#BDD7EE'; 
                            @endphp
                            <table class="boleta-v2">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">{{ isset($bloque['titulo']) ? $bloque['titulo'] : 'HABITS' }}</th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        <th style="width: 40%;">MOMENTS --></th> 
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">{{ $periodo->nombre }}</th> 
                                        @endforeach 
                                        <th style="width: 15%;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($bloque['criterios']))
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                <td class="cal-prom-pas cal-total-col">{{ $criterio['promedio'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="promedio-bloque-pas">
                                        <td style="width: 40%; text-align: left; padding-left: 5px; font-size: 9px;">AVERAGE HABITS</td>
                                        @foreach($periodos as $periodo) <td class="cal-total-col">{{ $bloque['promedios_bloque'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                        <td class="cal-total-col">{{ $bloque['promedios_bloque']['promedio'] ?? '' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                        
                        {{-- PREESCOLAR: TABLA DE ASISTENCIAS --}}
                        @if(!empty($datosAsistencias))
                            <table class="boleta-v2 asistencias-table" style="margin-top: 20px;">
                                <thead>
                                    <tr class="header-row-titulo">
                                        <th colspan="{{ 1 + count($periodos) + 1 }}">CONTROL DE ASISTENCIAS // ATTENDANCE CONTROL</th>
                                    </tr>
                                    <tr class="header-row-periodos">
                                        <th style="width: 40%;">MOMENTO ----></th>
                                        @foreach($periodos as $periodo) 
                                            <th style="width: 15%;">ESP/ENG</th> 
                                        @endforeach
                                        <th style="width: 15%;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="1" class="label">ASISTENCIAS / ATTENDANCES</td>
                                        @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_asistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_asistencias'] ?? 0 }}</td> @endforeach
                                        <td class="cal-total-col">{{ $datosAsistencias['totales']['TOTAL_asistencias'] ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="1" class="label">RETARDOS / DELAYS</td>
                                        @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_retardos'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_retardos'] ?? 0 }}</td> @endforeach
                                        <td class="cal-total-col">{{ $datosAsistencias['totales']['TOTAL_retardos'] ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="1" class="label">INASISTENCIAS / ABSENCES</td>
                                        @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_inasistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_inasistencias'] ?? 0 }}</td> @endforeach
                                        <td class="cal-total-col">{{ $datosAsistencias['totales']['TOTAL_inasistencias'] ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                        
                        {{-- PREESCOLAR: EQUIVALENCIAS --}}
                        <table class="equivalencias-table" style="margin-top: 20px;">
                            <tr><td class="head">EQUIVALENCIAS</td></tr>
                            <tr><td>E- EXCELENTE (10)</td></tr>
                            <tr><td>MB- MUY BIEN (9)</td></tr>
                            <tr><td>B- BIEN (8)</td></tr>
                            <tr><td>R- REGULAR (7 Y 6)</td></tr> 
                            <tr><td>NA- NO ACREDITO</td></tr>
                            <tr><td>NP- NO PRESENTO</td></tr>
                        </table>

                    </td>
                </tr>
            </table>
            
            {{-- FIRMAS ABAJO --}}
            <div class="signatures-bottom-container">
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr><td class="sig-line-cell">&nbsp;</td></tr>
                                <tr><td class="sig-name">LIC. JULIETA YEE GONZALEZ M.ED</td></tr>
                                <tr><td class="sig-title">DIRECTORA</td></tr>
                            </table>
                        </td>
                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr><td class="sig-line-cell">&nbsp;</td></tr>
                                <tr><td class="sig-name">{{ isset($maestroEspanol) ? $maestroEspanol : 'LIC. [MAESTRO ESPAÑOL]' }}</td></tr>
                                <tr><td class="sig-title">NOMBRE Y FIRMA DEL MAESTRO</td></tr>
                            </table>
                        </td>
                        <td style="width: 33%; vertical-align: bottom; padding: 0 10px;">
                            <table class="sig-col-table">
                                <tr><td class="sig-line-cell">&nbsp;</td></tr>
                                <tr><td class="sig-name">{{ isset($maestroIngles) ? $maestroIngles : 'LIC. [TEACHER\'S NAME]' }}</td></tr>
                                <tr><td class="sig-title">TEACHER'S NAME AND SIGNATURE</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

        </div> 
    </div> 
</body>
</html>