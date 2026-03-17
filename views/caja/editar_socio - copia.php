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
    <title>Editar Socio - GYM MA DB</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
</head>
<body>
    <header>
        <div class="logo"><h2>✏️ Editar Expediente</h2></div>
        <a href="registro_socios.php" class="btn-accion" style="background:#7f8c8d;">← Cancelar</a>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 900px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <form action="../../controllers/SocioController.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px;">
                    <div style="text-align: center; min-width: 200px;">
                        <img src="../../public/uploads/<?php echo $s['foto_ruta']; ?>" 
                             width="160" height="160"
                             style="width:160px; height:160px; border-radius:12px; object-fit:cover; border:3px solid var(--primary); display:block; margin:0 auto;"
                             onerror="this.src='../../public/uploads/default.png'">
                        <br>
                        <input type="file" name="foto" style="width: 100%;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display:flex; flex-direction:column;"><label>NOMBRE</label><input type="text" name="nombre" value="<?php echo $s['nombre']; ?>" required></div>
                        <div style="display:flex; flex-direction:column;"><label>APELLIDO</label><input type="text" name="apellido" value="<?php echo $s['apellido']; ?>" required></div>
                        <div style="display:flex; flex-direction:column;"><label>CÉDULA</label><input type="text" name="cedula" value="<?php echo $s['cedula']; ?>"></div>
                        
                        <div style="display:flex; flex-direction:column;"><label>EDAD ACTUAL/MANUAL</label><input type="number" name="edad" value="<?php echo $s['edad']; ?>"></div>
                        
                        <div style="display:flex; flex-direction:column;"><label>TELÉFONO</label><input type="text" name="telefono" value="<?php echo $s['telefono']; ?>"></div>
                        <div style="display:flex; flex-direction:column;"><label>ENFERMEDAD</label><input type="text" name="enfermedad" value="<?php echo $s['enfermedad']; ?>"></div>
                        
                        <div style="display:flex; flex-direction:column; grid-column: span 2;"><label>EN CASO DE EMERGENCIA LLAMAR A:</label><input type="text" name="emergencia_contacto" value="<?php echo $s['emergencia_contacto']; ?>"></div>
                        
                        <div style="grid-column: span 2; background: #f9f9f9; padding: 10px; border-radius: 5px; font-size: 0.8rem; border-left: 4px solid var(--primary);">
                            <strong>FECHA DE INGRESO:</strong> 
                            <?php 
                                $fi = $s['fecha_ingreso'];
                                echo ($fi && strtotime($fi) > 0) ? date('d/m/Y h:i A', strtotime($fi)) : date('d/m/Y'); 
                            ?>
                        </div>
                    </div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" name="btn_editar" class="btn-accion" style="border:none; cursor:pointer; background:#27ae60; color:white; padding:10px 20px; border-radius:5px; font-weight:bold;">💾 ACTUALIZAR DATOS</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>