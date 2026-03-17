<?php
session_start();
if (!isset($_SESSION['rol'])) { header("Location: ../dashboard.php"); exit(); }

require_once "../../config/Database.php";
require_once "../../classes/Socio.php";

$db = (new Database())->getConnection();
$socioObj = new Socio($db);
$socios = $socioObj->listarSocios();

// --- 1. NUEVA LÓGICA DE REPORTES (CONSULTAS DIRECTAS) ---
$totalActivos = $db->query("SELECT COUNT(*) FROM socios WHERE fecha_vencimiento >= CURDATE()")->fetchColumn();
$vencenManana = $db->query("SELECT COUNT(*) FROM socios WHERE fecha_vencimiento = DATE_ADD(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();

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
        body { background: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; color: #000; margin: 0; }
        * { color: #000; } 

        .header-negro { 
            background: #2d3436; padding: 15px 25px; display: flex; 
            align-items: center; justify-content: space-between; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .header-negro h2 { color: #fff; margin: 0; }

        .btn-regresar {
            background: #e17055; color: white !important; text-decoration: none;
            font-weight: bold; padding: 10px 20px; border-radius: 5px;
            transition: all 0.3s ease; border: none; display: inline-block;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }
        .btn-regresar:hover { background: #ff7675; transform: translateY(-2px); }

        .contenedor-caja { padding: 20px; }

        /* 2. ESTILO DE LAS TARJETAS DE REPORTE */
        .seccion-reporte { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card-mini { 
            background: white; padding: 15px; border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; 
            justify-content: space-between; align-items: center; border-left: 8px solid #2d3436;
        }
        .card-mini h3 { margin: 0; font-size: 0.85rem; text-transform: uppercase; color: #636e72 !important; }
        .card-mini .numero { font-size: 1.8rem; font-weight: bold; }

        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }

        .tabla-gym { width: 100%; border-collapse: collapse; min-width: 1000px; background: white; }
        .tabla-gym th { background: #dfe6e9; padding: 12px; text-align: left; border-bottom: 2px solid #000; }
        .tabla-gym td { padding: 15px; border-bottom: 1px solid #ccc; font-weight: 600; vertical-align: middle; }
        .tabla-gym td, .tabla-gym b, .tabla-gym span, .tabla-gym small { color: #000 !important; }

        .acciones-flex { display: flex; gap: 15px; justify-content: flex-end; }
        .ico-btn { font-size: 1.6rem; text-decoration: none; transition: 0.2s; display: inline-block; }
        .ico-btn:hover { transform: scale(1.3); }

        .badge { padding: 6px 12px; border-radius: 5px; color: white !important; font-size: 0.75rem; font-weight: 800; }
        .bg-vencido { background: #d63031; } 
        .bg-activo { background: #27ae60; }  
        .bg-noplan { background: #636e72; }  

        .btn-guardar { 
            background: #27ae60; color: white !important; border: none; padding: 15px; 
            border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; 
            margin-top: 15px; font-size: 1rem; transition: 0.3s;
        }
        .btn-guardar:hover { background: #219150; }

        .grid-registro { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #000; border-radius: 5px; box-sizing: border-box; font-weight: 600; }
    </style>
</head>
<body>

    <header class="header-negro">
        <h2>👤 CONTROL DE SOCIOS</h2>
        <a href="../dashboard.php" class="btn-regresar">REGRESAR A DASHBOARD</a>
    </header>

    <div class="contenedor-caja">

        <div class="seccion-reporte">
            <div class="card-mini" style="border-color: #27ae60;">
                <div><h3>Socios Activos</h3><div class="numero"><?php echo $totalActivos; ?></div></div>
                <span style="font-size: 2.2rem;">✅</span>
            </div>
            <div class="card-mini" style="border-color: #e67e22;">
                <div><h3>Vencen Mañana</h3><div class="numero"><?php echo $vencenManana; ?></div></div>
                <button onclick="filtrarManana()" style="background:#0984e3; color:white!important; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; font-weight:bold;">VER LISTA</button>
            </div>
        </div>
        
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

        <div class="stat-card" style="display:flex; gap:10px; align-items:center;">
            <input type="text" id="input_busqueda" placeholder="🔍 Buscar socio por nombre, ID o teléfono..." style="flex:1;">
            <button onclick="window.location.reload()" style="background:#636e72; color:white!important; border:none; padding:10px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">TODOS</button>
        </div>

        <div class="stat-card" style="padding:0; overflow-x: auto;">
            <table class="tabla-gym" id="tabla_socios">
                <thead>
                    <tr>
                        <th style="width:60px;">FOTO</th>
                        <th>NOMBRE Y APELLIDO</th>
                        <th>ID / EDAD</th>
                        <th>SALUD / CONTACTO / EMER.</th>
                        <th style="text-align:center;">ESTADO / VENCE</th>
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
                        
                        // Guardamos la fecha en un atributo para el filtro de JavaScript
                        $fechaVenceData = ($s['fecha_vencimiento']) ? date('d/m/Y', strtotime($s['fecha_vencimiento'])) : '';
                    ?>
                    <tr data-vence="<?php echo $fechaVenceData; ?>">
                        <td>
                            <img src="../../public/uploads/<?php echo $s['foto_ruta'] ?: 'default.png'; ?>" 
                                 style="width:55px; height:55px; border-radius:50%; object-fit:cover; border: 2px solid #000;">
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
                            <span class="badge <?php echo $clase; ?>"><?php echo $label; ?></span><br>
                            <small>Vence: <?php echo $fechaVenceData ?: 'S/P'; ?></small>
                        </td>
                        <td>
                            <div class="acciones-flex">
                                <a href="https://wa.me/505<?php echo preg_replace('/[^0-9]/','',$s['telefono']); ?>" target="_blank" class="ico-btn" title="WhatsApp">💬</a>
                                <a href="../caja/punto_venta.php?id_socio=<?php echo $s['id']; ?>" class="ico-btn" title="Cobrar">💰</a>
                                <a href="editar_socio.php?id=<?php echo $s['id']; ?>" class="ico-btn" title="Editar">✏️</a>
                                <a href="../../controllers/SocioController.php?delete=<?php echo $s['id']; ?>" 
                                   onclick="return confirm('¿Eliminar socio?')" class="ico-btn" style="color:#d63031;" title="Borrar">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Buscador por texto
        document.getElementById('input_busqueda').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('#tabla_socios tbody tr');
            filas.forEach(f => {
                f.style.display = f.innerText.toLowerCase().includes(filtro) ? '' : 'none';
            });
        });

        // Filtro mágico para los que vencen mañana
        function filtrarManana() {
            let hoy = new Date();
            hoy.setDate(hoy.getDate() + 1); // Sumamos 1 día
            let mananaStr = hoy.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            let filas = document.querySelectorAll('#tabla_socios tbody tr');
            filas.forEach(f => {
                let fechaFila = f.getAttribute('data-vence');
                f.style.display = (fechaFila === mananaStr) ? '' : 'none';
            });
        }
    </script>
</body>
</html>