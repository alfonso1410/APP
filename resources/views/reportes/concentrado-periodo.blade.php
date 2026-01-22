<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Concentrado de Calificaciones</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        @page {
            margin-top: 10px;
            margin-bottom: 5px;
            margin-left: 5px;
            margin-right: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px;
        }
        .logo-izquierda {
            width: 170px;
        }
        .logo-derecha {
            width: 60px;
        }
        .titulo-centro {
            text-align: center;
            font-weight: bold;
        }
        .titulo-centro .principal {
            font-size: 11px;
        }
        .titulo-centro .subtitulo {
            font-size: 9px;
        }
        .titulo-centro .concentrado {
            font-size: 9px;
            background-color: #E0E0E0;
            padding: 1px;
        }

       .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
}
   .info-table tr.info-table-row td {
    border: 1px solid #000;
    padding: 4px;
    font-weight: bold;
    font-size: 9px;
    text-align: left;
    vertical-align: middle;
    background-color: #FFFFFF;
}
      .info-table tr.info-table-row td.label {
    background-color: #E0E0E0;
    font-weight: bold;
    width: 15%;
    font-size: 9px;
}

        .tabla-con-barra {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        
        .tabla-con-barra > tbody > tr > td {
            padding: 0;
            margin: 0;
            vertical-align: top;
        }

       .main-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8px;
    table-layout: fixed;
}
     .main-table th, .main-table td {
    border: 1px solid #000;
    padding: 3px;
    height: 24px;
    text-align: center;
    vertical-align: middle;
}
        
        .main-table thead {
            display: table-header-group;
        }
        
.main-table thead tr.column-headers th { 
    font-weight: bold;
    font-size: 8px;
    background-color: #F0F0F0;
    padding: 4px;
    word-wrap: break-word;
    line-height: 1.2;
}

