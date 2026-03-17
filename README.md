🏋️‍♂️ GYM MA: Sistema de Gestión para Gimnasios
GYM MA es una solución integral y ligera diseñada para la administración eficiente de centros deportivos. Centraliza el control de socios, planes, inventario y finanzas en una interfaz intuitiva y segura, optimizada para entornos locales.


🌟 Características Principales
🗄️ Arquitectura de Datos Robusta: Base de datos relacional con cifrado BCRYPT para contraseñas y prevención de Inyecciones SQL mediante PDO.

💰 Control Financiero Total: Ciclo de caja obligatorio (Apertura -> POS -> Cierre) con soporte multimoneda (COR/USD) y registro de tasa de cambio histórica para reportes precisos.

👥 Gestión de Membresías: Seguimiento dinámico de estados (Activo, Vencido, Próximo a vencer) con alertas visuales y expedientes de salud.

📦 Control de Inventario: Gestión de suplementos y productos con alertas automáticas de stock bajo (umbral < 5 unidades).

📊 Dashboard Administrativo: Gráficas de ingresos (Chart.js), historial de cajas para auditoría y gestión de planes.

🖨️ Tickets Térmicos: Generación de recibos de venta optimizados para impresoras de 80mm.

🛠️ Tecnologías Utilizadas
Backend: PHP 7.4+ (Programación Orientada a Objetos).

Base de Datos: MySQL / MariaDB.

Frontend: HTML5, CSS3 nativo, JavaScript (AJAX para búsquedas en tiempo real).

Librerías: Chart.js para visualización de datos financieros.

🚀 Instalación Rápida
Clonar/Copiar el proyecto: Coloca la carpeta del proyecto en la ruta: C:\xampp\htdocs\gym_ma.

Preparar el Servidor: Inicia Apache y MySQL desde el Panel de Control de XAMPP.

Ejecutar el Instalador: Visita http://localhost/gym_ma/instalar.php en tu navegador. El script creará automáticamente la base de datos gym_ma_db y todas sus tablas.

[!WARNING]
SEGURIDAD: Una vez finalizada la instalación, elimina el archivo instalar.php de tu servidor para evitar reinicios accidentales de la base de datos.

🔑 Acceso Inicial
Usuario: admin

Contraseña: admin123

📋 Flujo Operativo (Guía de Usuario)
1. Inicio de Jornada
El cajero debe realizar la Apertura de Caja declarando el monto inicial. El acceso al Punto de Venta permanece bloqueado hasta que se complete este paso.

2. Ventas y Cobros
Planes: Busca al socio por nombre o cédula. El sistema calcula automáticamente la nueva fecha de vigencia.

Productos: Selecciona artículos del inventario y la cantidad; el stock se descuenta en tiempo real.

Multimoneda: Permite pagos en Córdobas o Dólares, calculando el vuelto exacto según la tasa del BCN configurada.

3. Cierre de Turno
Al finalizar, el cajero ingresa el Monto Real físico. El sistema compara esto con el Monto Esperado (Apertura + Ventas) y registra cualquier faltante o sobrante para auditoría.

📊 Estructura de la Base de Datos
usuarios: Roles (ADMIN/CAJA) y acceso.

socios: Datos personales, médicos y contactos de emergencia.

ventas: Registro histórico detallado con tasa de cambio fija al momento de la venta.

cajas: Control de turnos y flujos de efectivo.

inventario: Stock de productos y alertas.

📄 Licencia
Este proyecto está bajo la Licencia MIT.

Copyright (c) 2026 GYM MA DB

Se concede permiso por la presente, de forma gratuita, a cualquier persona que obtenga una copia de este software y de los archivos de documentación asociados, para utilizar el software sin restricción, incluyendo sin limitación los derechos a usar, copiar, modificar, fusionar, publicar, distribuir, sublicenciar y/o vender copias del Software, sujeto a que se incluya el aviso de copyright anterior en todas las copias o partes sustanciales del mismo.

