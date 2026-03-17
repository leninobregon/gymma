<?php
session_start();
session_unset();
session_destroy();

// Simplemente login.php porque ya estás en la carpeta views
header("Location: login.php"); 
exit();