<?php
session_start();
if (!isset($_SESSION['rol'])) { header("Location: ../dashboard.php"); exit(); }

require_once "../../config/Database.php";
require_once "../../classes/Socio.php";

$db = (new Database())->getConnection();
$socioObj = new Socio($db);
$socios = $socioObj->listarSocios();

function calcularEdadCedula($cedula) {
    if (empty($cedula) || strlen($cedula) < 14) return null;
    $fecha = substr(str_replace('-', '', $cedula), 3, 6);
    $d = substr($fecha, 0, 2); $m = substr($fecha, 2, 2); $a = substr($fecha, 4, 2);
    $anio = ($a > 25) ? "19".$a : "20".$a;
    try {
        return (new DateTime())->diff(new DateTime("$anio-$m-$d"))->y;
    } catch (Exception $e) { return null; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SOCIOS - GYM MA</title>
    <style>
        /* RESET Y COLORES BASE */
        body { background: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; color: #000; margin: 0; }
        
        .header-negro { background: #2d3436; color: white; padding: 15px 25px; display: flex; align-items: center; justify-content: space-between; }
        .contenedor-caja { padding: 20px; }
        
        /* TARJETAS */
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; color: #000; }

        /* FORMULARIO */
        .grid-registro { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        label { display: block; font-size: 0.8rem; color: #000; font-weight: 700; margin-bottom: 5px; text-transform: uppercase; }
        input { width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box; color: #000; font-weight: 600; }

        /* TABLA FORZADA A NEGRO */
        .tabla-gym { width: 100%; border-collapse: collapse; min-width: 1000px; background: white; }
        .tabla-gym th { background: #e0e0e0; color: #000; padding: 12px; text-align: left; font-size: 0.85rem; border-bottom: 2px solid #000; }
        .tabla-gym td { padding: 15px; border-bottom: 1px solid #ccc; color: #000 !important; font-weight: 600; vertical-align: middle; }
        
        /* FUERZA EL NEGRO EN CUALQUIER TEXTO DENTRO DE LA TABLA */
        .tabla-gym span, .tabla-gym small, .tabla-gym b, .tabla-gym td { color: #000 !important; }

        /* BOTONES DE ACCIÓN */
        .acciones-flex { display: flex; gap: 15px; justify-content: flex-end; align-items: center; }
        .ico-btn { font-size: 1.5rem; text-decoration: none; display: inline-block; transition: 0.2s; }
        .ico-btn:hover { transform: scale(1.2); }

        /* ESTADOS */
        .badge { padding: 6px 12px; border-radius: 5px; color: white !important; font-size: 0.75rem; font-weight: 800; display: inline-block; }
        .bg-vencido { background: #d63031; } 
        .bg-activo { background: #27ae60; }  
        .bg-noplan { background: #636e72; }  

        .btn-guardar { background: #27ae60; color: white; border: none; padding: 15px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 15px; font-size: 1rem; }
    </style>
</head>
<body>

    <header class="header-negro">
        <h2 style="margin:0;">👤 CONTROL DE SOCIOS - GYM MA</h2>
        <a href="../dashboard.php" style="color:white; text-decoration:none; font-weight:bold; background:#e17055; padding:8px 15px; border-radius:5px;">SALIR</a>
    </header>

    <div class="contenedor-caja">
        
        <div class="stat-card">
            <h3 style="margin-top:0; border-bottom: 2px solid #000; padding-bottom:5px;">REGISTRO DE SOCIO</h3>
            <form action="../../controllers/SocioController.php" method="POST" enctype="multipart/form-data">
                <div class="grid-registro">
                    <div><label>Nombres</label><input type="text" name="nombre" required></div>
                    <div><label>Apellidos</label><input type="text" name="apellido" required></div>
                    <div><label>Cédula</label><input type="text" name="cedula"></div>
                    <div><label>Edad</label><input type="number" name="edad"></div>
                    <div><label>WhatsApp</label><input type="text" name="telefono"></div>
                    <div><label>Salud</label><input type="text" name="enfermedad"></div>
                    <div><label>Emergencia</label><input type="text" name="emergencia_contacto"></div>
                    <div><label>Foto</label><input type="file" name="foto"></div>
                </div>
                <button type="submit" name="btn_guardar" class="btn-guardar">GUARDAR SOCIO NUEVO</button>
            </form>
        </div>

        <div class="stat-card" style="padding:0; overflow-x: auto;">
            <table class="tabla-gym" id="tabla_socios">
                <thead>
                    <tr>
                        <th style="width:60px;">FOTO</th>
                        <th>NOMBRE Y APELLIDO</th>
                        <th>ID / EDAD</th>
                        <th>SALUD Y EMERGENCIAS</th>
                        <th style="text-align:center;">ESTADO</th>
                        <th style="text-align:right;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = $socios->fetch(PDO::FETCH_ASSOC)): 
                        $e_calc = calcularEdadCedula($s['cedula'] ?? '');
                        $edad_final = ($s['edad'] > 0) ? $s['edad'] : ($e_calc ?: '--');
                        $dias = $s['dias_restantes'] ?? -1;
                        $clase = ($dias < 0) ? "bg-vencido" : "bg-activo";
                        $label = ($dias < 0) ? "VENCIDO" : "ACTIVO";
                        if(empty($s['fecha_vencimiento'])) { $label = "SIN PLAN"; $clase = "bg-noplan"; }
                    ?>
                    <tr>
                        <td>
                            <img src="../../public/uploads/<?php echo $s['foto_ruta'] ?: 'default.png'; ?>" 
                                 style="width:50px; height:50px; border-radius:50%; object-fit:cover; border: 1px solid #000;">
                        </td>
                        <td>
                            <b style="font-size:1.1rem;"><?php echo strtoupper($s['nombre']." ".$s['apellido']); ?></b><br>
                            <small>📅 Ingreso: <?php echo date('d/m/Y', strtotime($s['fecha_ingreso'])); ?></small>
                        </td>
                        <td>
                            <b>ID:</b> <?php echo $s['cedula'] ?: 'N/A'; ?><br>
                            <b>EDAD:</b> <?php echo $edad_final; ?> Años
                        </td>
                        <td>
                            <span>🩺 <?php echo $s['enfermedad'] ?: 'Ninguna'; ?></span><br>
                            <span>📞 <?php echo $s['telefono'] ?: 'S/N'; ?></span><br>
                            <b style="color:#d63031 !important;">🚨 Emer: <?php echo $s['emergencia_contacto'] ?: 'S/N'; ?></b>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge <?php echo $clase; ?>"><?php echo $label; ?></span>
                        </td>
                        <td>
                            <div class="acciones-flex">
                                <a href="https://wa.me/505<?php echo preg_replace('/[^0-9]/','',$s['telefono']); ?>" target="_blank" class="ico-btn" title="WhatsApp">💬</a>
                                <a href="../caja/punto_venta.php?id_socio=<?php echo $s['id']; ?>" class="ico-btn" title="Cobrar">💰</a>
                                <a href="editar_socio.php?id=<?php echo $s['id']; ?>" class="ico-btn" title="Editar">✏️</a>
                                <a href="../../controllers/SocioController.php?delete=<?php echo $s['id']; ?>" 
                                   onclick="return confirm('¿Eliminar socio?')" class="ico-btn" title="Borrar">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>