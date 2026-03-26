<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { header("Location: ../dashboard.php"); exit(); }
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$tema_actual = $config['tema'] ?? 'default';
$_SESSION['tema'] = $tema_actual;

$monedas = [
    ['iso'=>'NIO', 'nom'=>'Córdoba Nicaragüense', 'sim'=>'C$'],
    ['iso'=>'USD', 'nom'=>'Dólar Estadounidense', 'sim'=>'$'],
    ['iso'=>'EUR', 'nom'=>'Euro', 'sim'=>'€'],
    ['iso'=>'GBP', 'nom'=>'Libra Esterlina', 'sim'=>'£'],
    ['iso'=>'MXN', 'nom'=>'Peso Mexicano', 'sim'=>'$'],
    ['iso'=>'CRC', 'nom'=>'Colón Costarricense', 'sim'=>'₡'],
    ['iso'=>'GTQ', 'nom'=>'Quetzal Guatemalteco', 'sim'=>'Q'],
    ['iso'=>'HNL', 'nom'=>'Lempira Hondureña', 'sim'=>'L'],
    ['iso'=>'SVC', 'nom'=>'Dólar Salvadoreño', 'sim'=>'$'],
    ['iso'=>'COP', 'nom'=>'Peso Colombiano', 'sim'=>'$'],
    ['iso'=>'VES', 'nom'=>'Bolívar Venezolano', 'sim'=>'Bs'],
    ['iso'=>'ARS', 'nom'=>'Peso Argentino', 'sim'=>'$'],
    ['iso'=>'BRL', 'nom'=>'Real Brasileño', 'sim'=>'R$'],
    ['iso'=>'JPY', 'nom'=>'Yen Japonés', 'sim'=>'¥'],
    ['iso'=>'CNY', 'nom'=>'Yuan Chino', 'sim'=>'¥'],
    ['iso'=>'KRW', 'nom'=>'Won Coreano', 'sim'=>'₩'],
    ['iso'=>'INR', 'nom'=>'Rupia India', 'sim'=>'₹'],
    ['iso'=>'CAD', 'nom'=>'Dólar Canadiense', 'sim'=>'$'],
    ['iso'=>'AUD', 'nom'=>'Dólar Australiano', 'sim'=>'$'],
    ['iso'=>'CHF', 'nom'=>'Franco Suizo', 'sim'=>'Fr'],
    ['iso'=>'PEN', 'nom'=>'Sol Peruano', 'sim'=>'S/'],
    ['iso'=>'CLP', 'nom'=>'Peso Chileno', 'sim'=>'$']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .card { background: var(--bg-card); padding: 30px; border-radius: 12px; max-width: 800px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid var(--border-color); color: var(--text-main); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .f-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        label { font-weight: bold; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 1px solid var(--input-border); border-radius: 6px; background: var(--input-bg); color: var(--input-text); }
        .bcn { background: var(--bg-card); padding: 15px; border-radius: 8px; border-left: 4px solid #2980b9; grid-column: span 2; border: 1px solid var(--border-color); }
        body { background-color: var(--bg-body); color: var(--text-main); }
        header { background: var(--header-bg); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        
        /* ESTILO ACTUALIZADO PARA REDONDEAR EL LOGO */
        #pre { 
            height: 80px; /* Un poco más grande para que se aprecie */
            width: 80px;  /* Ancho fijo para que sea un círculo perfecto */
            margin-top: 10px; 
            object-fit: cover; /* Importante para que no se estire la imagen */
            border-radius: 50%; /* Esto hace el círculo perfectp */
            border: 2px solid #ddd; /* Un borde sutil opcional */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra suave opcional */
        }
    </style>
</head>
<body class="<?php echo ($tema_actual !== 'default') ? 'tema-' . $tema_actual : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-cogs"></i> Configuración General</h2></div>
        <a href="../dashboard.php" class="btn-volver gris">← Volver</a>
    </header>

    <div class="dashboard-wrapper">
        <?php if(isset($_GET['msj'])) echo "<p style='text-align:center; color:green;'><b>¡Datos actualizados con éxito!</b></p>"; ?>
        
        <div class="card">
            <form action="../../controllers/ConfigController.php" method="POST" enctype="multipart/form-data">
                <div class="grid">
                    <div class="f-group" style="grid-column: span 2;">
                        <label>NOMBRE DEL GIMNASIO</label>
                        <input type="text" name="nombre_gym" value="<?php echo $config['nombre_gym']; ?>" required>
                    </div>
                    
                    <div class="f-group">
                        <label>TELÉFONO</label>
                        <input type="text" name="telefono_gym" value="<?php echo $config['telefono_gym']; ?>">
                    </div>

                    <div class="f-group">
                        <label>LOGO DEL GIMNASIO</label>
                        <input type="file" name="logo_gym" accept="image/*" onchange="document.getElementById('pre').src = window.URL.createObjectURL(this.files[0])">
                        <img id="pre" src="../../public/img/<?php echo $config['logo_ruta']; ?>" alt="Previsualización del logo">
                    </div>

                    <div class="f-group" style="grid-column: span 2;">
                        <label>DIRECCIÓN</label>
                        <textarea name="direccion_gym" rows="2"><?php echo $config['direccion_gym']; ?></textarea>
                    </div>

                    <div class="f-group">
                        <label>MONEDA PRINCIPAL</label>
                        <select name="moneda_iso" id="m_sel" onchange="updateM()">
                            <?php foreach($monedas as $m): ?>
                                <option value="<?php echo $m['iso']; ?>" data-nom="<?php echo $m['nom']; ?>" data-sim="<?php echo $m['sim']; ?>" <?php if($config['moneda_iso']==$m['iso']) echo 'selected'; ?>>
                                    <?php echo $m['iso']." - ".$m['nom']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bcn">
                        <label style="color:#2980b9;">TASA DE CAMBIO BCN (1 USD a NIO)</label>
                        <input type="number" step="0.0001" name="tipo_cambio_bcn" value="<?php echo $config['tipo_cambio_bcn']; ?>" style="width:100%; font-size:1.2rem; font-weight:bold; color:#2980b9;">
                    </div>

                    <div class="f-group" style="grid-column: span 2;">
                        <label>APARIENCIA - TEMA</label>
                        <div style="display:flex; gap:15px; margin-top:10px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="tema" value="default" <?php echo ($config['tema'] ?? 'default') == 'default' ? 'checked' : ''; ?>>
                                <span style="width:30px; height:30px; background:#f4f4f4; border:2px solid #ddd; border-radius:6px; display:inline-block;"></span>
                                <span>Claro</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="tema" value="oscuro" <?php echo ($config['tema'] ?? '') == 'oscuro' ? 'checked' : ''; ?>>
                                <span style="width:30px; height:30px; background:#1a1a1a; border:2px solid #333; border-radius:6px; display:inline-block;"></span>
                                <span>Oscuro</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="radio" name="tema" value="darkblue" <?php echo ($config['tema'] ?? '') == 'darkblue' ? 'checked' : ''; ?>>
                                <span style="width:30px; height:30px; background:#0d1b2a; border:2px solid #1b263b; border-radius:6px; display:inline-block;"></span>
                                <span>Dark Blue</span>
                            </label>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="moneda_nombre" id="m_nom" value="<?php echo $config['moneda_nombre']; ?>">
                <input type="hidden" name="moneda_simbolo" id="m_sim" value="<?php echo $config['moneda_simbolo']; ?>">

                <button type="submit" name="btn_save_config" class="btn-accion" style="width:100%; margin-top:20px; border:none; cursor:pointer;">💾 GUARDAR CONFIGURACIÓN</button>
            </form>
        </div>
    </div>
    <script>
        function updateM(){
            const s = document.getElementById('m_sel');
            const o = s.options[s.selectedIndex];
            document.getElementById('m_nom').value = o.getAttribute('data-nom');
            document.getElementById('m_sim').value = o.getAttribute('data-sim');
        }
    </script>
</body>
</html>