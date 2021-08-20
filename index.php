<!DOCTYPE HTML>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="css/style.css">
    <title>Cobro Digital</title>
</head>

<body>
    <?php
    $mediosPago = array(
        "D" => "Débito Automático",
        "P" => "Pago Automático",
        "C" => "Sistema Nac. de Pagos"
    );
    $mediosPagoEntes = array(
        "00" => "Efectivo",
        "90" => "Tarjeta de Débito",
        "99" => "Tarjeta de Crédito"
    );
    ?>
    <div id="container-tabla">
        <div class="title">Cobranzas con Códigos de Barras</div>
        <table class="tabla">
            <?php
            $resource = "resources/888ENTES5723_308.TXT";
            leerArchivo($resource, $mediosPagoEntes);
            ?>
        </table>
        <div class="footer-tabla">
            Cantidad de registros: <?php echo $count; ?> <br />
            Importe total: $<?php echo $importeTotal; ?> <br /><br />
            <strong>Promedio de monto por medio de pago</strong> <br />
            <?php echo "Efectivo - $" . number_format($promEfec, 2); ?> <br />
            <?php echo "Tarjeta de Débito - $" . number_format($promDeb, 2); ?> <br />
            <?php echo "Tarjeta de Crédito - $" . number_format($promCred, 2); ?> 
        </div>
    </div>
    <div id="container-tabla">
        <div class="title">Reversión solicitada en el Banco de la cuenta</div>
        <table class="tabla">
            <?php
            $resource = "resources/REND.REV-REVC8496.REV-20191125.TXT";
            leerArchivo($resource, $mediosPago);
            ?>
        </table>
        <div class="footer-tabla">
            Cantidad de registros: <?php echo $count; ?> <br />
            Importe total: $<?php echo $importeTotal; ?> <br />
            Promedio de monto por medio de pago: <?php echo $valPago . " - $" . number_format($importeTotal / $count, 2); ?>
        </div>
    </div>
    <div id="container-tabla">
        <div class="title">Rendición Cobranza</div>
        <table class="tabla">
            <?php
            $resource = "resources/REND.COB-COBC8496.COB-20191125.TXT";
            leerArchivo($resource, $mediosPago);
            ?>
        </table>
        <div class="footer-tabla">
            Cantidad de registros: <?php echo $count; ?> <br />
            Importe total: $<?php echo $importeTotal; ?> <br />
            Promedio de monto por medio de pago: <?php echo $valPago . " - $" . number_format($importeTotal / $count, 2); ?>
        </div>
    </div>
</body>

<?php

function leerArchivo($resource, $mediosPago)
{
    global $count;
    $count = 0;
    global $importeTotal;
    $importeTotal = 0;
    global $valPago;

    $archivo = fopen($resource, "r")
        or die("Archivo no existente!");

    if (str_contains($resource, 'REND.COB') || str_contains($resource, 'REND.REV')) {

        while ($linea = fgets($archivo)) {
            echo "<tr>";

            $tipoReg = substr($linea, 0, 4);
            if ($tipoReg == "0000") {
                //Datos del header
                $pago = substr($linea, 8, 1);
                $valPago = $mediosPago[$pago];
                if ($valPago == "") $valPago = "Medio de pago inexistente";
                //Encabezado tabla
                echo '<th>Nro. Transacción</th>';
                echo '<th>Monto</th>';
                echo '<th>Identificador</th>';
                echo '<th>Fecha de Pago</th>';
                echo '<th>Medio de Pago</th>';
            } elseif ($tipoReg == "0371" || $tipoReg == "0360" || $tipoReg == "0370") {
                $count += 1;
                $clientId = substr($linea, 4, 22);
                $cbu = substr($linea, 26, 26);
                $referencia = substr($linea, 52, 15);
                $fecha = substr($linea, 67, 8);
                $importe = floatval(ltrim(substr($linea, 75, 14), '0') / 100);
                $importeTotal = $importeTotal + $importe;
                echo '<td>' . $referencia . '</td>';
                echo '<td>$' . $importe . '</td>';
                echo '<td>' . $clientId . '</td>';
                echo '<td>' . $fecha . '</td>';
                echo '<td>' . $valPago . '</td>';
            }

            echo "</tr>";
        }
    } elseif (str_contains($resource, '888ENTES')) {
        $importeEfec = 0;
        $cantEfec = 0;
        $importeDeb = 0;
        $cantDeb = 0;
        $importeCred = 0;
        $cantCred = 0;
        global $promEfec;
        $promEfec = 0;
        global $promDeb;
        $promDeb = 0;
        global $promCred;
        $promCred = 0;
        echo "<tr>";
        echo '<th>Nro. Transacción</th>';
        echo '<th>Monto</th>';
        echo '<th>Identificador</th>';
        echo '<th>Fecha de Pago</th>';
        echo '<th>Medio de Pago</th>';
        echo "</tr>";
        while ($linea = fgets($archivo)) {
            echo "<tr>";

            $tipoReg = substr($linea, 0, 5);
            if ($tipoReg == "DATOS") {
                $count += 1;
                $transaccion = substr($linea, 40, 8);
                $importe = floatval(ltrim(substr($linea, 77, 11), '0') / 100);
                $identificacion = substr($linea, 58, 19);
                $fecha = substr($linea, 224, 6);
                $pago = substr($linea, 247, 2);
                if ($pago == "00") {
                    $importeEfec += $importe;
                    $cantEfec += 1;
                } elseif ($pago == "90") {
                    $importeDeb += $importe;
                    $cantDeb += 1;
                } elseif ($pago == "99") {
                    $importeCred += $importe;
                    $cantCred += 1;
                }
                $valPago = $mediosPago[$pago];
                if ($valPago == "") $valPago = "Medio de pago inexistente";
                $importeTotal = $importeTotal + $importe;
                echo '<td>' . $transaccion . '</td>';
                echo '<td>$' . $importe . '</td>';
                echo '<td>' . $identificacion . '</td>';
                echo '<td>' . $fecha . '</td>';
                echo '<td>' . $valPago . '</td>';
            }

            echo "</tr>";
        }
        if ($cantEfec == 0) $cantEfec = 1;
        if ($cantDeb == 0) $cantDeb = 1;
        if ($cantCred == 0) $cantCred = 1;
        $promEfec = $importeEfec / $cantEfec;
        $promDeb = $importeDeb / $cantDeb;
        $promCred = $importeCred / $cantCred;
    }

    fclose($archivo);
}

?>