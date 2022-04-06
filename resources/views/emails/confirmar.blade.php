<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Confirmar sede</title>
</head>
<body>
    <p>Hola {{$nombre_voluntario}}, favor de confirmar tu asistencia:</p>
    <p>Fecha: {{$fecha}}</p>
    <p>Lugar: {{$lugar}}</p>
    <br>
    <a href="http://localhost/eventos_backend/public/voluntario/confirmar/{{$id_detalle_jornada}}">Confirma</a>
    <a href="http://localhost/eventos_backend/public/voluntario/rechazar/{{$id_detalle_jornada}}">Rechazar</a>
</body>
</html>
