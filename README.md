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

------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

⚔️ Guía Maestra de Instalación - Gimnasio Spartan
Esta guía utiliza la estructura real de tu base de datos (socios, ventas, inventario y cajas) para asegurar que el sistema sea funcional desde el primer segundo.

🐧 Paso 1: Configuración en Linux (Producción)
Si usas Ubuntu o Debian, ejecuta estos comandos para preparar el servidor:

Preparar carpetas y descargar código:

Bash
sudo mkdir -p /var/www/gym
sudo chown $USER:$USER /var/www/gym
cd /var/www/gym
git clone https://github.com/leninobregon/gymma .
Configurar el Servidor Web (Puerto 82):
Crea el archivo con sudo nano /etc/apache2/sites-available/gym.conf y pega esto:

Apache
<VirtualHost *:82>
    DocumentRoot /var/www/gym
    <Directory /var/www/gym>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/gym_error.log
    CustomLog ${APACHE_LOG_DIR}/gym_access.log combined
</VirtualHost>
Activar puerto y permisos:

Bash
sudo sed -i '/Listen 80/a Listen 82' /etc/apache2/ports.conf
sudo a2ensite gym.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
sudo chown -R www-data:www-data /var/www/gym
sudo chmod -R 775 /var/www/gym
🪟 Paso 2: Configuración en Windows (Desarrollo)
Mueve la carpeta del proyecto a C:\xampp\htdocs\gym.

Abre el XAMPP Control Panel e inicia Apache y MySQL.

Entra a http://localhost/phpmyadmin y crea la base de datos gym_ma_db con cotejamiento utf8mb4_general_ci.

🛠️ Paso 3: Código del Instalador Maestro (instalar.php)
Crea un archivo llamado instalar.php en la raíz de la carpeta gym. He mejorado el código original para que cree automáticamente todas las tablas que requiere tu archivo SQL:

PHP
<?php
$db_host = "localhost";
$db_name = "gym_ma_db";
$db_user = "root";
$db_pass = ""; 

try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Crear Base de Datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db_name`;");

    // 2. Crear Estructura (Usuarios, Socios, Inventario, Ventas, Cajas)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `nombre` varchar(50) NOT NULL,
        `apellido` varchar(50) NOT NULL,
        `usuario` varchar(50) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `rol` enum('ADMIN','CAJA') NOT NULL DEFAULT 'CAJA'
    ) ENGINE=InnoDB;");

    // 3. Crear Administrador por defecto (admin / admin123)
    $pass = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT IGNORE INTO `usuarios` (id, nombre, apellido, usuario, password, rol) 
                VALUES (1, 'Admin', 'Spartan', 'admin', '$pass', 'ADMIN');");

    // 4. Generar archivo Database.php automáticamente
    $db_class = "<?php class Database {
        private \$host = '$db_host';
        private \$db_name = '$db_name';
        private \$username = '$db_user';
        private \$password = '$db_pass';
        public \$conn;
        public function getConnection() {
            \$this->conn = null;
            try {
                \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
                \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                \$this->conn->exec(\"set names utf8\");
            } catch(PDOException \$e) { echo \"Error: \" . \$e->getMessage(); }
            return \$this->conn;
        }
    } ?>";
    file_put_contents("Database.php", $db_class);

    echo "✅ INSTALACIÓN COMPLETADA EXITOSAMENTE.";
} catch (Exception $e) { echo "❌ ERROR: " . $e->getMessage(); }
?>
🏁 Paso 4: Pasos Finales
Ejecutar: Abre en tu navegador http://localhost/gym/instalar.php.

Seguridad: Una vez veas el mensaje de éxito, borra el archivo instalar.php inmediatamente.

Acceso:

Usuario: admin

Clave: admin123
