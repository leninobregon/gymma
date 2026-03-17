<?php
session_start();
require_once "../../config/Database.php";
require_once "../../classes/Usuario.php";
require_once "../../config/AppConfig.php";

// Seguridad: Validar sesión
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php");
    exit();
}

$db = (new Database())->getConnection();
$userObj = new Usuario($db);
$config = (new AppConfig($db))->obtenerConfig();

$tasa_cambio = $config['tasa_cambio'] ?? 36.65;
$usuarios = $userObj->listarUsuarios(); // Asegúrate de que este método exista en la clase
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - GYM MA DB</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .tasa-destacada { 
            font-size: 13px; font-weight: bold; background: #2d3436; 
            color: #f1c40f; padding: 6px 14px; border-radius: 20px;
            border: 1px solid #f1c40f;
        }
        .alerta {
            padding: 15px; margin-bottom: 20px; border-radius: 8px;
            text-align: center; font-weight: bold; font-size: 14px;
        }
        .alerta-exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alerta-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .tabla-usuarios { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-usuarios th { background: #f8f9fa; color: #7f8c8d; padding: 12px; text-align: left; font-size: 12px; border-bottom: 2px solid #eee; }
        .tabla-usuarios td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-mini { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>👥 Gestión de Usuarios</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-destacada">TASA REF: C$ <?= $tasa_cambio ?></span>
            <a href="../dashboard.php" class="btn-accion" style="background:#7f8c8d; text-decoration: none;">← Volver</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        
        <?php if(isset($_GET['res'])): ?>
            <?php if($_GET['res'] == 'registrado'): ?>
                <div class="alerta alerta-exito">✅ Usuario registrado correctamente.</div>
            <?php elseif($_GET['res'] == 'actualizado'): ?>
                <div class="alerta alerta-exito">💾 Cambios guardados con éxito.</div>
            <?php elseif($_GET['res'] == 'eliminado'): ?>
                <div class="alerta alerta-exito">🗑️ Usuario eliminado del sistema.</div>
            <?php elseif($_GET['res'] == 'autoerror'): ?>
                <div class="alerta alerta-error">❌ No puedes eliminar tu propia cuenta.</div>
            <?php elseif($_GET['res'] == 'error'): ?>
                <div class="alerta alerta-error">⚠️ Hubo un problema al procesar la solicitud.</div>
            <?php endif; ?>
        <?php endif; ?>

        
        <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#2c3e50; font-size: 16px; border-bottom: 2px solid #3498db; display: inline-block; padding-bottom: 5px;">NUEVO USUARIO</h3>
            <form action="../../controllers/UsuarioController.php" method="POST" style="margin-top:15px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                    <div><label style="font-size:11px; font-weight:bold;">NOMBRE</label>
                    <input type="text" name="nombre" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                    
                    <div><label style="font-size:11px; font-weight:bold;">APELLIDO</label>
                    <input type="text" name="apellido" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                    
                    <div><label style="font-size:11px; font-weight:bold;">ALIAS USUARIO</label>
                    <input type="text" name="usuario" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                    
                    <div><label style="font-size:11px; font-weight:bold;">CÉDULA</label>
                    <input type="text" name="cedula" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                    
                    <div><label style="font-size:11px; font-weight:bold;">CONTRASEÑA</label>
                    <input type="password" name="password" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></div>
                    
                    <div><label style="font-size:11px; font-weight:bold;">ROL</label>
                    <select name="rol" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                        <option value="CAJA">CAJERO / CAJA</option>
                        <option value="ADMIN">ADMINISTRADOR</option>
                    </select></div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" name="btn_registrar_user" class="btn-accion" style="border:none; cursor:pointer; background:#3498db;">💾 GUARDAR USUARIO</button>
                </div>
            </form>
        </div>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>NOMBRE COMPLETO</th>
                        <th>USUARIO (ALIAS)</th>
                        <th>ROL</th>
                        <th>CÉDULA</th>
                        <th style="text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $usuarios->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><strong><?php echo strtoupper($row['nombre'] . " " . $row['apellido']); ?></strong></td>
                        <td><code style="background:#f0f0f0; padding:2px 5px; border-radius:4px;"><?php echo $row['usuario']; ?></code></td>
                        <td>
                            <span style="font-size:11px; font-weight:bold; color: <?= $row['rol'] == 'ADMIN' ? '#e67e22' : '#2980b9' ?>">
                                <?= $row['rol'] ?>
                            </span>
                        </td>
                        <td><?php echo $row['cedula']; ?></td>
                        <td style="text-align: center;">
                            <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn-mini" style="background:#f1c40f; color:black;">✏️ Editar</a>
                            
                            <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <a href="../../controllers/UsuarioController.php?eliminar=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('¿Está seguro de eliminar permanentemente a este usuario?')" 
                                   class="btn-mini" style="background:#e74c3c; color:white; margin-left:5px;">🗑️ Borrar</a>
                            <?php else: ?>
                                <small style="color:#bdc3c7; font-style:italic;">(Tú)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>