# 🏋️‍♂️ GYM MA: Sistema de Gestión para Gimnasios

GYM MA es una solución integral y ligera diseñada para la administración eficiente de centros deportivos. Centraliza el control de socios, planes, inventario y finanzas en una interfaz intuitiva y segura, optimizada para entornos locales.

---

## 🌟 Características Principales

### 🗄️ Arquitectura de Datos Robusta
- Base de datos relacional con cifrado BCRYPT para contraseñas
- Prevención de Inyecciones SQL mediante PDO

### 💰 Control Financiero Total
- Ciclo de caja obligatorio (Apertura → POS → Cierre)
- Soporte multimoneda (Córdobas NIO / Dólares USD)
- Registro de tasa de cambio histórica para reportes precisos

### 👥 Gestión de Membresías
- Seguimiento dinámico de estados (Activo, Vencido, Próximo a vencer)
- Alertas visuales automáticas
- Expedientes de salud de socios

### 📦 Control de Inventario
- Gestión de suplementos y productos
- Alertas automáticas de stock bajo (umbral < 5 unidades)

### 📊 Dashboard Administrativo
- Gráficas de ingresos (Chart.js)
- Historial de cajas para auditoría
- Métricas en tiempo real

### 🖨️ Tickets y Recibos
- Generación de recibos de venta optimizados
- Reimpresión de tickets

### 🎨 Sistema de Temas
- Default - Colores claros profesionales
- Oscuro - Fondo oscuro moderno
- Darkblue - Tono azul elegante

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Uso |
|------------|-----|
| PHP 7.4+ | Backend (POO, PDO) |
| MySQL / MariaDB | Base de datos |
| HTML5, CSS3 | Frontend |
| Chart.js | Gráficos y visualización |
| Font Awesome | Iconos profesionales |

---

## 🚀 Instalación Rápida

1. **Clonar/Copiar el proyecto**: Coloca la carpeta en `C:\xampp\htdocs\gym_ma`

2. **Preparar el Servidor**: Inicia Apache y MySQL desde el Panel de Control de XAMPP

3. **Ejecutar el Instalador**: Visita `http://localhost/gym_ma/instalar.php`

> ⚠️ **SEGURIDAD**: Una vez finalizada la instalación, elimina el archivo `instalar.php`

---

## 🔑 Credenciales de Acceso

| Campo | Valor |
|-------|-------|
| Usuario | `admin` |
| Contraseña | `admin123` |

---

## 📋 Flujo Operativo

### 1. Inicio de Jornada
El cajero debe realizar la **Apertura de Caja** declarando el monto inicial. El acceso al Punto de Venta permanece bloqueado hasta que se complete este paso.

### 2. Ventas y Cobros
- **Planes**: Busca al socio por nombre o cédula. El sistema calcula automáticamente la nueva fecha de vigencia.
- **Productos**: Selecciona artículos del inventario y la cantidad; el stock se descuenta en tiempo real.
- **Multimoneda**: Permite pagos en Córdobas o Dólares, calculando el vuelto exacto según la tasa del BCN configurada.

### 3. Cierre de Turno
Al finalizar, el cajero ingresa el **Monto Real** físico. El sistema compara esto con el **Monto Esperado** (Apertura + Ventas) y registra cualquier faltante o sobrante para auditoría.

---

## 📊 Estructura de la Base de Datos

| Tabla | Descripción |
|-------|-------------|
| usuarios | Roles (ADMIN/CAJA) y acceso |
| socios | Datos personales, médicos y contactos de emergencia |
| ventas | Registro histórico detallado con tasa de cambio |
| cajas | Control de turnos y flujos de efectivo |
| inventario | Stock de productos y alertas |
| planes | Planes de membresía |
| caja_egresos | Registro de gastos |
| configuracion | Configuración del sistema |

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT.

Copyright (c) 2026 GYM MA DB

Se concede permiso por la presente, de forma gratuita, a cualquier persona que obtenga una copia de este software y de los archivos de documentación asociados, para utilizar el software sin restricción.

---

## ✉️ Soporte y Respaldo
- **Backups**: Utiliza la opción "Respaldar Base de Datos" regularmente
- **Soporte**: Contacta al administrador del sistema

---

## 📖 Guía de Instalación

### 🪟 Windows con XAMPP

