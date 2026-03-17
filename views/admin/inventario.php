<?php
session_start();
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Inventario.php";

// SEGURIDAD: Solo el Admin entra aquí
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php");
    exit();
}

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$invObj = new Inventario($db);
$productos = $invObj->leerTodo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
</head>
<body>
    <header>
        <div class="logo"><h2>📦 Gestión de Inventario</h2></div>
        <a href="../dashboard.php" class="btn-accion" style="background:#7f8c8d;">← Volver al Dashboard</a>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:var(--secondary);">NUEVO PRODUCTO O SERVICIO</h3>
            <form action="../../controllers/InventarioController.php" method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.8rem; color:gray;">DESCRIPCIÓN</label>
                        <input type="text" name="descripcion" placeholder="Ej: Proteína Whey 2lb" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.8rem; color:gray;">PRECIO (<?php echo $config['moneda_simbolo']; ?>)</label>
                        <input type="number" step="0.01" name="precio" placeholder="0.00" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.8rem; color:gray;">STOCK INICIAL</label>
                        <input type="number" name="cantidad" placeholder="Cantidad" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                </div>
                <div style="text-align:right; margin-top:20px;">
                    <button type="submit" name="agregar" class="btn-accion" style="border:none; cursor:pointer;">💾 GUARDAR PRODUCTO</button>
                </div>
            </form>
        </div>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--secondary); color: white; text-align: left;">
                        <th style="padding: 12px;">Descripción</th>
                        <th style="padding: 12px;">Precio</th>
                        <th style="padding: 12px;">Stock</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($prod = $productos->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><strong><?php echo strtoupper($prod['descripcion']); ?></strong></td>
                        <td style="padding: 12px;"><?php echo $config['moneda_simbolo'] . " " . number_format($prod['precio'], 2); ?></td>
                        <td style="padding: 12px;"><?php echo $prod['cantidad']; ?></td>
                        <td style="padding: 12px;">
                            <?php if($prod['cantidad'] <= 5): ?>
                                <span style="background: #e74c3c; color:white; padding:3px 10px; border-radius:15px; font-size:11px; font-weight:bold;">BAJO STOCK</span>
                            <?php else: ?>
                                <span style="background: #27ae60; color:white; padding:3px 10px; border-radius:15px; font-size:11px; font-weight:bold;">OK</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="editar_producto.php?id=<?php echo $prod['id']; ?>" style="text-decoration:none;">✏️</a>
                            <a href="../../controllers/InventarioController.php?eliminar_id=<?php echo $prod['id']; ?>" onclick="return confirm('¿Eliminar producto?')" style="margin-left:10px; text-decoration:none;">🗑️</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>