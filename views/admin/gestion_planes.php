<?php
session_start();
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Plan.php";

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

$db = (new Database())->getConnection();
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();
$planObj = new Plan($db);
$planes = $planObj->listarTodo();

// Tasa de cambio para cálculos de referencia
$tasa = $config['tasa_cambio'] ?? 36.65;
$tema = $_SESSION['tema'] ?? 'default';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Planes - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .tasa-ref { font-size: 12px; background: #2c3e50; color: white; padding: 4px 12px; border-radius: 15px; }
        .precio-usd { color: #27ae60; font-weight: bold; font-size: 0.9rem; display: block; }
        .input-wrapper { position: relative; width: 100%; }
        .input-hint { position: absolute; bottom: -18px; left: 5px; font-size: 10px; color: #27ae60; font-weight: bold; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-tags"></i> Gestión de Planes</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-ref">Tasa: C$ <?php echo number_format($tasa, 2); ?></span>
            <a href="../dashboard.php" class="btn-volver gris">← Volver</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <?php if(isset($_GET['msj'])): ?>
            <?php if($_GET['msj'] == 'ok'): ?>
                <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">✅ Plan creado exitosamente</div>
            <?php elseif($_GET['msj'] == 'editado'): ?>
                <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">💾 Plan actualizado</div>
            <?php elseif($_GET['msj'] == 'eliminado'): ?>
                <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center;">🗑️ Plan eliminado</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#2c3e50;">NUEVO PLAN DE MEMBRESÍA</h3>
            <form action="../../controllers/PlanController.php" method="POST">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;">
                    <input type="text" name="nombre_plan" placeholder="Nombre (Ej: Mes Pesas)" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    
                    <input type="number" name="duracion_dias" placeholder="Días (Ej: 30)" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    
                    <div class="input-wrapper">
                        <input type="number" step="0.01" name="precio" id="precio_plan" placeholder="Precio (C$)" required style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                        <small id="hint_usd" class="input-hint">Ref: $ 0.00 USD</small>
                    </div>
                </div>
                <div style="text-align:right; margin-top:25px;">
                    <button type="submit" name="agregar" class="btn-accion" style="border:none; cursor:pointer; background:#2980b9;">💾 GUARDAR PLAN</button>
                </div>
            </form>
        </div>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; color: #7f8c8d; text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px;">NOMBRE DEL PLAN</th>
                        <th style="padding: 15px;">DURACIÓN</th>
                        <th style="padding: 15px;">PRECIO (C$)</th>
                        <th style="padding: 15px;">EQUIV. (USD)</th>
                        <th style="padding: 15px; text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $planes->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;"><strong><?php echo strtoupper($row['nombre_plan']); ?></strong></td>
                        <td style="padding: 15px; color: #666;"><?php echo $row['duracion_dias']; ?> Días</td>
                        <td style="padding: 15px;"><strong>C$ <?php echo number_format($row['precio'], 2); ?></strong></td>
                        <td style="padding: 15px;">
                            <span class="precio-usd">$ <?php echo number_format($row['precio'] / $tasa, 2); ?></span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="editar_plan.php?id=<?php echo $row['id']; ?>" style="text-decoration:none; margin-right:15px; color:#f39c12; font-weight:bold;" title="Editar">✏️ Editar</a>
                            <a href="../../controllers/PlanController.php?eliminar_id=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar plan?')" style="text-decoration:none; color:#e74c3c; font-weight:bold;" title="Eliminar">🗑️ Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>