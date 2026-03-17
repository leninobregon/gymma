<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }

require_once "../../config/Database.php";
$db = (new Database())->getConnection();

// Traer la configuración del gimnasio
$stmtConf = $db->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmtConf->fetch(PDO::FETCH_ASSOC);

// Cargar datos
$planes = $db->query("SELECT id, nombre_plan, precio FROM planes WHERE estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
$productos = $db->query("SELECT id, descripcion, precio, cantidad FROM inventario WHERE cantidad > 0")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .caja-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; margin-top: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333 !important; font-size: 0.85rem; text-transform: uppercase; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 15px; background: white; color: #333; }
        .ficha-socio-box { display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 5px solid var(--primary); }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert-error { background: #ff7675; color: white; }
        .alert-success { background: #55efc4; color: #00b894; }
        @media (max-width: 900px) { .caja-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <img src="../../public/img/<?php echo $config['logo_ruta']; ?>" style="height: 40px;" onerror="this.style.display='none'">
            <h2><?php echo $config['nombre_gym']; ?></h2>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="../dashboard.php" class="btn-accion" style="background: #7f8c8d; text-decoration: none;">← REGRESAR</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <h2 style="color: var(--primary); margin-bottom: 5px;">💰 Módulo de Caja</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['res'])): ?>
            <div class="alert alert-success">✅ Venta registrada correctamente.</div>
        <?php endif; ?>

        <div class="caja-container">
            <div class="stat-card" style="text-align: left;">
                <h3 style="font-size: 1rem; color: var(--primary); margin-bottom: 15px;">🔍 IDENTIFICAR SOCIO</h3>
                <input type="text" id="input_busqueda" placeholder="Nombre o Cédula..." onkeyup="buscarSocio()">
                <div id="ficha_socio" class="ficha-socio-box">
                    <h2 id="s_nombre" style="font-size: 1.2rem; color: var(--primary); margin: 0;"></h2>
                    <p>📱 Tel: <span id="s_tel"></span></p>
                    <div id="s_badge" style="margin-top:10px; padding: 8px; border-radius: 5px; text-align: center; color: white;"></div>
                </div>
            </div>

            <div class="stat-card" style="text-align: left;">
                <h3 style="font-size: 1rem; color: var(--primary); margin-bottom: 15px;">🛒 PROCESAR VENTA</h3>
                <form action="../../controllers/VentaController.php" method="POST">
                    <input type="hidden" name="id_socio" id="id_socio_hidden">
                    
                    <label>Tipo de Operación</label>
                    <select name="tipo_cobro" id="tipo_cobro" onchange="toggleVenta()">
                        <option value="PRODUCTO">Venta de Artículo / Suplemento</option>
                        <option value="PLAN">Renovación de Membresía</option>
                    </select>

                    <div id="div_productos" style="background: #f1f4f6; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <label>Seleccionar Artículo</label>
                        <select name="id_producto">
                            <?php foreach($productos as $pr): ?>
                                <option value="<?= $pr['id'] ?>"><?= $pr['descripcion'] ?> (Stock: <?= $pr['cantidad'] ?>) - C$ <?= number_format($pr['precio'], 2) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Cantidad</label>
                        <input type="number" name="cantidad" value="1" min="1">
                    </div>

                    <div id="div_planes" style="display:none; background: #f1f4f6; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <label>Seleccionar Nuevo Plan</label>
                        <select name="id_plan">
                            <?php foreach($planes as $pl): ?>
                                <option value="<?= $pl['id'] ?>"><?= $pl['nombre_plan'] ?> - C$ <?= number_format($pl['precio'], 2) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label>Método de Pago</label>
                    <select name="metodo_pago">
                        <option value="EFECTIVO">Efectivo 💵</option>
                        <option value="TRANSFERENCIA">Transferencia 📱</option>
                    </select>

                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #ffeeba;">
                        <label style="color: #856404 !important;">🖨️ ¿IMPRIMIR TICKET?</label>
                        <div style="display: flex; gap: 20px;">
                            <label style="display:flex; align-items:center; gap:5px;"><input type="radio" name="opcion_ticket" value="si" checked> SÍ</label>
                            <label style="display:flex; align-items:center; gap:5px;"><input type="radio" name="opcion_ticket" value="no"> NO</label>
                        </div>
                    </div>

                    <button type="submit" name="procesar_pago" class="btn-accion" style="width: 100%; border: none; padding: 15px; cursor: pointer;">
                        🚀 REGISTRAR Y FINALIZAR
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function buscarSocio() {
        let valor = document.getElementById('input_busqueda').value;
        let ficha = document.getElementById('ficha_socio');
        if(valor.length < 3) { ficha.style.display = 'none'; return; }
        fetch('../../ajax/buscar_socio.php?consulta=' + valor)
        .then(r => r.json()).then(data => {
            if(data.status !== 'no_encontrado') {
                ficha.style.display = 'block';
                document.getElementById('id_socio_hidden').value = data.id;
                document.getElementById('s_nombre').innerText = data.nombre;
                document.getElementById('s_tel').innerText = data.telefono;
                let b = document.getElementById('s_badge');
                b.innerText = data.mensaje;
                b.style.background = (data.status === 'vigente' ? '#27ae60' : '#e74c3c');
            } else { ficha.style.display = 'none'; }
        });
    }

    function toggleVenta() {
        let t = document.getElementById('tipo_cobro').value;
        document.getElementById('div_productos').style.display = (t === 'PRODUCTO') ? 'block' : 'none';
        document.getElementById('div_planes').style.display = (t === 'PLAN') ? 'block' : 'none';
    }
    </script>
</body>
</html>