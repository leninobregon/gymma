<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Inventario.php";

$db = (new Database())->getConnection();
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();

// Tasa de cambio para la referencia visual
$tasa = $config['tipo_cambio_bcn'] ?? 36.6243;
$tema = $_SESSION['tema'] ?? 'default';

$id = $_GET['id'] ?? 0;
$query = "SELECT * FROM inventario WHERE id = ? LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) { header("Location: gestion_inventario.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .helper-text { font-size: 0.75rem; color: #27ae60; margin-top: 5px; font-weight: bold; display: block; }
        .tasa-info { font-size: 11px; background: #f1f1f1; padding: 4px 10px; border-radius: 5px; color: #555; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-box-open"></i> Editar Producto</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-info">Tasa Ref: C$ <?php echo number_format($tasa, 2); ?></span>
            <a href="gestion_inventario.php" class="btn-volver gris">← Cancelar</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0; color:#2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">DATOS DEL PRODUCTO</h3>
            
            <form action="../../controllers/InventarioController.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 20px;">
                    
                    <div style="display:flex; flex-direction:column;">
                        <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">DESCRIPCIÓN / NOMBRE</label>
                        <input type="text" name="descripcion" value="<?php echo $p['descripcion']; ?>" required 
                               style="padding:12px; border:1px solid #ddd; border-radius:8px; font-weight:bold;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">PRECIO (C$)</label>
                            <input type="number" step="0.01" name="precio" id="precio_inv" value="<?php echo $p['precio']; ?>" required 
                                   style="padding:12px; border:1px solid #ddd; border-radius:8px; font-weight:bold;">
                            <span id="ref_usd_inv" class="helper-text">Equiv: $ <?php echo number_format($p['precio'] / $tasa, 2); ?> USD</span>
                        </div>

                        <div style="display:flex; flex-direction:column;">
                            <label style="font-size:0.8rem; color:gray; margin-bottom:5px;">STOCK ACTUAL</label>
                            <input type="number" name="cantidad" value="<?php echo $p['cantidad']; ?>" required 
                                   style="padding:12px; border:1px solid #ddd; border-radius:8px;">
                        </div>
                    </div>
                </div>

                <div style="text-align:right; margin-top:30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <button type="submit" name="btn_actualizar_prod" class="btn-accion" style="border:none; cursor:pointer; width: 100%;">
                        💾 ACTUALIZAR PRODUCTO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const tasaCambio = <?php echo $tasa; ?>;
        const inputPrecioInv = document.getElementById('precio_inv');
        const displayUsd = document.getElementById('ref_usd_inv');

        inputPrecioInv.addEventListener('input', function() {
            const val = parseFloat(this.value);
            if (!isNaN(val) && val > 0) {
                const calculo = (val / tasaCambio).toFixed(2);
                displayUsd.innerText = `Equiv: $ ${calculo} USD`;
            } else {
                displayUsd.innerText = `Equiv: $ 0.00 USD`;
            }
        });
    </script>
</body>
</html>