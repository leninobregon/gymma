<header>
    <div class="logo">
        <img src="../public/img/logo.png" alt="Logo" class="logo-circular">
    </div>
    <div class="user-info">
        <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? '#' : '../dashboard.php'; ?>" style="color:white; text-decoration:none; margin-right:15px;">🏠 Inicio</a>
        <span>Hola, <strong><?php echo $_SESSION['usuario']; ?></strong></span>
        <a href="../../controllers/AuthController.php?action=logout" style="margin-left:15px; color:#ff7675; text-decoration:none; font-weight:bold;">Cerrar Sesión</a>
    </div>
</header>