✉️ Soporte y Respaldo
Backups: Utiliza la opción "Respaldar Base de Datos" en el panel de administración regularmente.

Soporte: Para consultas técnicas o personalizaciones, contacta al administrador del sistema.

<img width="1259" height="827" alt="image" src="https://github.com/user-attachments/assets/63bbb7ef-edc1-46ee-81ef-ec4a82591789" />

<img width="1264" height="723" alt="image" src="https://github.com/user-attachments/assets/753a70e7-e68d-4779-915e-fb0256161eb4" />

----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

📄 Guía de Despliegue: Gimnasio Spartan en Apache
1. Preparación del Entorno (Directorio y Git)
Utilizaremos /var/www/gym como la ruta oficial del proyecto.

Bash
# 1. Crear la carpeta y dar propiedad a tu usuario para clonar
sudo mkdir -p /var/www/gym
sudo chown $USER:$USER /var/www/gym
cd /var/www/gym

# 2. Clonar el repositorio (el punto indica que se clona en la carpeta actual)
git clone https://github.com/leninobregon/gymma .
2. Configuración de Apache (Virtual Host)
Configuraremos el puerto 82 para que no choque con el puerto 80 estándar.

Bash
# 1. Crear el archivo de configuración
sudo nano /etc/apache2/sites-available/gym.conf
Pega este contenido exacto:

Apache
<VirtualHost *:82>
    ServerAdmin admin@localhost
    DocumentRoot /var/www/gym
    <Directory /var/www/gym>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/gym_error.log
    CustomLog ${APACHE_LOG_DIR}/gym_access.log combined
</VirtualHost>
Habilitar el sitio y el puerto:

Bash
# 2. Crear el enlace simbólico
sudo ln -s /etc/apache2/sites-available/gym.conf /etc/apache2/sites-enabled/

# 3. Configurar Apache para que escuche el puerto 82
sudo sed -i '/Listen 80/a Listen 82' /etc/apache2/ports.conf

# 4. Habilitar el módulo rewrite (necesario para muchas apps PHP)
sudo a2enmod rewrite

# 5. Reiniciar Apache
sudo systemctl restart apache2
3. Configuración del Firewall (UFW)
Si no abres el puerto, el navegador te dará "Error de conexión".

Bash
sudo ufw allow 82/tcp
sudo ufw reload
4. Permisos de Archivos (Clave para evitar Error 500)
El servidor web (www-data) debe poder escribir en la carpeta para que el instalador genere el archivo Database.php.

Bash
# Cambiar el dueño al usuario del servidor web
sudo chown -R www-data:www-data /var/www/gym

# Permisos de lectura, escritura y ejecución
sudo chmod -R 775 /var/www/gym
5. Configuración de MySQL/MariaDB (Acceso Root)
Debian bloquea el acceso a root sin contraseña por seguridad. Ejecuta esto para permitir que el instalador trabaje:

Bash
sudo mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY ''; FLUSH PRIVILEGES;"
6. Ejecución del Instalador Maestro
Crea el archivo instalar.php en /var/www/gym/ con el siguiente código optimizado para tu base de datos gym_ma_db:

PHP
<?php
// CONFIGURACIÓN DE TU DB
$host = "localhost";
$db_name = "gym_ma_db";
$user_db = "root";
$pass_db = "";

