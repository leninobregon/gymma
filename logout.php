<?php
session_start();
session_unset();
session_destroy();
// Redirige al login que está en la carpeta views
header("Location: views/login.php"); 
exit();