<?php
session_start();
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Plan.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

$db = (new Database())->getConnection();
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();
$planObj = new Plan($db);

// Tasa de cambio para la referencia visual
$tasa = $config['tipo_cambio_bcn'] ?? 36.6243;
$tema = $_SESSION['tema'] ?? 'default';

$id = $_GET['id'] ?? 0;
$p = $planObj->obtenerPorId($id);

if (!$p) { header("Location: gestion_planes.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Plan - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .helper-text { font-size: 0.75rem; color: #27ae60; margin-top: 5px; font-weight: bold; }
        .input-group { position: relative; }
        .tasa-badge { font-size: 10px; background: #eee; padding: 2px 8px; border-radius: 10px; color: #666; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-tag"></i> Editar Plan</h2></div>
        <div style="display:flex; align-items:center; gap:10px;">
            <span class="tasa-badge">Tasa BCN: C$ <?php echo number_format($tasa, 4); ?></span>
            <a href="gestion_planes.php" class="btn-volver gris">← Cancelar</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0; color:#2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">CONFIGURACIÓN DE MEMBRESÍA</h3>
            
            <form action="../../controllers/PlanController.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 20px;">
                    
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">NOMBRE DEL PLAN</label>
                        <input type="text" name="nombre_plan" value="<?php echo $p['nombre_plan']; ?>" required 
                               style="padding:12px; border:1px solid #ddd; border-radius:8px; font-weight:bold;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">DURACIÓN (DÍAS)</label>
                            <input type="number" name="duracion_dias" value="<?php echo $p['duracion_dias']; ?>" required 
                                   style="padding:12px; border:1px solid #ddd; border-radius:8px;">
                        </div>

                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">PRECIO (C$)</label>
                            <input type="number" step="0.01" name="precio" id="precio_input" value="<?php echo $p['precio']; ?>" required 
                                   style="padding:12px; border:1px solid #ddd; border-radius:8px; font-weight:bold; color:#2c3e50;">
                            <span id="ref_usd" class="helper-text">Ref: $ <?php echo number_format($p['precio'] / $tasa, 2); ?> USD</span>
                        </div>
                    </div>
                </div>

                <div style="text-align:right; margin-top:30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <button type="submit" name="btn_actualizar_plan" class="btn-accion" style="border:none; cursor:pointer; width: 100%;">
                        💾 GUARDAR CAMBIOS
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script para calcular la referencia en USD dinámicamente
        const tasa = <?php echo $tasa; ?>;
        const inputPrecio = document.getElementById('precio_input');
        const spanRef = document.getElementById('ref_usd');

        inputPrecio.addEventListener('input', function() {
            const valor = parseFloat(this.value);
            if (!isNaN(valor) && valor > 0) {
                const usd = (valor / tasa).toFixed(2);
                spanRef.innerText = `Ref: $ ${usd} USD`;
            } else {
                spanRef.innerText = `Ref: $ 0.00 USD`;
            }
        });
    </script>
</body>
</html>