try {
    $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear Base de Datos y usarla
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db_name`;");

    // Estructura mínima para iniciar sesión
    $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `nombre` varchar(50) NOT NULL,
        `usuario` varchar(50) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA'
    ) ENGINE=InnoDB;");

    // Crear Admin (admin / admin123)
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT IGNORE INTO `usuarios` (id, nombre, usuario, password, rol) 
                VALUES (1, 'Admin Spartan', 'admin', '$hash', 'ADMIN');");

    // ACTUALIZAR TU CLASE DATABASE.PHP AUTOMÁTICAMENTE
    $db_class = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$db_name';
    private \$username = '$user_db';
    private \$password = '$pass_db';
    public \$conn;
    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) { echo \"Error: \" . \$exception->getMessage(); }
        return \$this->conn;
    }
} ?>";
    file_put_contents("Database.php", $db_class);

    echo "✅ INSTALACIÓN EXITOSA. Usuario: admin / Clave: admin123";
} catch (Exception $e) { echo "❌ Error: " . $e->getMessage(); }
?>
7. Paso Final de Seguridad
Una vez que veas el mensaje de éxito en http://tu_ip:82/instalar.php:

Bash
# Borrar el instalador para evitar ataques
sudo rm /var/www/gym/instalar.php
----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
Gimnasio Spartan en XAMPP (Windows) sin errores, seguiremos un proceso similar al de Linux pero adaptado a las rutas y herramientas de Windows.

Aquí tienes la guía definitiva paso a paso para dejarlo funcionando al 100%:

1. Ubicación del Proyecto
En Windows, XAMPP utiliza la carpeta htdocs.

Abre tu explorador de archivos y ve a C:\xampp\htdocs.

Crea una carpeta llamada gym.

Copia todos los archivos de tu proyecto dentro de C:\xampp\htdocs\gym.

2. Configuración de Base de Datos (MySQL)
XAMPP, por defecto, trae el usuario root sin contraseña, lo cual coincide con la configuración de tu archivo Database.php.

Abre el XAMPP Control Panel.

Inicia los módulos Apache y MySQL.

Haz clic en el botón Admin de MySQL (esto abrirá phpMyAdmin).

Crea una base de datos nueva llamada gym_ma_db con el cotejamiento utf8mb4_general_ci.

3. El Script de Instalación Maestro para XAMPP
Crea un archivo llamado instalar.php dentro de C:\xampp\htdocs\gym\ y pega este código. Está diseñado para detectar que estás en Windows y configurar todo automáticamente:

PHP
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración específica para XAMPP
$host = "localhost";
$db_name = "gym_ma_db";
$user_db = "root";
$pass_db = ""; 

try {
    $pdo = new PDO("mysql:host=$host", $user_db, $pass_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Crear Base de Datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db_name`;");
    echo "✅ Base de datos '$db_name' lista.<br>";

    // 2. Crear Tabla de Usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `nombre` varchar(50) NOT NULL,
        `usuario` varchar(50) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA'
    ) ENGINE=InnoDB;");

    // 3. Crear Usuario Administrador (admin / admin123)
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT IGNORE INTO `usuarios` (id, nombre, usuario, password, rol) 
                VALUES (1, 'Administrador XAMPP', 'admin', '$hash', 'ADMIN');");
    echo "✅ Usuario 'admin' creado con clave 'admin123'.<br>";

    // 4. Escribir archivo Database.php automáticamente
    $db_class_content = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$db_name';
    private \$username = '$user_db';
    private \$password = '$pass_db';
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) {
            echo \"Error de conexión: \" . \$exception->getMessage();
        }
        return \$this->conn;
    }
} ?>";
    
    file_put_contents("Database.php", $db_class_content);
    echo "✅ Archivo 'Database.php' configurado para XAMPP.<br>";

    echo "<br><strong style='color:green;'>¡SISTEMA LISTO!</strong><br>";
    echo "<a href='views/login.php'>Ir al Login</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
4. Ejecución
Abre tu navegador.

Ingresa a la dirección: http://localhost/gym/instalar.php.

Una vez que termine, borra el archivo instalar.php de tu carpeta htdocs/gym.

Diferencias clave con Linux:
Permisos: En Windows/XAMPP no necesitas ejecutar chown o chmod; el servidor tiene acceso total a los archivos por defecto.

Virtual Hosts: No es obligatorio crear un archivo .conf en sites-available. Puedes acceder simplemente usando localhost/gym/.

Puerto: XAMPP usa el puerto 80 por defecto. No necesitas abrir puertos en el firewall a menos que quieras que alguien fuera de tu red local entre a tu PC.

Resumen de Acceso:
URL: http://localhost/gym/

Login: admin

Clave: admin123
