🏋️‍♂️ GYM MA: Sistema de Gestión para Gimnasios

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
- **Default** - Colores claros profesionales
- **Oscuro** - Fondo oscuro moderno  
- **Darkblue** - Tono azul elegante

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Uso |
|------------|-----|
| **PHP 7.4+** | Backend (POO, PDO) |
| **MySQL / MariaDB** | Base de datos |
| **HTML5, CSS3** | Frontend |
| **Chart.js** | Gráficos y visualización |
| **Font Awesome** | Iconos profesionales |

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
| `usuarios` | Roles (ADMIN/CAJA) y acceso |
| `socios` | Datos personales, médicos y contactos de emergencia |
| `ventas` | Registro histórico detallado con tasa de cambio |
| `cajas` | Control de turnos y flujos de efectivo |
| `inventario` | Stock de productos y alertas |
| `planes` | Planes de membresía |
| `caja_egresos` | Registro de gastos |
| `configuracion` | Configuración del sistema |

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

### 🐧 Linux (Debian/Ubuntu) con LAMP

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar LAMP
sudo apt install apache2 mariadb-server php php-mysql php-cli php-zip php-curl php-xml php-mbstring unzip git -y

# Habilitar servicios
sudo systemctl enable apache2 mariadb
sudo systemctl start apache2 mariadb

# Configurar MariaDB
sudo mysql -u root -p
CREATE DATABASE gym_ma_db;
CREATE USER 'gymuser'@'localhost' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gymuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Descargar proyecto
cd /var/www/html
sudo git clone https://github.com/leninobregon/gymma.git gym_ma

# Permisos
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo chmod -R 755 /var/www/html/gym_ma
sudo chmod 777 /var/www/html/gym_ma/tmp

# Configurar Apache
sudo nano /etc/apache2/sites-available/gym_ma.conf

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

sudo a2ensite gym_ma.conf
sudo a2enmod rewrite
sudo systemctl reload apache2

# Si hay errores, usar:
sudo a2ensite gym_ma.conf --force
sudo systemctl restart apache2
```

### 🌐 Linux (Debian/Ubuntu) con LEMP

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

---

<img width="980" height="733" alt="image" src="https://github.com/user-attachments/assets/4e47904d-314c-4da2-80d1-16c3070960df" />


<img width="1177" height="839" alt="image" src="https://github.com/user-attachments/assets/9f87e014-e7f6-4258-b01d-fe2829f8293d" />
