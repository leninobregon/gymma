<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }
require_once "../../config/Database.php";
$db = (new Database())->getConnection();

// 1. Configuración y Datos
$config = $db->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$planes = $db->query("SELECT id, nombre_plan, precio FROM planes WHERE estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
$productos = $db->query("SELECT id, descripcion, precio, cantidad FROM inventario WHERE cantidad > 0")->fetchAll(PDO::FETCH_ASSOC);

// 2. Tasa de cambio
$tasa_dolar = 36.65; 

// 3. Recaudado Hoy por este Cajero
$hoy = date('Y-m-d');
$stmtTotal = $db->prepare("SELECT SUM(monto_total) as total FROM ventas WHERE id_usuario = ? AND DATE(fecha_venta) = ? AND estado != 'ANULADO'");
$stmtTotal->execute([$_SESSION['user_id'], $hoy]);
$recaudado = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$ultimo_id = null;
if(isset($_GET['res'])) {
    $st = $db->prepare("SELECT id FROM ventas WHERE id_usuario = ? ORDER BY id DESC LIMIT 1");
    $st->execute([$_SESSION['user_id']]);
    $ultimo_id = $st->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CAJA - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        /* DISEÑO EQUILIBRADO Y PROFESIONAL */
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #000; margin: 0; }
        .font-negra { color: #000 !important; font-weight: 600; }
        
        .recaudado-header { 
            background: #2d3436; color: white; padding: 12px 20px; 
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .monto-total { font-size: 1.5rem; font-weight: bold; color: #55efc4; }

        .caja-layout { display: grid; grid-template-columns: 320px 1fr; gap: 20px; padding: 20px; }
        .stat-card { background: white; padding: 18px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        /* FORMULARIOS AJUSTADOS */
        input, select { 
            width: 100%; padding: 10px; font-size: 0.95rem; 
            border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;
            color: #000; font-weight: 500;
        }

        /* CALCULADORA */
        .calculadora-box { background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #ced4da; }
        .vuelto-txt { font-size: 1.3rem; color: #27ae60; font-weight: 800; text-align: center; display: block; }

        /* TABLA HISTORIAL */
        .tabla-historial { width: 100%; border-collapse: collapse; }
        .tabla-historial th { background: #f1f2f6; color: #2d3436; padding: 10px; text-align: left; font-size: 0.85rem; border-bottom: 2px solid #dfe6e9; }
        .tabla-historial td { 
            padding: 10px; border-bottom: 1px solid #eee; 
            font-size: 0.95rem; color: #000 !important; font-weight: 600;
        }
        .ticket-id { color: #e67e22; font-weight: bold; }

        .btn-pagar { 
            width: 100%; padding: 12px; background: #27ae60; color: white; border: none; 
            border-radius: 6px; font-size: 1.1rem; font-weight: bold; cursor: pointer;
        }
        .btn-pagar:hover { background: #219150; }
        .btn-pagar:disabled { background: #b2bec3; cursor: not-allowed; }

        .btn-salir {
            background: #ff7675; color: white; padding: 8px 15px; 
            text-decoration: none; border-radius: 5px; font-size: 0.85rem; font-weight: bold;
        }
        .btn-salir:hover { background: #ee5253; }

        @media (max-width: 1000px) { .caja-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <header class="recaudado-header">
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="font-size: 1.8rem;">💰</span>
            <h2 style="margin:0; font-size: 1.2rem;">CAJA: <?php echo $config['nombre_gym']; ?></h2>
        </div>
        <div style="text-align: right; display: flex; align-items: center; gap: 20px;">
            <div>
                <small style="display:block; font-size: 0.65rem; opacity: 0.8; text-transform: uppercase;">Recaudado Hoy</small>
                <span class="monto-total">C$ <?php echo number_format($recaudado, 2); ?></span>
            </div>
            <a href="../dashboard.php" class="btn-salir">Regresar al Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        
        <?php if($ultimo_id): ?>
            <div class="alert alert-success" style="display: flex; justify-content: space-between; align-items: center; margin: 0 20px 20px 20px; padding: 8px 20px; border-left: 5px solid #27ae60;">
                <span style="font-size: 0.95rem;">✅ Venta <b>#<?php echo $ultimo_id; ?></b> registrada correctamente</span>
                <button onclick="reimprimir(<?php echo $ultimo_id; ?>)" style="background:#0984e3; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size: 0.85rem;">🖨️ IMPRIMIR TICKET</button>
            </div>
        <?php endif; ?>

        <div class="caja-layout">
            
            <div class="stat-card">
                <h3 class="font-negra" style="margin-top:0; font-size: 1rem;">🔍 BUSCAR CLIENTE</h3>
                <input type="text" id="input_busqueda" placeholder="Nombre o Cédula..." onkeyup="buscarSocio()" autofocus>
                
                <div id="ficha_socio" style="display:none; margin-top:15px; padding:15px; background:#fdfdfd; border: 1px solid #00b894; border-radius:8px;">
                    <h3 id="s_nombre" class="font-negra" style="margin:0; font-size: 1rem;"></h3>
                    <p style="font-size: 0.85rem; margin: 5px 0;">Tel: <b id="s_tel"></b></p>
                    <div id="s_badge" style="padding:6px; color:white; border-radius:4px; text-align:center; font-weight:bold; font-size: 0.85rem;"></div>
                    <input type="hidden" id="id_socio_hidden">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <div class="stat-card" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <form action="../../controllers/VentaController.php" method="POST">
                        <input type="hidden" name="id_socio" id="id_socio_hidden_form">
                        
                        <label class="font-negra" style="font-size: 0.85rem;">TIPO DE VENTA</label>
                        <select name="tipo_cobro" id="tipo_cobro" onchange="validarYActualizar()">
                            <option value="PRODUCTO">Venta de Producto</option>
                            <option value="PLAN">Pago de Membresía</option>
                        </select>

                        <div id="div_productos">
                            <label class="font-negra" style="font-size: 0.85rem;">PRODUCTO / ARTÍCULO</label>
                            <select name="id_producto" id="select_prod" onchange="actualizarCalculos()">
                                <?php foreach($productos as $p): ?>
                                    <option value="<?=$p['id']?>" data-precio="<?=$p['precio']?>"><?=$p['descripcion']?> (C$ <?=$p['precio']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <label class="font-negra" style="font-size: 0.85rem;">CANTIDAD</label>
                            <input type="number" name="cantidad" id="cant_vender" value="1" min="1" onchange="actualizarCalculos()" onkeyup="actualizarCalculos()">
                        </div>

                        <div id="div_planes" style="display:none;">
                            <label class="font-negra" style="font-size: 0.85rem;">SELECCIONE PLAN</label>
                            <select name="id_plan" id="select_plan" onchange="actualizarCalculos()">
                                <?php foreach($planes as $pl): ?>
                                    <option value="<?=$pl['id']?>" data-precio="<?=$pl['precio']?>"><?=$pl['nombre_plan']?> (C$ <?=$pl['precio']?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="procesar_pago" id="btnPagar" class="btn-pagar">💰 REGISTRAR PAGO</button>
                    </form>

                    <div class="calculadora-box">
                        <label class="font-negra" style="display:block; text-align:center; margin-bottom:8px; font-size: 0.85rem;">CALCULADORA DE VUELTO</label>
                        <div style="display:flex; gap:5px; margin-bottom: 10px;">
                            <input type="number" id="pago_cajero" step="0.01" placeholder="Paga con..." onkeyup="calcularVuelto()" style="margin-bottom:0;">
                            <select id="moneda" onchange="calcularVuelto()" style="width:75px; margin-bottom:0; background: #eee;">
                                <option value="COR">C$</option>
                                <option value="USD">$</option>
                            </select>
                        </div>
                        <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #ddd; text-align:center;">
                            <small style="font-size: 0.75rem; color: #777;">TOTAL:</small>
                            <div id="total_mostrar" style="font-size:1.3rem; font-weight:bold; color: #000;">C$ 0.00</div>
                            <hr style="margin:8px 0; border:0; border-top:1px solid #eee;">
                            <small style="font-size: 0.75rem; color: #777;">SU VUELTO:</small>
                            <span id="vuelto_txt" class="vuelto-txt">C$ 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h3 class="font-negra" style="margin-top:0; font-size: 1rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">📜 ÚLTIMAS VENTAS DE HOY</h3>
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="tabla-historial">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>CONCEPTO</th>
                                    <th style="text-align:right;">MONTO</th>
                                    <th style="text-align:center;">---</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtH = $db->prepare("SELECT id, concepto, monto_total FROM ventas WHERE id_usuario = ? AND DATE(fecha_venta) = ? ORDER BY id DESC LIMIT 10");
                                $stmtH->execute([$_SESSION['user_id'], $hoy]);
                                while($v = $stmtH->fetch()): ?>
                                <tr>
                                    <td class="ticket-id">#<?=$v['id']?></td>
                                    <td><?=$v['concepto']?></td>
                                    <td style="text-align:right; font-size: 1rem; color: #27ae60;"><b>C$ <?=number_format($v['monto_total'], 2)?></b></td>
                                    <td style="text-align:center;">
                                        <button onclick="reimprimir(<?=$v['id']?>)" style="padding:4px 8px; cursor:pointer; background:none; border: 1px solid #ddd; border-radius:4px; font-size: 1.1rem;">🖨️</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    const TASA_DOLAR = <?=$tasa_dolar?>;
    let socioEstatus = "";

    function buscarSocio() {
        let v = document.getElementById('input_busqueda').value;
        if(v.length < 3) return;
        fetch('../../ajax/buscar_socio.php?consulta=' + v).then(r => r.json()).then(data => {
            if(data.status !== 'no_encontrado') {
                document.getElementById('ficha_socio').style.display = 'block';
                document.getElementById('s_nombre').innerText = data.nombre;
                document.getElementById('s_tel').innerText = data.telefono;
                document.getElementById('id_socio_hidden').value = data.id;
                document.getElementById('id_socio_hidden_form').value = data.id;
                socioEstatus = data.status;
                let b = document.getElementById('s_badge');
                b.innerText = data.mensaje;
                b.style.background = (data.status === 'vigente' ? '#27ae60' : '#d63031');
                validarYActualizar();
            }
        });
    }

    function validarYActualizar() {
        let tipo = document.getElementById('tipo_cobro').value;
        let btn = document.getElementById('btnPagar');
        document.getElementById('div_productos').style.display = (tipo === 'PRODUCTO' ? 'block' : 'none');
        document.getElementById('div_planes').style.display = (tipo === 'PLAN' ? 'block' : 'none');

        if(tipo === 'PLAN' && socioEstatus === 'vigente') {
            btn.disabled = true;
            btn.innerText = "CLIENTE YA VIGENTE";
            btn.style.background = "#b2bec3";
        } else {
            btn.disabled = false;
            btn.innerText = "💰 REGISTRAR PAGO";
            btn.style.background = "#27ae60";
        }
        actualizarCalculos();
    }

    function actualizarCalculos() {
        let tipo = document.getElementById('tipo_cobro').value;
        let select = (tipo === 'PRODUCTO') ? document.getElementById('select_prod') : document.getElementById('select_plan');
        let precio = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'));
        let cantidad = (tipo === 'PRODUCTO') ? parseInt(document.getElementById('cant_vender').value) || 1 : 1;
        
        let total = precio * cantidad;
        document.getElementById('total_mostrar').innerText = "C$ " + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        calcularVuelto();
    }

    function calcularVuelto() {
        let totalTxt = document.getElementById('total_mostrar').innerText.replace('C$ ', '').replace(/,/g, '');
        let totalCords = parseFloat(totalTxt);
        let pago = parseFloat(document.getElementById('pago_cajero').value) || 0;
        let moneda = document.getElementById('moneda').value;
        
        let pagoEnCords = (moneda === 'USD') ? pago * TASA_DOLAR : pago;
        let vuelto = pagoEnCords - totalCords;

        let elVuelto = document.getElementById('vuelto_txt');
        elVuelto.innerText = "C$ " + (vuelto > 0 ? vuelto.toLocaleString('en-US', {minimumFractionDigits: 2}) : "0.00");
        elVuelto.style.color = (vuelto < 0) ? "#e74c3c" : "#27ae60";
    }

    function reimprimir(id) {
        window.open('../caja/imprimir_recibo.php?id=' + id, 'Ticket', 'width=400,height=600');
    }

    actualizarCalculos();
    </script>
</body>
</html>