.main-table .cell-num {
    width: 3%;
    font-weight: bold;
}
      .main-table .cell-alumno {
    width: 25%;
    text-align: left;
    padding-left: 5px;
    font-weight: bold;
    font-size: 8px;
}

       .main-table tbody tr {
    height: 25px;
}
     .main-table tbody td {
    font-size: 9px;
}

        /* NUEVO: Estilo para la fila de promedios por criterio */
        .main-table .fila-promedios-criterio {
            background-color: #FFFFFF;
            font-weight: bold;
        }
        .main-table .fila-promedios-criterio td {
            font-size: 9px;
        }

        .barra-lateral {
            width: 3%;
            background-color: #004A99;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
        }
        .barra-lateral .text-vertical {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding: 3px 0;
        }

        .resumen-promedio {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 5px;
            border-collapse: collapse;
            font-size: 8px;
        }
        .resumen-promedio td {
            padding: 1px;
            text-align: center;
        }
        .resumen-promedio .label {
            font-weight: bold;
            font-size: 8px;
            padding-right: 5px;
        }
        .resumen-promedio .value-box {
            border: 1px solid #000;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 9px;
            background-color: #FFFFFF;
        }
        .resumen-promedio .promedio-box {
            border: 1px solid #000;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 9px;
            background-color: #90EE90;
        }

        .footer-firmas {
            width: 100%;
            margin-top: 30px;
            padding: 0;
            font-size: 7px;
        }
       .footer-firmas table {
    width: 80%;
    border-collapse: collapse;
    margin: 0 auto;
}
        .footer-firmas td {
            width: 50%;
            text-align: center;
            padding: 0 90px;
            vertical-align: top;
        }
        .firma-container hr {
            border: none;
            border-top: 1px solid #000;
            margin: 0 0 2px 0;
            width: 100%;
            
        }
        .firma-texto {
            font-weight: bold;
            font-size: 7px;
            text-align: center;
        }

        .contenido-no-separar {
            page-break-inside: avoid;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-izquierda" style="text-align: left; width: 170px;">
                @if(file_exists(public_path('Assets/logo-princeton.png')))
                <img src="{{ public_path('Assets/logo-princeton.png') }}" alt="Logo" style="width: 170px;">
                @endif
            </td>
            <td class="titulo-centro">
                <div class="principal">"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</div>
                <div class="subtitulo">SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</div>
                <div class="concentrado">CONCENTRADO DE CALIFICACIONES  {{ $periodo->cicloEscolar->nombre ?? '2024-2025' }}</div>
            </td>
            <td class="logo-derecha" style="text-align: right; width: 60px; vertical-align: bottom;">
                @if(file_exists(public_path('Assets/logo-azul.png')))
                <img src="{{ public_path('Assets/logo-azul.png') }}" alt="Logo Azul" style="width: 60px;">
                @endif
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr class="info-table-row">
            <td class="label" style="width: 10%;">DOCENTE:</td>
            <td style="width: 40%;">{{ $nombreMaestro ?? 'Sin asignar' }}</td>
            <td class="label" style="width: 10%;">GRADO:</td>
            <td style="width: 40%;">
                {{ $grupo->grado->nombre ?? 'N/A' }}{{ $grupo->nombre_grupo ? ' - '.$grupo->nombre_grupo : '' }}
            </td>
        </tr>
        <tr class="info-table-row">
            <td class="label">MATERIA:</td>
            <td colspan="3">{{ $materia->nombre }}</td>
        </tr>
    </table>

    @php
        $alumnosChunked = $alumnos->chunk(25);
    @endphp

    @foreach($alumnosChunked as $index => $chunk)
        <div class="contenido-no-separar">

            <table class="tabla-con-barra">
                <tbody>
                    <tr>
                        <td style="width: 97%;">
                            <table class="main-table">
                                <thead>
                                    <tr class="column-headers">
                                        <th class="cell-num">#</th>
                                        <th class="cell-alumno">ALUMNO</th>
                                        @foreach($criterios as $criterio)
                                            <th>{{ mb_strtoupper($criterio['nombre']) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chunk as $alumno)
                                    <tr>
                                        <td class="cell-num">{{ $loop->iteration + ($index * 25) }}</td>
                                        <td class="cell-alumno">{{ mb_strtoupper($alumno->apellido_paterno) }} {{ mb_strtoupper($alumno->apellido_materno) }} {{ mb_strtoupper($alumno->nombres) }}</td>
                                        
                                        @foreach($criterios as $criterio)
                                            <td>
                                                @php
                                                    $calif = $calificaciones->get($alumno->alumno_id)
                                                                            ?->get($criterio['id'])
                                                                            ?->calificacion_obtenida;
                                                @endphp
                                                
                                                @if(is_numeric($calif))
                                                    {{ $criterio['es_promedio'] ? number_format($calif, 1) : number_format($calif, 1) }}
                                                @else
                                                    NP
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach

                                    <!-- NUEVA FILA: Promedios por Criterio (solo en la última página) -->
                                    @if($loop->last && $index == count($alumnosChunked) - 1)
                                    <tr class="fila-promedios-criterio">
                                        <td colspan="2" style="text-align: right; padding-right: 10px;"><strong>ALUMNOS EVALUADOS: </strong>{{ count($alumnos) }}</td>
                                        @foreach($criterios as $criterio)
                                            <td>
                                                @php
                                                    $promedioCriterio = $promediosPorCriterio[$criterio['id']] ?? null;
                                                @endphp
                                                
                                                @if(is_numeric($promedioCriterio))
                                                    {{ number_format($promedioCriterio, 1) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endif
                                </tbody>
                                
                            </table>
                        </td>
                        
                        <td class="barra-lateral">
                            <div class="text-vertical">{{ mb_strtoupper($periodo->nombre ?? '1er. TRIMESTRE') }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            @if($loop->last)
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
                                    <div class="firma-texto">Vo.Bo. COORDINACION ACADEMICA</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        </div>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif

    @endforeach

</body>
</html>