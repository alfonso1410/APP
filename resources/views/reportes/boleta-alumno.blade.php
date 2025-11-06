<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 25px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
        }
        .header h3, .header h4 {
            margin: 2px 0;
            font-weight: bold;
        }
        .header .logo {
            position: absolute;
            top: 0;
            left: 0;
            width: 80px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px 8px;
        }
        .info-table .label {
            background-color: #eee;
            font-weight: bold;
            width: 15%;
        }
        .boleta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            text-align: center;
        }
        .boleta-table th,
        .boleta-table td {
            border: 1px solid #000;
            padding: 3px;
            height: 20px;
        }
        .boleta-table thead th {
            background-color: #E0E0E0;
            font-weight: bold;
            padding: 5px;
        }
        .boleta-table .th-materia {
            background-color: #F5F5F5;
            text-align: left;
            font-weight: bold;
            padding-left: 5px;
        }
        .boleta-table .th-campo {
            background-color: #B0C4DE;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            padding-left: 5px;
        }
        .boleta-table .th-promedio-final {
            background-color: #E0E0E0;
            text-align: left;
            font-weight: bold;
            padding-left: 5px;
        }
        .boleta-table .cal-pas {
            font-weight: bold;
        }
        .boleta-table .cal-sep {
            background-color: #F5F5F5;
            font-weight: bold;
        }
        .boleta-table .cal-promedio-final {
            background-color: #E0E0E0;
            font-weight: bold;
        }
        .boleta-table .col-promedio-materia {
            background-color: #F0F8FF;
        }
        .boleta-table .col-promedio-campo {
            background-color: #E6E6FA;
            font-weight: bold;
        }
        .empty-cell {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('Assets/logo-princeton.png') }}" alt="Logo" class="logo">
            <h3>"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</h3>
            <h4>SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</h4>
            <h4>BOLETA DE EVALUACIÓN</h4>
            <h4>CICLO ESCOLAR: {{ $ciclo->nombre }}</h4>
        </div>

        <table class="info-table">
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

        <table class="boleta-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25%;">CAMPOS FORMATIVOS / MATERIAS</th>
                    @foreach($periodos as $periodo)
                        <th colspan="2">{{ $periodo->nombre }}</th>
                    @endforeach
                    <th colspan="2">PROMEDIO</th>
                </tr>
                <tr>
                    @foreach($periodos as $periodo)
                        <th>PAS</th>
                        <th>SEP</th>
                    @endforeach
                    <th>PAS</th>
                    <th>SEP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataCampos as $campo)
                    <tr class="th-campo">
                        <td>{{ $campo['nombre'] }}</td>
                        @foreach($periodos as $periodo)
                            <td class="empty-cell"></td>
                            <td class="cal-sep">
                                @if($esPreescolar)
                                    {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($campo['calificaciones_sep'][$periodo->periodo_id]) }}
                                @else
                                    {{ $campo['calificaciones_sep'][$periodo->periodo_id] }}
                                @endif
                            </td>
                        @endforeach
                        <td class="col-promedio-campo">
                            @if($esPreescolar)
                                {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($campo['promedio_final_pas']) }}
                            @else
                                {{ $campo['promedio_final_pas'] }}
                            @endif
                        </td>
                        <td class="col-promedio-campo cal-sep">
                            @if($esPreescolar)
                                {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($campo['promedio_final_sep']) }}
                            @else
                                {{ $campo['promedio_final_sep'] }}
                            @endif
                        </td>
                    </tr>

                    @foreach($campo['materias'] as $materia)
                        <tr>
                            <td class="th-materia">{{ $materia['nombre'] }}</td>
                            @foreach($periodos as $periodo)
                                <td class="cal-pas">
                                    @if($esPreescolar)
                                        {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($materia['calificaciones_pas'][$periodo->periodo_id]) }}
                                    @else
                                        {{ $materia['calificaciones_pas'][$periodo->periodo_id] }}
                                    @endif
                                </td>
                                <td class="empty-cell"></td>
                            @endforeach
                            <td class="col-promedio-materia cal-pas">
                                @if($esPreescolar)
                                    {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($materia['promedio_pas']) }}
                                @else
                                    {{ $materia['promedio_pas'] }}
                                @endif
                            </td>
                            <td class="empty-cell"></td>
                        </tr>
                    @endforeach
                @endforeach

                <tr class="th-promedio-final">
                    <td>PROMEDIO</td>
                    @foreach($periodos as $periodo)
                        <td class="empty-cell"></td>
                        <td class="cal-promedio-final cal-sep">
                            @if($esPreescolar)
                                {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($promediosFinales[$periodo->periodo_id]) }}
                            @else
                                {{ $promediosFinales[$periodo->periodo_id] }}
                            @endif
                        </td>
                    @endforeach
                    <td class="empty-cell"></td>
                    <td class="cal-promedio-final cal-sep">
                        @if($esPreescolar)
                            {{ \App\Helpers\CalificacionHelper::convertirANivelPreescolar($promediosFinales['promedio_final_sep']) }}
                        @else
                            {{ $promediosFinales['promedio_final_sep'] }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
