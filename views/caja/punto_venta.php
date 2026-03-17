<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }
require_once "../../config/Database.php";
$db = (new Database())->getConnection();

// 0. CONFIGURACIÓN MULTIMONEDA
$moneda_usuario = $_SESSION['moneda'] ?? 'COR'; // COR o USD
$tasa_dolar = 36.65; 
$simbolo = ($moneda_usuario === 'USD') ? '$' : 'C$';

// Función para mostrar montos convertidos en la interfaz
function mostrarMonto($monto, $moneda, $tasa) {
    return ($moneda === 'USD') ? $monto / $tasa : $monto;
}

// 1. VALIDACIÓN DE CAJA ACTIVA
$stmtCaja = $db->prepare("SELECT id FROM cajas WHERE estado = 'ABIERTA' AND id_usuario = ? LIMIT 1");
$stmtCaja->execute([$_SESSION['user_id']]);
$cajaActual = $stmtCaja->fetch(PDO::FETCH_ASSOC);

if (!$cajaActual) {
    header("Location: apertura_caja.php");
    exit();
}
$_SESSION['id_caja'] = $cajaActual['id'];

// 2. CARGA DE DATOS
$config = $db->query("SELECT * FROM configuracion LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$planes = $db->query("SELECT id, nombre_plan, precio FROM planes WHERE estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
$productos = $db->query("SELECT id, descripcion, precio, cantidad FROM inventario WHERE cantidad > 0")->fetchAll(PDO::FETCH_ASSOC);

// 3. RECAUDADO ESPECÍFICO (Se guarda en C$, se muestra según sesión)
$stmtTotal = $db->prepare("SELECT SUM(monto_total) as total FROM ventas WHERE id_caja = ? AND estado != 'ANULADO'");
$stmtTotal->execute([$_SESSION['id_caja']]);
$recaudadoCords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$recaudadoTurno = mostrarMonto($recaudadoCords, $moneda_usuario, $tasa_dolar);

$ultimo_id = null;
if(isset($_GET['res'])) {
    $st = $db->prepare("SELECT id FROM ventas WHERE id_caja = ? ORDER BY id DESC LIMIT 1");
    $st->execute([$_SESSION['id_caja']]);
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
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #000; margin: 0; }
        .font-negra { color: #000 !important; font-weight: 600; }
        .recaudado-header { background: #2d3436; color: white; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .monto-total { font-size: 1.5rem; font-weight: bold; color: #55efc4; }
        .caja-layout { display: grid; grid-template-columns: 320px 1fr; gap: 20px; padding: 20px; }
        .stat-card { background: white; padding: 18px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        input, select { width: 100%; padding: 10px; font-size: 0.95rem; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; color: #000; font-weight: 500; }
        
        .calculadora-box { background: #f1f2f6; padding: 15px; border-radius: 10px; border: 1px solid #ced4da; }
        .label-calc { color: #2d3436 !important; font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 5px; }
        .vuelto-txt { font-size: 1.5rem; color: #27ae60; font-weight: 800; text-align: center; display: block; }
        
        .btn-pagar { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 6px; font-size: 1.1rem; font-weight: bold; cursor: pointer; }
        .btn-dashboard { background: #34495e; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.85rem; font-weight: bold; }
        .btn-cerrar { background: #d63031; color: white; padding: 10px; text-decoration: none; border-radius: 5px; font-size: 0.85rem; font-weight: bold; display: block; text-align: center; margin-top: 20px; }
        .tabla-historial { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-historial td { padding: 10px; border-bottom: 1px solid #eee; font-weight: 600; color: #000; }
    </style>
</head>
<body>

    <header class="recaudado-header">
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="font-size: 1.8rem;">🏦</span>
            <h2 style="margin:0; font-size: 1.2rem;">CAJA ACTIVA #<?php echo $_SESSION['id_caja']; ?></h2>
        </div>
        <div style="text-align: right; display: flex; align-items: center; gap: 20px;">
            <div>
                <small style="display:block; font-size: 0.65rem; opacity: 0.8; text-transform: uppercase;">Recaudado (<?php echo $moneda_usuario; ?>)</small>
                <span class="monto-total"><?php echo $simbolo . " " . number_format($recaudadoTurno, 2); ?></span>
            </div>
            <a href="../dashboard.php" class="btn-dashboard">🏠 IR AL DASHBOARD</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <?php if($ultimo_id): ?>
            <div style="background: #e6fffa; color: #27ae60; padding: 15px; margin: 20px; border-left: 5px solid #27ae60; display: flex; justify-content: space-between; align-items: center;">
                <span>✅ Venta <b>#<?php echo $ultimo_id; ?></b> procesada con éxito.</span>
                <button onclick="reimprimir(<?php echo $ultimo_id; ?>)" style="background:#0984e3; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; font-weight:bold;">🖨️ IMPRIMIR TICKET</button>
            </div>
        <?php endif; ?>

        <div class="caja-layout">
            <div class="stat-card">
                <h3 class="font-negra" style="margin-top:0; font-size: 1rem;">🔍 CLIENTE</h3>
                <input type="text" id="input_busqueda" placeholder="Buscar socio..." onkeyup="buscarSocio()" autofocus>
                
                <div id="ficha_socio" style="display:none; margin-top:15px; padding:15px; background:#f9f9f9; border: 1px solid #00b894; border-radius:8px;">
                    <h3 id="s_nombre" class="font-negra" style="margin:0;"></h3>
                    <p id="s_tel" style="font-size: 0.85rem; margin: 5px 0; color: #666;"></p>
                    <div id="s_badge" style="padding:6px; color:white; border-radius:4px; text-align:center; font-weight:bold; font-size: 0.8rem;"></div>
                    <input type="hidden" id="id_socio_real_hidden">
                </div>

                <a href="cerrar_caja.php" class="btn-cerrar" onclick="return confirm('¿Cerrar turno de caja hoy?')">❌ CERRAR CAJA</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="stat-card" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                    <form action="../../controllers/VentaController.php" method="POST">
                        <input type="hidden" name="id_socio" id="id_socio_real">
                        <input type="hidden" name="id_caja" value="<?php echo $_SESSION['id_caja']; ?>">
                        
                        <label class="font-negra">MOVIMIENTO</label>
                        <select name="tipo_cobro" id="tipo_cobro" onchange="validarYActualizar()">
                            <option value="PRODUCTO">Venta de Producto</option>
                            <option value="PLAN">Pago de Membresía</option>
                        </select>

                        <div id="div_productos">
                            <label class="font-negra">SELECCIONAR PRODUCTO</label>
                            <select name="id_producto" id="select_prod" onchange="actualizarCalculos()">
                                <?php foreach($productos as $p): 
                                    $p_vista = mostrarMonto($p['precio'], $moneda_usuario, $tasa_dolar);
                                ?>
                                    <option value="<?=$p['id']?>" data-precio-cords="<?=$p['precio']?>"><?=$p['descripcion']?> (<?=$simbolo?> <?=number_format($p_vista, 2)?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <label class="font-negra">CANTIDAD</label>
                            <input type="number" name="cantidad" id="cant_vender" value="1" min="1" onchange="actualizarCalculos()">
                        </div>

                        <div id="div_planes" style="display:none;">
                            <label class="font-negra">MEMBRESÍA</label>
                            <select name="id_plan" id="select_plan" onchange="actualizarCalculos()">
                                <?php foreach($planes as $pl): 
                                    $pl_vista = mostrarMonto($pl['precio'], $moneda_usuario, $tasa_dolar);
                                ?>
                                    <option value="<?=$pl['id']?>" data-precio-cords="<?=$pl['precio']?>"><?=$pl['nombre_plan']?> (<?=$simbolo?> <?=number_format($pl_vista, 2)?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" name="procesar_pago" id="btnPagar" class="btn-pagar">💰 COBRAR AHORA</button>
                    </form>

                    <div class="calculadora-box">
                        <label class="label-calc">PAGO RECIBIDO:</label>
                        <div style="display:flex; gap:5px; margin-bottom:15px;">
                            <input type="number" id="pago_cajero" placeholder="0.00" onkeyup="calcularVuelto()" style="margin:0;">
                            <select id="moneda_pago" onchange="calcularVuelto()" style="width:70px; margin:0;">
                                <option value="COR">C$</option>
                                <option value="USD">$</option>
                            </select>
                        </div>
                        
                        <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align:center;">
                            <span class="label-calc" style="color:#7f8c8d !important;">TOTAL A PAGAR</span>
                            <div id="total_mostrar" style="font-size:1.8rem; font-weight:800; color:#2d3436;"><?=$simbolo?> 0.00</div>
                            <hr style="margin:10px 0; border:0; border-top:1px solid #eee;">
                            <span class="label-calc" style="color:#7f8c8d !important;">SU VUELTO EN <?=$moneda_usuario?></span>
                            <span id="vuelto_txt" class="vuelto-txt"><?=$simbolo?> 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h3 class="font-negra" style="margin:0; font-size: 1rem;">📜 HISTORIAL RECIENTE</h3>
                    <table class="tabla-historial">
                        <thead><tr style="text-align:left; font-size:0.8rem; color:#95a5a6;"><th>TICKET</th><th>CONCEPTO</th><th style="text-align:right;">MONTO</th><th style="text-align:center;">ACCIONES</th></tr></thead>
                        <tbody>
                            <?php
                            $stmtH = $db->prepare("SELECT id, concepto, monto_total FROM ventas WHERE id_caja = ? ORDER BY id DESC LIMIT 5");
                            $stmtH->execute([$_SESSION['id_caja']]);
                            while($v = $stmtH->fetch()): 
                                $v_vista = mostrarMonto($v['monto_total'], $moneda_usuario, $tasa_dolar);
                            ?>
                            <tr>
                                <td style="color:#e67e22;">#<?=$v['id']?></td>
                                <td><?=$v['concepto']?></td>
                                <td style="text-align:right; color:#27ae60;"><?=$simbolo?> <?=number_format($v_vista, 2)?></td>
                                <td style="text-align:center;"><button onclick="reimprimir(<?=$v['id']?>)" style="cursor:pointer; border:1px solid #ddd; background:white; padding:4px 8px; border-radius:4px;">🖨️</button></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    const TASA_DOLAR = <?=$tasa_dolar?>;
    const MONEDA_SESION = "<?=$moneda_usuario?>";
    const SIMBOLO = "<?=$simbolo?>";
    let socioEstatus = "";

    function buscarSocio() {
        let v = document.getElementById('input_busqueda').value;
        if(v.length < 3) return;
        fetch('../../ajax/buscar_socio.php?consulta=' + v).then(r => r.json()).then(data => {
            if(data.status !== 'no_encontrado') {
                document.getElementById('ficha_socio').style.display = 'block';
                document.getElementById('s_nombre').innerText = data.nombre;
                document.getElementById('s_tel').innerText = "Tel: " + data.telefono;
                document.getElementById('id_socio_real').value = data.id;
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
            btn.disabled = true; btn.innerText = "CLIENTE VIGENTE"; btn.style.background = "#bdc3c7";
        } else {
            btn.disabled = false; btn.innerText = "💰 COBRAR AHORA"; btn.style.background = "#27ae60";
        }
        actualizarCalculos();
    }

    function actualizarCalculos() {
        let tipo = document.getElementById('tipo_cobro').value;
        let select = (tipo === 'PRODUCTO') ? document.getElementById('select_prod') : document.getElementById('select_plan');
        
        // Obtenemos siempre el precio base en Cords
        let precioCords = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio-cords'));
        let cant = (tipo === 'PRODUCTO') ? parseInt(document.getElementById('cant_vender').value) || 1 : 1;
        
        let totalCords = precioCords * cant;
        let totalVista = (MONEDA_SESION === 'USD') ? totalCords / TASA_DOLAR : totalCords;
        
        document.getElementById('total_mostrar').innerText = SIMBOLO + " " + totalVista.toFixed(2);
        calcularVuelto();
    }

    function calcularVuelto() {
        // Total en la moneda que se está mostrando
        let totalVista = parseFloat(document.getElementById('total_mostrar').innerText.replace(SIMBOLO + ' ', '')) || 0;
        let pagoRecibido = parseFloat(document.getElementById('pago_cajero').value) || 0;
        let monedaPagoRecibido = document.getElementById('moneda_pago').value;

        let pagoNormalizado = 0;

        // Convertimos el pago recibido a la moneda de la vista para restar
        if (MONEDA_SESION === 'COR') {
            pagoNormalizado = (monedaPagoRecibido === 'USD') ? pagoRecibido * TASA_DOLAR : pagoRecibido;
        } else {
            pagoNormalizado = (monedaPagoRecibido === 'COR') ? pagoRecibido / TASA_DOLAR : pagoRecibido;
        }

        let vuelto = pagoNormalizado - totalVista;
        document.getElementById('vuelto_txt').innerText = SIMBOLO + " " + (vuelto > 0 ? vuelto.toFixed(2) : "0.00");
    }

    function reimprimir(id) { window.open('imprimir_recibo.php?id=' + id, 'Ticket', 'width=400,height=600'); }
    actualizarCalculos();
    </script>
</body>
</html>