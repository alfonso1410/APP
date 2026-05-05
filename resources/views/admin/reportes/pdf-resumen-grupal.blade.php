<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Promedios Generales SEP</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.5px; /* Ligera reducción para ajuste óptimo */
            margin: 0;
            padding: 0;
            color: #333;
        }

        @page {
            margin: 10px 10px 10px 10px;
        }

        /* Cabecera con Logos */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px;
        }
        .logo-izquierda { width: 170px; }
        .logo-derecha { width: 60px; }
        
        .titulo-centro {
            text-align: center;
            font-weight: bold;
        }
        .titulo-centro .principal { font-size: 10px; }
        .titulo-centro .subtitulo { font-size: 8px; }
        .titulo-centro .concentrado {
            font-size: 9px;
            background-color: #E0E0E0;
            padding: 2px;
            margin-top: 3px;
            display: block;
            text-transform: uppercase;
        }

        /* Tabla de Información del Grupo */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 8.5px;
        }
        .info-table td.label {
            background-color: #E0E0E0;
            font-weight: bold;
            width: 12%;
        }

        /* Tabla Principal de Calificaciones */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 4px 2px; /* Reducción de padding */
            height: 24px;      /* Altura reducida un 15% para mayor compacidad */
            text-align: center;
            vertical-align: middle;
        }
        .main-table thead th {
            background-color: #F0F0F0;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
        }
        .cell-num { width: 25px; }
        .cell-alumno {
            width: 200px;
            text-align: left !important;
            padding-left: 6px !important;
            font-weight: bold;
        }
        .promedio-final {
            font-weight: bold;
            background-color: #F9F9F9;
        }
    </style>
</head>
<body>

    <!-- Cabecera con Logos e Identidad -->
    <table class="header-table">
        <tr>
            <td class="logo-izquierda">
                @if(file_exists(public_path('Assets/logo-princeton.png')))
                    <img src="{{ public_path('Assets/logo-princeton.png') }}" style="width: 160px;">
                @endif
            </td>
            <td class="titulo-centro">
                <div class="principal">"FORMACIÓN INTEGRAL PARA EL DESARROLLO DE LÍDERES"</div>
                <div class="subtitulo">SISTEMA BILINGÜE PRIMARIA CLAVE: 28PPR0307Y</div>
                <div class="concentrado">PROMEDIOS GENERALES SEP - {{ $grupo->cicloEscolar->nombre }}</div>
            </td>
            <td class="logo-derecha" style="text-align: right;">
                @if(file_exists(public_path('Assets/logo-azul.png')))
                    <img src="{{ public_path('Assets/logo-azul.png') }}" style="width: 55px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- Información del Reporte -->
    <table class="info-table">
        <tr>
            <td class="label">GRADO:</td>
            <td>{{ $grupo->grado->nombre }} - {{ $grupo->nombre_grupo }}</td>
            <td class="label">NIVEL:</td>
            <td>{{ $grupo->grado->nivel->nombre }}</td>
        </tr>
    </table>

    <!-- Tabla de Calificaciones (Ordenada por promedio de mayor a menor) -->
    <table class="main-table">
        <thead>
            <tr>
                <th class="cell-num">#</th>
                <th class="cell-alumno">ALUMNO</th>
                @foreach($camposSep as $campo)
                    <th>{{ mb_strtoupper($campo) }}</th>
                @endforeach
                <th class="promedio-final">PROMEDIO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $index => $alumno)
            <tr>
                <td class="cell-num">{{ $index + 1 }}</td>
                <td class="cell-alumno">{{ mb_strtoupper($alumno['nombre']) }}</td>
                @foreach($camposSep as $campo)
                    <td>{{ $alumno['campos'][$campo]['valor'] }}</td>
                @endforeach
                <td class="promedio-final">{{ $alumno['promedio_final'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>