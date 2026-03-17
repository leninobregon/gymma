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

Perfecto, Lenin. Te dejo un **ejemplo de sección de instalación en Debian para tu README.md de GitHub**, bien estructurado y listo para copiar/pegar:

```markdown
# 📖 Guía de Instalación en Debian

## 1. 🔧 Preparar el entorno
Actualiza tu sistema:
```bash
sudo apt update && sudo apt upgrade -y
```

Instala Apache, PHP y MariaDB:
```bash
sudo apt install apache2 mariadb-server php php-mysql php-cli php-zip php-curl php-xml php-mbstring unzip git -y
```

Habilita y arranca servicios:
```bash
sudo systemctl enable apache2 mariadb
sudo systemctl start apache2 mariadb
```

---

## 2. 🗄️ Configurar la base de datos
Accede a MariaDB:
```bash
sudo mysql -u root -p
```

Crea base de datos y usuario:
```sql
CREATE DATABASE gym_ma_db;
CREATE USER 'gymuser'@'localhost' IDENTIFIED BY 'tu_password_segura';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gymuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. 📂 Descargar el proyecto
Clona el repositorio en el directorio web:
```bash
cd /var/www/html
sudo git clone https://github.com/leninobregon/gymma.git gym_ma
```

Asegura permisos:
```bash
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo chmod -R 755 /var/www/html/gym_ma
```

---

## 4. ⚙️ Configurar Apache
Crea un VirtualHost:
```bash
sudo nano /etc/apache2/sites-available/gym_ma.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/gym_ma
    ServerName localhost

    <Directory /var/www/html/gym_ma>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gym_ma_error.log
    CustomLog ${APACHE_LOG_DIR}/gym_ma_access.log combined
</VirtualHost>
```

Habilita sitio y mod_rewrite:
```bash
sudo a2ensite gym_ma.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## 5. 🚀 Instalación inicial
Accede en el navegador:
```
http://localhost/gym_ma/instalar.php
```

- Se crearán las tablas automáticamente.  
- Usuario inicial: **admin**  
- Contraseña: **admin123**  

⚠️ **IMPORTANTE:** elimina `instalar.php` después de la instalación:
```bash
sudo rm /var/www/html/gym_ma/instalar.php
```

---

## 6. 🔒 Seguridad y mantenimiento
- Cambia la contraseña del usuario `admin` inmediatamente.  
- Realiza respaldos periódicos con la opción integrada de “Respaldar Base de Datos”.  
- Mantén actualizado Apache, PHP y MariaDB:
```bash
sudo apt upgrade -y
```

---

Con esta guía, tu sistema queda listo para funcionar en **Debian** de forma segura y profesional.
```

---

👉 Te recomiendo añadir esta sección al final de tu README, justo después de la descripción del proyecto y sus características.  

¿Quieres que te prepare también una **versión resumida en inglés** para que tu repositorio sea más accesible a usuarios internacionales?
