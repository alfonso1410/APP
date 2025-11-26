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
        .main-container { width: 100%; }
        .main-columns-table { width: 100%; border-collapse: collapse; border: none; page-break-inside: avoid; }
        .main-left-td { width: 60%; padding-right: 10px; vertical-align: top; border: none; }
        .main-right-td { width: 40%; padding-left: 10px; vertical-align: top; border: none; }

        /* --- ESTILOS DE TABLAS --- */
        .boleta-v2 { width: 100%; border-collapse: collapse; font-size: 8px; text-align: center; margin-bottom: 8px; page-break-inside: avoid; }
        .boleta-v2 th, .boleta-v2 td { border: 1px solid #000; padding: 2px; height: 16px; }
        
        .boleta-v2 thead .header-row-periodos th { background-color: #E0E0E0; font-weight: bold; font-size: 7px; padding: 3px; }
        .boleta-v2 thead .header-row-titulo th { font-weight: bold; font-size: 9px; text-align: center; padding-left: 5px; }
        
        .boleta-v2 thead .header-row-gray th { background-color: #E0E0E0; font-weight: bold; font-size: 8px; padding: 3px; text-align: center; }
        .boleta-v2 thead .header-row-gray .header-materia { text-align: left; padding-left: 5px; font-size: 9px; }

        .boleta-v2 .materia-sep { background-color: #ffffff; text-align: left; font-weight: bold; padding-left: 10px; }
        .primaria-style .materia-sep { background-color: #F5F5F5; }

        .boleta-v2 .criterio-pas { text-align: left; font-weight: bold; padding-left: 5px; }
        .boleta-v2 .promedio-bloque-pas { background-color: #F3F3F3; font-weight: bold; text-align: left; padding-left: 5px; }
        .boleta-v2 .promedio-final-combinado { background-color: #D9D9D9; font-weight: bold; text-align: left; padding-left: 5px; }
        
        .boleta-v2 .cal-pas { font-weight: normal; }
        .boleta-v2 .cal-sep { background-color: #E6E6FA; font-weight: bold; vertical-align: middle; }
        .boleta-v2 .cal-prom-sep { background-color: #E6E6FA; font-weight: bold; vertical-align: middle; }
        .boleta-v2 .cal-prom-pas { background-color: #F3F3F3; font-weight: bold; }
        
        .asistencias-table { font-size: 7px; } 
        .asistencias-table .label { text-align: left; padding-left: 5px; }
        .asistencias-table .header-row-titulo th { background-color: #D9D9D9; } 
        .asistencias-table .header-row-periodos th { background-color: #F3F3F3; }
        
        .tutor-table { width: 100%; border-collapse: collapse; font-size: 7px; text-align: center; margin-top: 15px; page-break-inside: avoid; }
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
                    {{-- COLUMNA IZQUIERDA --}}
                    <td class="main-left-td">

                        @if($esPreescolar)
                            {{-- ==================== PREESCOLAR IZQUIERDA ==================== --}}

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
                                            <th style="width: 40%; text-align: left; padding-left: 5px;">MOMENTOS --></th>
                                            @foreach($periodos as $periodo) <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> @endforeach
                                            <th>PROMEDIO FINAL</th>
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
                                                    <td class="cal-pas" style="font-weight: bold;">{{ $materia['promedio_pas'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            @endforeach

                            @php $nombreBloqueAcademico = $esPK1 ? 'PROGRAMA DE LECTURA' : 'PROGRAMA ACADEMICO'; @endphp
                            @if(!empty($datosBloques[$nombreBloqueAcademico]))
                                @php
                                    $bloque = $datosBloques[$nombreBloqueAcademico];
                                    $bgTitle = '#F8CBAD';
                                @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">
                                                {{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($bloque['criterios']))
                                            @foreach($bloque['criterios'] as $criterio)
                                                <tr>
                                                    <td class="criterio-pas" style="width: 40%;">{{ isset($criterio['nombre']) ? $criterio['nombre'] : '' }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                    <td class="cal-pas" style="font-weight: bold;">{{ $criterio['promedio'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($dataPrinceton))
                                @foreach($dataPrinceton as $campo)
                                    <table class="boleta-v2">
                                        <thead>
                                            <tr class="header-row-titulo">
                                                <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #BDD7EE; text-align: center;">
                                                    {{ isset($campo['nombre']) ? $campo['nombre'] : 'Programa Princeton' }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($campo['materias']))
                                                @foreach($campo['materias'] as $materia)
                                                    <tr>
                                                        <td class="criterio-pas" style="width: 40%;">{{ isset($materia['nombre']) ? $materia['nombre'] : '' }}</td>
                                                        @foreach($periodos as $periodo) <td class="cal-pas">{{ $materia['calificaciones_pas'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                        <td class="cal-pas" style="font-weight: bold;">{{ $materia['promedio_pas'] ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                @endforeach
                            @endif

                            {{-- PREESCOLAR: TABLA DE FIRMAS (MOVIDA AQUI DESDE LA DERECHA) --}}
                            <table class="tutor-table">
                                <thead>
                                    <tr><th colspan="4">FIRMA DEL PADRE O TUTOR</th></tr>
                                    <tr>
                                        <th style="width: 10%;">PERIODO</th> {{-- Periodo más corto --}}
                                        <th style="width: 50%;">NOMBRE</th>  {{-- Nombre más largo --}}
                                        <th style="width: 20%;">FIRMA</th>
                                        <th style="width: 20%;">FECHA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>1ER</td><td></td><td></td><td></td></tr>
                                    <tr><td>2DO</td><td></td><td></td><td></td></tr>
                                    <tr><td>3RO</td><td></td><td></td><td></td></tr>
                                </tbody>
                            </table>

                            {{-- PREESCOLAR: EQUIVALENCIAS (SE MANTIENE AQUI) --}}
                            <table class="equivalencias-table" style="margin-top: 10px;">
                                <tr><td class="head">EQUIVALENCIAS</td></tr>
                                <tr><td>E- EXCELENTE (10)</td></tr>
                                <tr><td>MB- MUY BIEN (9)</td></tr>
                                <tr><td>B- BIEN (8)</td></tr>
                                <tr><td>R- REGULAR (7 Y 6)</td></tr>
                                <tr><td>NA- NO ACREDITO</td></tr>
                                <tr><td>NP- NO PRESENTO</td></tr>
                            </table>

                        @else
                            {{-- ==================== PRIMARIA IZQUIERDA ==================== --}}
                            
                            @foreach($dataCamposSEP as $campo)
                                @php 
                                    $rowCountForSEP = (isset($campo['materias']) && is_array($campo['materias'])) ? count($campo['materias']) : 0;
                                    
                                    // Lógica de colores para Primaria igual a la imagen
                                    $bgTitle = '#DDEBF7'; 
                                    $n = strtoupper($campo['nombre']);
                                    if(str_contains($n, 'LENGUAJES')) $bgTitle = '#FFE699'; 
                                    elseif(str_contains($n, 'SABERES')) $bgTitle = '#BDD7EE'; 
                                    elseif(str_contains($n, 'ÉTICA') || str_contains($n, 'ETICA')) $bgTitle = '#C6E0B4'; 
                                    elseif(str_contains($n, 'HUMANO')) $bgTitle = '#E6D9EB'; 
                                @endphp
                                <table class="boleta-v2 primaria-style">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 3 + (count($periodos) * 2) }}" style="background-color: {{ $bgTitle }}; text-align: left; padding-left: 5px;">{{ isset($campo['nombre']) ? $campo['nombre'] : 'Campo Formativo' }}</th>
                                        </tr>
                                        <tr class="header-row-gray">
                                            <th style="width: 30%;" class="header-materia">MATERIAS</th>
                                            @foreach($periodos as $periodo) <th colspan="2">{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> @endforeach
                                            <th colspan="2">PROMEDIO</th>
                                        </tr>
                                        <tr class="header-row-gray">
                                            <th></th>
                                            @foreach($periodos as $periodo) <th>PAS</th> <th>SEP</th> @endforeach
                                            <th>PAS</th> <th>SEP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($campo['materias']))
                                            @foreach($campo['materias'] as $materia)
                                                <tr>
                                                    <td class="materia-sep">{{ isset($materia['nombre']) ? $materia['nombre'] : '' }}</td>
                                                    @if ($loop->first)
                                                        @foreach($periodos as $periodo)
                                                            <td class="cal-pas">{{ isset($materia['calificaciones_pas'][$periodo->periodo_id]) && is_numeric($materia['calificaciones_pas'][$periodo->periodo_id]) ? round($materia['calificaciones_pas'][$periodo->periodo_id], 1) + 0 : '' }}</td>
                                                            <td class="cal-sep" rowspan="{{ $rowCountForSEP }}">{{ isset($campo['calificaciones_sep'][$periodo->periodo_id]) && is_numeric($campo['calificaciones_sep'][$periodo->periodo_id]) ? round($campo['calificaciones_sep'][$periodo->periodo_id], 1) + 0 : '' }}</td>
                                                        @endforeach
                                                        <td class="cal-pas">{{ isset($materia['promedio_pas']) && is_numeric($materia['promedio_pas']) ? round($materia['promedio_pas'], 1) + 0 : '' }}</td>
                                                        <td class="cal-prom-sep" rowspan="{{ $rowCountForSEP }}">{{ isset($campo['promedio_final_sep']) && is_numeric($campo['promedio_final_sep']) ? round($campo['promedio_final_sep'], 1) + 0 : '' }}</td>
                                                    @else
                                                        @foreach($periodos as $periodo)
                                                            <td class="cal-pas">{{ isset($materia['calificaciones_pas'][$periodo->periodo_id]) && is_numeric($materia['calificaciones_pas'][$periodo->periodo_id]) ? round($materia['calificaciones_pas'][$periodo->periodo_id], 1) + 0 : '' }}</td>
                                                        @endforeach
                                                        <td class="cal-pas">{{ isset($materia['promedio_pas']) && is_numeric($materia['promedio_pas']) ? round($materia['promedio_pas'], 1) + 0 : '' }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            @endforeach

                            @if(!empty($promediosGeneralesSEP))
                                <table class="boleta-v2">
                                    <tbody>
                                        <tr style="background-color: #E0E0E0; font-weight: bold; border-top: 2px solid #000;">
                                            <td style="width: 30%; text-align: center;">PROMEDIO GENERAL</td>
                                            @foreach($periodos as $periodo) <td class="cal-pas"></td> <td class="cal-prom-sep">{{ isset($promediosGeneralesSEP[$periodo->periodo_id]) ? $promediosGeneralesSEP[$periodo->periodo_id] + 0 : '' }}</td> @endforeach
                                            <td class="cal-pas"></td> <td class="cal-prom-sep">{{ isset($promediosGeneralesSEP['final']) ? $promediosGeneralesSEP['final'] + 0 : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($datosBloques['PROGRAMA ACADEMICO']))
                                @php $bloque = $datosBloques['PROGRAMA ACADEMICO']; @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #C6E0B4;">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ isset($periodo->nombre) ? $periodo->nombre : '' }}</th> @endforeach <th>PROMEDIO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($bloque['criterios']))
                                            @foreach($bloque['criterios'] as $criterio)
                                                <tr>
                                                    <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($criterio['calificaciones'][$periodo->periodo_id]) ? round($criterio['calificaciones'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                    <td class="cal-prom-pas">{{ is_numeric($criterio['promedio']) ? round($criterio['promedio'], 1)+0 : '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        <tr class="promedio-bloque-pas">
                                            <td>PROMEDIO</td>
                                            @foreach($periodos as $periodo) <td>{{ is_numeric($bloque['promedios_bloque'][$periodo->periodo_id]) ? round($bloque['promedios_bloque'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                            <td>{{ is_numeric($bloque['promedios_bloque']['promedio']) ? round($bloque['promedios_bloque']['promedio'], 1)+0 : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($dataPrinceton))
                                @foreach($dataPrinceton as $campo)
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #B4C6E7;">{{ $campo['nombre'] }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>PROMEDIO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($campo['materias'] as $materia)
                                            <tr>
                                                <td class="criterio-pas" style="font-weight: bold;">{{ $materia['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($materia['calificaciones_pas'][$periodo->periodo_id]) ? round($materia['calificaciones_pas'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                <td class="cal-prom-pas">{{ is_numeric($materia['promedio_pas']) ? round($materia['promedio_pas'], 1)+0 : '' }}</td>
                                            </tr>
                                        @endforeach

                                        {{-- NUEVA FILA DE PROMEDIO PRINCETON --}}
                                        <tr class="promedio-bloque-pas">
                                            <td>PROMEDIO</td>
                                            @foreach($periodos as $periodo) 
                                                <td>{{ isset($promediosPrinceton[$periodo->periodo_id]) ? $promediosPrinceton[$periodo->periodo_id] : '' }}</td> 
                                            @endforeach
                                            <td>{{ isset($promediosPrinceton['promedio']) ? $promediosPrinceton['promedio'] : '' }}</td>
                                        </tr>
                                        {{-- FIN NUEVA FILA --}}

                                    </tbody>
                                </table>
                                @endforeach
                            @endif

                            @if(!empty($promediosCombinadosAcademico))
                                <table class="boleta-v2">
                                    <tbody>
                                        <tr class="promedio-final-combinado">
                                            <td style="width: 35%; text-align: left; padding-left: 5px; font-size: 9px;">PROMEDIO FINAL</td>
                                            @foreach($periodos as $periodo) <td>{{ is_numeric($promediosCombinadosAcademico[$periodo->periodo_id]) ? round($promediosCombinadosAcademico[$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                            <td>{{ is_numeric($promediosCombinadosAcademico['promedio']) ? round($promediosCombinadosAcademico['promedio'], 1)+0 : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            {{-- PRIMARIA: TABLA DE FIRMAS (MOVIDA AQUI DESDE LA DERECHA) --}}
                            <table class="tutor-table">
                                <thead>
                                    <tr><th colspan="4">FIRMA DEL PADRE O TUTOR</th></tr>
                                    <tr>
                                        <th style="width: 10%;">PERIODO</th> {{-- Periodo más corto --}}
                                        <th style="width: 50%;">NOMBRE</th>  {{-- Nombre más largo --}}
                                        <th style="width: 20%;">FIRMA</th>
                                        <th style="width: 20%;">FECHA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>1ER</td><td></td><td></td><td></td></tr>
                                    <tr><td>2DO</td><td></td><td></td><td></td></tr>
                                    <tr><td>3RO</td><td></td><td></td><td></td></tr>
                                </tbody>
                            </table>

                        @endif

                    </td>
                    
                    {{-- COLUMNA DERECHA --}}
                    <td class="main-right-td">

                        @if($esPreescolar)
                            {{-- ==================== PREESCOLAR DERECHA ==================== --}}

                            @if(!empty($datosBloques['HÁBITOS']))
                                @php 
                                    $bloque = $datosBloques['HÁBITOS'];
                                    $bgTitle = '#F8CBAD'; 
                                @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;">MOMENTOS --></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($bloque['criterios']))
                                            @foreach($bloque['criterios'] as $criterio)
                                                <tr>
                                                    <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                    <td class="cal-prom-pas">{{ $criterio['promedio'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($datosBloques['ENGLISH']))
                                @php 
                                    $bloque = $datosBloques['ENGLISH'];
                                    $bgTitle = '#DDEBF7'; 
                                @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">ENGLISH</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;">MOMENTS</th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>FINAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($bloque['criterios']))
                                            @foreach($bloque['criterios'] as $criterio)
                                                <tr>
                                                    <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                    <td class="cal-prom-pas">{{ $criterio['promedio'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        <tr class="promedio-bloque-pas">
                                            <td>SEP AVERAGE</td>
                                            @foreach($periodos as $periodo) <td>{{ $bloque['promedios_bloque'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                            <td>{{ $bloque['promedios_bloque']['promedio'] ?? '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($datosBloques['HABITS']))
                                @php 
                                    $bloque = $datosBloques['HABITS'];
                                    $bgTitle = '#C6E0B4';
                                @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: {{ $bgTitle }}; text-align: center;">{{ isset($bloque['titulo']) ? $bloque['titulo'] : '' }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($bloque['criterios']))
                                            @foreach($bloque['criterios'] as $criterio)
                                                <tr>
                                                    <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                    @foreach($periodos as $periodo) <td class="cal-pas">{{ $criterio['calificaciones'][$periodo->periodo_id] ?? '' }}</td> @endforeach
                                                    <td class="cal-prom-pas">{{ $criterio['promedio'] ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                            
                            {{-- PREESCOLAR: TABLA DE ASISTENCIAS (MOVIDA AQUI DESDE LA IZQUIERDA Y SIN LA 2DA COLUMNA) --}}
                            @if(!empty($datosAsistencias))
                                <table class="boleta-v2 asistencias-table" style="margin-top: 15px;">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            {{-- Colspan ajustado: 1(label) + Periodos + 1(Total) --}}
                                            <th colspan="{{ 1 + count($periodos) + 1 }}">CONTROL DE ASISTENCIAS // ATTENDANCE CONTROL</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;">MOMENTO ----></th>
                                            @foreach($periodos as $periodo) <th>ESP/ENG</th> @endforeach
                                            <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">ASISTENCIAS / ATTENDANCES</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_asistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_asistencias'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_asistencias'] ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">RETARDOS / DELAYS</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_retardos'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_retardos'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_retardos'] ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">INASISTENCIAS / ABSENCES</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_inasistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_inasistencias'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_inasistencias'] ?? 0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            {{-- FIRMA DEL ALUMNO (Debajo de asistencias) --}}
                            <table style="width: 60%; margin: 40px auto 10px auto; border-collapse: collapse; text-align: center; page-break-inside: avoid;">
                                <tr>
                                    <td style="border-bottom: 1px solid #000; height: 1px;"></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 7px; font-weight: bold; padding-top: 2px;">FIRMA DEL ALUMNO</td>
                                </tr>
                            </table>

                        @else
                            {{-- ==================== PRIMARIA DERECHA ==================== --}}

                            @if(!empty($datosBloques['HÁBITOS']))
                                @php $bloque = $datosBloques['HÁBITOS']; @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #FCE4D6;">{{ $bloque['titulo'] }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($criterio['calificaciones'][$periodo->periodo_id]) ? round($criterio['calificaciones'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                <td class="cal-prom-pas">{{ is_numeric($criterio['promedio']) ? round($criterio['promedio'], 1)+0 : '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($datosBloques['ENGLISH']))
                                @php $bloque = $datosBloques['ENGLISH']; @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #DDEBF7;">ENGLISH</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;">TRIMESTER</th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>FINAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($criterio['calificaciones'][$periodo->periodo_id]) ? round($criterio['calificaciones'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                <td class="cal-prom-pas">{{ is_numeric($criterio['promedio']) ? round($criterio['promedio'], 1)+0 : '' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="promedio-bloque-pas" style="background-color: #DDEBF7;">
                                            <td>SEP AVERAGE</td>
                                            @foreach($periodos as $periodo) <td>{{ is_numeric($bloque['promedios_bloque'][$periodo->periodo_id]) ? round($bloque['promedios_bloque'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                            <td>{{ is_numeric($bloque['promedios_bloque']['promedio']) ? round($bloque['promedios_bloque']['promedio'], 1)+0 : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            @if(!empty($datosBloques['READING PROGRAM']))
                                @php $bloque = $datosBloques['READING PROGRAM']; @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #DDEBF7;">{{ $bloque['titulo'] }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($criterio['calificaciones'][$periodo->periodo_id]) ? round($criterio['calificaciones'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                <td class="cal-prom-pas">{{ is_numeric($criterio['promedio']) ? round($criterio['promedio'], 1)+0 : '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            
                            @if(!empty($datosBloques['HABITS']))
                                @php $bloque = $datosBloques['HABITS']; @endphp
                                <table class="boleta-v2">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            <th colspan="{{ 1 + count($periodos) + 1 }}" style="background-color: #C6E0B4;">{{ $bloque['titulo'] }}</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;"></th> @foreach($periodos as $periodo) <th>{{ $periodo->nombre }}</th> @endforeach <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bloque['criterios'] as $criterio)
                                            <tr>
                                                <td class="criterio-pas">{{ $criterio['nombre'] }}</td>
                                                @foreach($periodos as $periodo) <td class="cal-pas">{{ is_numeric($criterio['calificaciones'][$periodo->periodo_id]) ? round($criterio['calificaciones'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                                <td class="cal-prom-pas">{{ is_numeric($criterio['promedio']) ? round($criterio['promedio'], 1)+0 : '' }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="promedio-bloque-pas">
                                            <td>AVERAGE</td>
                                            @foreach($periodos as $periodo) <td>{{ is_numeric($bloque['promedios_bloque'][$periodo->periodo_id]) ? round($bloque['promedios_bloque'][$periodo->periodo_id], 1)+0 : '' }}</td> @endforeach
                                            <td>{{ is_numeric($bloque['promedios_bloque']['promedio']) ? round($bloque['promedios_bloque']['promedio'], 1)+0 : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            {{-- PRIMARIA: TABLA DE ASISTENCIAS (MOVIDA AQUI DESDE LA IZQUIERDA Y ACTUALIZADA EXACTAMENTE IGUAL A PREESCOLAR) --}}
                            @if(!empty($datosAsistencias))
                                <table class="boleta-v2 asistencias-table" style="margin-top: 15px;">
                                    <thead>
                                        <tr class="header-row-titulo">
                                            {{-- Colspan ajustado: 1(label) + Periodos + 1(Total) --}}
                                            <th colspan="{{ 1 + count($periodos) + 1 }}">CONTROL DE ASISTENCIAS // ATTENDANCE CONTROL</th>
                                        </tr>
                                        <tr class="header-row-periodos">
                                            <th style="width: 35%;">MOMENTO ----></th>
                                            @foreach($periodos as $periodo) <th>ESP/ENG</th> @endforeach
                                            <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">ASISTENCIAS / ATTENDANCES</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_asistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_asistencias'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_asistencias'] ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">RETARDOS / DELAYS</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_retardos'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_retardos'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_retardos'] ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td rowspan="1" class="label" style="font-weight: bold;">INASISTENCIAS / ABSENCES</td>
                                            @foreach($periodos as $periodo) <td>{{ $datosAsistencias['periodos'][$periodo->periodo_id]['ESP_inasistencias'] ?? 0 }} / {{ $datosAsistencias['periodos'][$periodo->periodo_id]['ENG_inasistencias'] ?? 0 }}</td> @endforeach
                                            <td>{{ $datosAsistencias['totales']['TOTAL_inasistencias'] ?? 0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                            
                            <table style="width: 60%; margin: 40px auto 10px auto; border-collapse: collapse; text-align: center; page-break-inside: avoid;">
                                <tr>
                                    <td style="border-bottom: 1px solid #000; height: 1px;"></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 7px; font-weight: bold; padding-top: 2px;">FIRMA DEL ALUMNO</td>
                                </tr>
                            </table>

                        @endif

                    </td>
                </tr>
            </table>
            
            {{-- FIRMAS ABAJO (IGUAL PARA AMBOS) --}}
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