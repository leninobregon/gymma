<?php
session_start();
// SEGURIDAD: Solo Admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Inventario.php";

$db = (new Database())->getConnection();
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();
$invObj = new Inventario($db);
$productos = $invObj->leerTodo();

// Tasa de cambio para cálculos rápidos
$simbolo = $config['moneda_simbolo'] ?? 'C$';
$tasa = $config['tasa_cambio'] ?? $config['tipo_cambio_bcn'] ?? 36.65;
$tema = $_SESSION['tema'] ?? 'default';

$msj = $_GET['msj'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Bimoneda - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .badge-usd { color: #27ae60; font-weight: bold; font-size: 0.85rem; display: block; }
        .tasa-flotante { background: #2c3e50; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-warehouse"></i> Gestión de Inventario</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-flotante">Tasa: <?php echo $simbolo; ?> <?php echo number_format($tasa, 2); ?></span>
            <a href="../dashboard.php" class="btn-volver gris">← Volver</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <?php if ($msj === 'agregado'): ?>
            <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;">✅ Producto agregado correctamente</div>
        <?php elseif ($msj === 'actualizado'): ?>
            <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;">✅ Producto actualizado correctamente</div>
        <?php elseif ($msj === 'eliminado'): ?>
            <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;">✅ Producto eliminado correctamente</div>
        <?php elseif ($error === '1'): ?>
            <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px;">❌ Error en la operación</div>
        <?php endif; ?>

        <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h3 style="margin-top:0; color:#2c3e50;">NUEVO PRODUCTO O SERVICIO</h3>
            <form action="../../controllers/InventarioController.php" method="POST">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px;">
                    <input type="text" name="descripcion" placeholder="Descripción (ej: Proteína Whey)" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                    
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="precio" id="precio_new" placeholder="Precio (<?php echo $simbolo; ?>)" required style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                        <small id="ref_usd_new" style="color:#27ae60; font-weight:bold; position:absolute; bottom:-18px; left:5px; font-size:10px;">Ref: $ 0.00</small>
                    </div>

                    <input type="number" name="cantidad" placeholder="Stock" required style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                </div>
                <div style="text-align:right; margin-top:25px;">
                    <button type="submit" name="agregar" class="btn-accion" style="border:none; cursor:pointer; background:#3498db;">💾 GUARDAR EN SISTEMA</button>
                </div>
            </form>
        </div>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="margin-bottom: 15px; display: flex; gap: 10px;">
                <input type="text" id="busqueda_inventario" placeholder="<i class='fas fa-search'></i> Buscar producto..." style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; flex: 1;">
                <select id="filtro_stock" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <option value="todos">Todos</option>
                    <option value="bajo">Stock bajo (≤5)</option>
                    <option value="alto">Stock alto (>5)</option>
                </select>
            </div>
            <table style="width: 100%; border-collapse: collapse;" id="tabla_inventario">
                <thead>
                    <tr style="background: #f8f9fa; color: #7f8c8d; text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px;">DESCRIPCIÓN</th>
                        <th style="padding: 15px;">PRECIO (<?php echo $simbolo; ?>)</th>
                        <th style="padding: 15px;">REF (USD)</th>
                        <th style="padding: 15px;">STOCK</th>
                        <th style="padding: 15px; text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="tbody_inventario">
                    <?php while ($prod = $productos->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="fila-producto" data-descripcion="<?php echo strtolower($prod['descripcion']); ?>" data-stock="<?php echo $prod['cantidad']; ?>" style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px;">
                            <strong><?php echo strtoupper($prod['descripcion']); ?></strong>
                        </td>
                        <td style="padding: 15px; font-weight:bold;">
                            <?php echo $simbolo; ?> <?php echo number_format($prod['precio'], 2); ?>
                        </td>
                        <td style="padding: 15px;">
                            <span class="badge-usd">$ <?php echo number_format($prod['precio'] / $tasa, 2); ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <?php if($prod['cantidad'] <= 5): ?>
                                <span style="color:#e74c3c; font-weight:bold;">⚠️ <?php echo $prod['cantidad']; ?> (Bajo)</span>
                            <?php else: ?>
                                <span style="color:#2ecc71; font-weight:bold;"><?php echo $prod['cantidad']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <a href="editar_producto.php?id=<?php echo $prod['id']; ?>" class="btn-edit" style="text-decoration:none; padding:5px 10px; background:#f1f1f1; border-radius:5px;">✏️ Editar</a>
                            <a href="../../controllers/InventarioController.php?eliminar_id=<?php echo $prod['id']; ?>" onclick="return confirm('¿Eliminar producto?')" class="btn-delete" style="margin-left:10px; text-decoration:none;">🗑️</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const tasa = <?php echo $tasa; ?>;
        const input = document.getElementById('precio_new');
        const ref = document.getElementById('ref_usd_new');

        input.addEventListener('input', () => {
            let val = parseFloat(input.value) || 0;
            ref.innerText = `Ref: $ ${(val / tasa).toFixed(2)} USD`;
        });

        // Filtrado de inventario
        const inputBusq = document.getElementById('busqueda_inventario');
        const filtroStock = document.getElementById('filtro_stock');
        const filas = document.querySelectorAll('.fila-producto');

        function filtrarInventario() {
            const texto = inputBusq.value.toLowerCase();
            const stock = filtroStock.value;
            
            filas.forEach(fila => {
                const desc = fila.dataset.descripcion;
                const cant = parseInt(fila.dataset.stock);
                const coincideDesc = desc.includes(texto);
                let coincideStock = true;
                if (stock === 'bajo') coincideStock = cant <= 5;
                else if (stock === 'alto') coincideStock = cant > 5;
                
                fila.style.display = (coincideDesc && coincideStock) ? '' : 'none';
            });
        }

        inputBusq.addEventListener('keyup', filtrarInventario);
        filtroStock.addEventListener('change', filtrarInventario);
    </script>
</body>
</html>