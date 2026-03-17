<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['ADMIN', 'CAJA'])) {
    header("Location: ../dashboard.php"); exit();
}
require_once "../../config/Database.php";
require_once "../../classes/Socio.php";
$db = (new Database())->getConnection();

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM socios WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$s) { header("Location: registro_socios.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Socio - GYM MA</title>
    <style>
        /* ESTILO IDENTICO A GESTION_SOCIOS.PHP */
        body { background: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; color: #000; margin: 0; }
        * { color: #000; } 

        .header-negro { 
            background: #2d3436; 
            padding: 15px 25px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .header-negro h2 { color: #fff; margin: 0; }

        /* BOTON REGRESAR CON EFECTO */
        .btn-regresar {
            background: #e17055;
            color: white !important;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
            border: none;
            display: inline-block;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }
        .btn-regresar:hover {
            background: #ff7675;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .contenedor-caja { padding: 25px; }
        
        .stat-card { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            max-width: 900px; 
            margin: 0 auto;
        }

        .grid-editar { 
            display: grid; 
            grid-template-columns: 220px 1fr; 
            gap: 30px; 
        }

        .form-inputs { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }

        label { 
            display: block; 
            font-size: 0.8rem; 
            color: #000; 
            font-weight: 700; 
            margin-bottom: 5px; 
            text-transform: uppercase; 
        }

        input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #000; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-weight: 600; 
            font-size: 0.95rem;
        }

        .btn-actualizar { 
            background: #27ae60; 
            color: white !important; 
            border: none; 
            padding: 15px 30px; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1rem; 
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-actualizar:hover { 
            background: #219150; 
            transform: scale(1.02);
        }

        .info-ingreso {
            grid-column: span 2; 
            background: #dfe6e9; 
            padding: 12px; 
            border-radius: 6px; 
            font-size: 0.9rem; 
            border-left: 5px solid #2d3436;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <header class="header-negro">
        <h2>✏️ EDITAR EXPEDIENTE</h2>
        <a href="registro_socios.php" class="btn-regresar">REGRESAR A GESTIÓN</a>
    </header>

    <div class="contenedor-caja">
        <div class="stat-card">
            <form action="../../controllers/SocioController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                
                <div class="grid-editar">
                    <div style="text-align: center;">
                        <img src="../../public/uploads/<?php echo $s['foto_ruta'] ?: 'default.png'; ?>" 
                             style="width:180px; height:180px; border-radius:12px; object-fit:cover; border:3px solid #000; display:block; margin:0 auto;"
                             onerror="this.src='../../public/uploads/default.png'">
                        <br>
                        <label>CAMBIAR FOTO</label>
                        <input type="file" name="foto" style="border:none; padding:0; font-size: 0.8rem;">
                    </div>

                    <div class="form-inputs">
                        <div>
                            <label>NOMBRE</label>
                            <input type="text" name="nombre" value="<?php echo $s['nombre']; ?>" required>
                        </div>
                        <div>
                            <label>APELLIDO</label>
                            <input type="text" name="apellido" value="<?php echo $s['apellido']; ?>" required>
                        </div>
                        <div>
                            <label>CÉDULA</label>
                            <input type="text" name="cedula" value="<?php echo $s['cedula']; ?>">
                        </div>
                        <div>
                            <label>EDAD MANUAL</label>
                            <input type="number" name="edad" value="<?php echo $s['edad']; ?>">
                        </div>
                        <div>
                            <label>WHATSAPP / TEL</label>
                            <input type="text" name="telefono" value="<?php echo $s['telefono']; ?>">
                        </div>
                        <div>
                            <label>SALUD / ENFERMEDAD</label>
                            <input type="text" name="enfermedad" value="<?php echo $s['enfermedad']; ?>">
                        </div>
                        <div style="grid-column: span 2;">
                            <label>EN CASO DE EMERGENCIA LLAMAR A:</label>
                            <input type="text" name="emergencia_contacto" value="<?php echo $s['emergencia_contacto']; ?>">
                        </div>
                        
                        <div class="info-ingreso">
                            <strong>📅 FECHA DE REGISTRO:</strong> 
                            <?php 
                                $fi = $s['fecha_ingreso'];
                                echo ($fi && strtotime($fi) > 0) ? date('d/m/Y h:i A', strtotime($fi)) : 'No registrada'; 
                            ?>
                        </div>
                    </div>
                </div>

                <div style="text-align:right; margin-top:30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <button type="submit" name="btn_editar" class="btn-actualizar">💾 ACTUALIZAR DATOS AHORA</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>