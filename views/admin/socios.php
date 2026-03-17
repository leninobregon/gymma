<?php
session_start();
require_once "../../config/Database.php";
$db = (new Database())->getConnection();

// Consulta que calcula los días restantes entre hoy y el vencimiento
$sql = "SELECT *, DATEDIFF(fecha_vencimiento, CURDATE()) as dias_restantes FROM socios ORDER BY nombre ASC";
$socios = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Socios - GYM MA</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .badge { padding: 6px 12px; border-radius: 50px; font-size: 11px; font-weight: bold; color: white; text-transform: uppercase; min-width: 130px; display: inline-block; }
        .bg-activo { background: #27ae60; }   /* Verde */
        .bg-vencido { background: #e74c3c; }  /* Rojo */
        .bg-alerta { background: #f39c12; }   /* Naranja */
        .bg-gris { background: #95a5a6; }     /* Gris */
        .search-box { padding: 10px; width: 100%; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>👥 Listado de Socios</h2></div>
        <a href="../dashboard.php" class="btn-accion" style="background:#7f8c8d;">← Volver</a>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            
            <input type="text" id="busquedaSocio" class="search-box" placeholder="🔍 Buscar por nombre o identificación...">

            <table style="width:100%; border-collapse: collapse;" id="tablaSocios">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding:15px; text-align:left;">Socio</th>
                        <th style="text-align:left;">DNI/Cédula</th>
                        <th style="text-align:center;">Vigencia</th>
                        <th style="text-align:center;">Fecha Vencimiento</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($socios as $s): 
                        $dias = $s['dias_restantes'];
                        $clase = "bg-gris";
                        $vigencia = "SIN PAGO";
                        $fecha_vence = "---";

                        if (!empty($s['fecha_vencimiento'])) {
                            $fecha_vence = date('d/m/Y', strtotime($s['fecha_vencimiento']));
                            
                            if ($dias < 0) { 
                                $clase = "bg-vencido"; 
                                $vigencia = "VENCIDO (" . abs($dias) . " días)";
                            } elseif ($dias <= 3) { 
                                $clase = "bg-alerta"; 
                                $vigencia = "VENCE EN $dias DÍAS";
                            } else { 
                                $clase = "bg-activo"; 
                                $vigencia = "VIGENTE ($dias días)";
                            }
                        }
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:12px;"><strong><?php echo strtoupper($s['nombre'] . " " . $s['apellido']); ?></strong></td>
                        <td style="color: #7f8c8d;"><?php echo $s['dni'] ?? 'N/A'; ?></td>
                        <td style="text-align:center;">
                            <span class="badge <?php echo $clase; ?>">
                                <?php echo $vigencia; ?>
                            </span>
                        </td>
                        <td style="text-align:center; font-weight: bold;">
                            <?php echo $fecha_vence; ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="../caja/punto_venta.php?id_socio=<?php echo $s['id']; ?>" title="Cobrar" style="text-decoration:none; font-size:22px;">💰</a>
                            <a href="editar_socio.php?id=<?php echo $s['id']; ?>" title="Editar" style="text-decoration:none; font-size:20px; margin-left:12px;">✏️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('busquedaSocio').addEventListener('keyup', function() {
            let filtro = this.value.toUpperCase();
            let filas = document.getElementById('tablaSocios').getElementsByTagName('tr');
            for (let i = 1; i < filas.length; i++) {
                let texto = filas[i].textContent || filas[i].innerText;
                filas[i].style.display = (texto.toUpperCase().indexOf(filtro) > -1) ? "" : "none";
            }
        });
    </script>
</body>
</html>