1. Descarga e instala XAMPP desde [apachefriends.org](https://www.apachefriends.org)
2. Inicia Apache y MySQL desde el Panel de Control XAMPP
3. Clona o copia el proyecto a `C:\xampp\htdocs\gym_ma`
4. Ejecuta `http://localhost/gym_ma/instalar.php`
5. Elimina `instalar.php` después de instalar

---

### 🐧 Linux (Debian/Ubuntu) con LAMP

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar LAMP
sudo apt install apache2 mariadb-server php php-mysql php-cli php-zip php-curl php-xml php-mbstring unzip git -y

# Habilitar servicios
sudo systemctl enable apache2 mariadb
sudo systemctl start apache2 mariadb
```

```bash
# Configurar MariaDB
sudo mysql -u root -p
```

```sql
CREATE DATABASE gym_ma_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'gymuser'@'localhost' IDENTIFIED BY 'gymuser';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gymuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Actualizar config/database.php con las credenciales
# o ejecutar el instalador: http://tu-servidor/instalar.php
```

---

### 📥 Importar Base de Datos

#### Opción 1: Desde el navegador
Ejecuta el instalador:
```
http://tu-servidor/instalar.php
```

#### Opción 2: Desde terminal

```bash
# Importar SQL
sudo mysql -u root -p gym_ma_db < /var/www/html/gym_ma/db/gym_ma_db.sql

# Verificar tablas
sudo mysql -u root -p -e "USE gym_ma_db; SHOW TABLES;"
```

---

### 📂 Descargar proyecto

```bash
cd /var/www/html
sudo git clone https://github.com/leninobregon/gymma.git gym_ma
```

---

### 🔐 Permisos

```bash
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo chmod -R 755 /var/www/html/gym_ma
sudo chmod 777 /var/www/html/gym_ma
```

---

### ⚙️ Configurar Apache

```bash
sudo nano /etc/apache2/sites-available/gym_ma.conf
```

```apache
<VirtualHost *:80>
    ServerName gym_ma.local
    ServerAlias www.gym_ma.local
    DocumentRoot /var/www/html/gym_ma

    <Directory /var/www/html/gym_ma>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gym_ma_error.log
    CustomLog ${APACHE_LOG_DIR}/gym_ma_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite gym_ma.conf
sudo a2enmod rewrite
sudo systemctl reload apache2

# Si hay errores, usar:
sudo a2ensite gym_ma.conf --force
sudo systemctl restart apache2
```

---

## 🛠️ Solución de Problemas

### 📌 Página por defecto de Apache
Si carga la página por defecto de Debian:

```bash
# Deshabilitar página por defecto
sudo a2dissite 000-default.conf

# Verificar configuración
sudo apache2ctl configtest

# Reiniciar Apache
sudo systemctl restart apache2
```

### 📌 Permisos
```bash
# Permisos correctos
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo chmod -R 755 /var/www/html/gym_ma
sudo chmod 777 /var/www/html/gym_ma
```

### 📌 Ver logs
```bash
# Logs de Apache
sudo tail -f /var/log/apache2/error.log

# Logs de PHP
sudo tail -f /var/log/php*-fpm.log

# Accede al sitio para generar el error y revisa los logs
```

### 📌 HTTP Error 500
Si aparece Error 500:

```bash
# 1. Verificar versión de PHP (importante!)
php -v

# 2. Instalar extensiones PHP según tu versión
# PHP 8.2:
sudo apt install php8.2-mysql php8.2-zip php8.2-curl php8.2-xml php8.2-mbstring -y

# PHP 8.1:
sudo apt install php8.1-mysql php8.1-zip php8.1-curl php8.1-xml php8.1-mbstring -y

# PHP 8.0:
sudo apt install php8.0-mysql php8.0-zip php8.0-curl php8.0-xml php8.0-mbstring -y

# PHP 7.4:
sudo apt install php7.4-mysql php7.4-zip php7.4-curl php7.4-xml php7.4-mbstring -y

# 3. Reiniciar Apache
sudo systemctl restart apache2

# 4. Verificar que funcionan las extensiones
php -m | grep -E "pdo|mysql|zip|curl"

# 5. Si siguen los errores, ver logs específicos
sudo tail -50 /var/log/apache2/error.log | grep -i "auth\|php\|fatal"
```

### ⚠️ Error 500 - Base de Datos
Si sale Error 500, puede que la base de datos no existe:

```bash
# Verificar MySQL/MariaDB
sudo systemctl status mariadb
sudo systemctl status mysql

# Si no está corriendo, iniciar
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

```bash
# Crear base de datos
sudo mysql -u root -p
```

```sql
CREATE DATABASE gym_ma_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
EXIT;
```

```bash
# Importar tablas (opcional - el instalador lo hace automáticamente)
sudo mysql -u root -p gym_ma_db < /var/www/html/gym_ma/db/gym_ma_db.sql

# O ejecutar el instalador en el navegador:
# http://tu-servidor/instalar.php
```

### 📌 Credenciales de la Base de Datos
El archivo `config/Database.php` usa:
- Usuario: `root`
- Contraseña: (vacía)
- Base de datos: `gym_ma_db`

Si necesitas un usuario específico:

```bash
sudo mysql -u root -p
```

```sql
CREATE USER 'gymuser'@'localhost' IDENTIFIED BY 'gymuser';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gymuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Luego edita `config/Database.php`:

```php
private $username = "gymuser";
private $password = "gymuser";
```

---

### 📌 Ver logs de errores específicos

```bash
sudo tail -100 /var/log/apache2/error.log | grep -i error
```

### 📌 Habilitar mostrar errores temporalmente

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Cambiar: `display_errors = Off`
Por: `display_errors = On`

```bash
# Reiniciar Apache
sudo systemctl restart apache2

# Ver logs ahora
sudo tail -f /var/log/apache2/error.log
```

### 📌 Permisos correctos

```bash
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo find /var/www/html/gym_ma -type f -exec chmod 644 {} \;
sudo find /var/www/html/gym_ma -type d -exec chmod 755 {} \;
```

---

## 🌐 Linux (Debian/Ubuntu) con LEMP

```bash
# Instalar LEMP
sudo apt install nginx mariadb-server php-fpm php-mysql -y

# Configurar Nginx
sudo nano /etc/nginx/sites-available/gym_ma
```

```nginx
server {
    listen 80;
    server_name gym_ma.local;
    root /var/www/html/gym_ma;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Habilitar sitio
sudo ln -s /etc/nginx/sites-available/gym_ma /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```


<img width="626" height="736" alt="image" src="https://github.com/user-attachments/assets/b6b9f961-bad5-4a08-a881-5059c17974bc" />

<img width="1224" height="834" alt="image" src="https://github.com/user-attachments/assets/b6ae3674-aa9f-4416-9a55-c615269dbcf3" />


