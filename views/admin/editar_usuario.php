<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}
require_once "../../config/Database.php";
require_once "../../classes/Usuario.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$userObj = new Usuario($db);
$config = (new AppConfig($db))->obtenerConfig();

$tasa_cambio = $config['tasa_cambio'] ?? 36.65;
$id = $_GET['id'] ?? 0;
$u = $userObj->obtenerPorId($id);

if (!$u) { header("Location: gestion_usuarios.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - GYM MA DB</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        /* ESTILO TASA DE CAMBIO SOLICITADO */
        .tasa-destacada { 
            font-size: 13px; 
            font-weight: bold; 
            background: #2d3436; 
            color: #f1c40f; /* Letra Amarilla */
            padding: 6px 14px; 
            border-radius: 20px;
            border: 1px solid #f1c40f;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .input-group label { font-size: 11px; font-weight: bold; color: #7f8c8d; margin-bottom: 5px; }
        .input-group input, .input-group select { 
            padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>✏️ Editar Usuario</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-destacada">TASA REF: C$ <?= $tasa_cambio ?></span>
            <a href="gestion_usuarios.php" class="btn-accion" style="background:#7f8c8d; text-decoration: none;">← Cancelar</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 800px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <form action="../../controllers/UsuarioController.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label>NOMBRE</label>
                        <input type="text" name="nombre" value="<?php echo $u['nombre']; ?>" required>
                    </div>

                    <div class="input-group">
                        <label>APELLIDO</label>
                        <input type="text" name="apellido" value="<?php echo $u['apellido']; ?>" required>
                    </div>

                    <div class="input-group">
                        <label>USUARIO</label>
                        <input type="text" name="usuario" value="<?php echo $u['usuario']; ?>" required>
                    </div>

                    <div class="input-group">
                        <label>CÉDULA</label>
                        <input type="text" name="cedula" value="<?php echo $u['cedula']; ?>" required>
                    </div>

                    <div class="input-group">
                        <label>ROL</label>
                        <select name="rol">
                            <option value="CAJA" <?php if($u['rol']=='CAJA') echo 'selected'; ?>>CAJA</option>
                            <option value="ADMIN" <?php if($u['rol']=='ADMIN') echo 'selected'; ?>>ADMIN</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>NUEVA CONTRASEÑA (OPCIONAL)</label>
                        <input type="password" name="password" placeholder="Dejar en blanco para no cambiar">
                    </div>
                </div>

                <div style="text-align:right; margin-top:20px; border-top: 1px solid #eee; padding-top: 20px;">
                    <button type="submit" name="btn_actualizar_user" class="btn-accion" style="border:none; cursor:pointer; background: #27ae60;">
                        💾 ACTUALIZAR DATOS
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>