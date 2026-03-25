<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['ADMIN', 'CAJA'])) {
    header("Location: ../dashboard.php"); exit();
}
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Socio.php";
$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

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
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .contenedor-caja { padding: 25px; }
        
        .stat-card { 
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

        .btn-actualizar { 
            background: var(--primary); 
            color: white !important; 
            border: none; 
            padding: 15px 30px; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1rem; 
            transition: 0.3s;
        }
        .btn-actualizar:hover { 
            filter: brightness(1.1);
            transform: scale(1.02);
        }

        .info-ingreso {
            grid-column: span 2; 
            padding: 12px; 
            border-radius: 6px; 
            font-size: 0.9rem; 
            border-left: 5px solid var(--secondary);
            margin-top: 10px;
        }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">

    <header>
        <div class="logo"><h2><i class="fas fa-user-edit"></i> EDITAR EXPEDIENTE</h2></div>
        <a href="registro_socios.php" class="btn-volver gris">← Volver</a>
    </header>

    <div class="dashboard-wrapper">
        <div class="stat-card">
            <form action="../../controllers/SocioController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                
                <div class="grid-editar">
                    <div style="text-align: center;">
                        <img src="../../public/uploads/<?php echo $s['foto_ruta'] ?: 'default.png'; ?>" 
                             style="width:180px; height:180px; border-radius:12px; object-fit:cover; border:3px solid var(--border-color); display:block; margin:0 auto;"
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

                <div style="text-align:right; margin-top:30px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <button type="submit" name="btn_editar" class="btn-actualizar">💾 ACTUALIZAR DATOS</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>