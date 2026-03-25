<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_egreso'])) {
    $desc = $_POST['descripcion'] ?? '';
    $monto = floatval($_POST['monto'] ?? 0);
    $categoria = $_POST['categoria'] ?? 'GENERAL';
    
    if ($desc && $monto > 0) {
        $stmt = $db->prepare("INSERT INTO caja_egresos (descripcion, monto_salida, id_usuario, categoria) VALUES (?, ?, ?, ?)");
        $stmt->execute([$desc, $monto, $_SESSION['user_id'], $categoria]);
        $mensaje = "✅ Egreso registrado correctamente";
    } else {
        $mensaje = "❌ Error: Complete todos los campos";
    }
}

$egresos = $db->query("SELECT e.*, u.usuario FROM caja_egresos e JOIN usuarios u ON e.id_usuario = u.id ORDER BY e.fecha_egreso DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$totalEgresos = array_sum(array_column($egresos, 'monto_salida'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Egresos - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        :root { --primary: #27ae60; --danger: #e74c3c; --warning: #f39c12; --text-main: #333; --text-muted: #666; --bg-card: #fff; --border-color: #ddd; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #aaa; --bg-card: #1e1e1e; --border-color: #333; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-card: #1b263b; --border-color: #334155; }
        body { background: var(--bg-card); color: var(--text-main); font-family: 'Segoe UI', sans-serif; }
        .form-egreso { background: var(--bg-card); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); margin-bottom: 20px; }
        .form-egreso input, .form-egreso select { padding: 10px; border-radius: 5px; border: 1px solid var(--border-color); margin-right: 10px; }
        .tabla-egresos { width: 100%; border-collapse: collapse; }
        .tabla-egresos th { background: var(--primary); color: white; padding: 12px; text-align: left; }
        .tabla-egresos td { padding: 10px; border-bottom: 1px solid var(--border-color); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2>📤 Registro de Egresos</h2></div>
        <div style="display:flex; gap:10px;">
            <a href="reportes.php" class="btn-volver gris">← Reportes</a>
            <a href="../dashboard.php" class="btn-volver">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <?php if (isset($mensaje)): ?>
            <div style="padding: 10px; border-radius: 5px; margin-bottom: 15px; background: rgba(39, 174, 96, 0.1); color: var(--primary);"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="form-egreso">
            <h3 style="margin-top:0;">➕ Nuevo Egreso</h3>
            <form method="POST">
                <input type="text" name="descripcion" placeholder="Descripción del gasto" required style="width: 300px;">
                <input type="number" step="0.01" name="monto" placeholder="Monto (C$)" required style="width: 120px;">
                <select name="categoria">
                    <option value="GENERAL">General</option>
                    <option value="SERVICIOS">Servicios</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="INSUMOS">Insumos</option>
                    <option value="NOMINA">Nómina</option>
                    <option value="OTROS">Otros</option>
                </select>
                <button type="submit" name="registrar_egreso" class="btn-volver" style="background: var(--danger);">💾 Registrar</button>
            </form>
        </div>

        <div class="stat-card">
            <h3><i class="fas fa-file-invoice-dollar"></i> Total Egresos: C$ <?php echo number_format($totalEgresos, 2); ?></h3>
            <table class="tabla-egresos">
                <thead>
                    <tr><th>Fecha</th><th>Descripción</th><th>Categoría</th><th>Usuario</th><th>Monto</th></tr>
                </thead>
                <tbody>
                    <?php foreach($egresos as $e): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($e['fecha_egreso'])); ?></td>
                        <td><?php echo htmlspecialchars($e['descripcion']); ?></td>
                        <td><?php echo $e['categoria']; ?></td>
                        <td><?php echo $e['usuario']; ?></td>
                        <td style="color: var(--danger); font-weight: bold;">C$ <?php echo number_format($e['monto_salida'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>