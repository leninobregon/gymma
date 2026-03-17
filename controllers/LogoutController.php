<?php
session_start();
session_unset(); // Limpia las variables
session_destroy(); // Destruye la sesión
// Como estamos en /controllers, subimos un nivel para ir a /views
header("Location: ../views/logout.php"); 
exit();
?>