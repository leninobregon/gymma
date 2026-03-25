# GUÍA DE INSTALACIÓN - SISTEMA GYM MA DB

## Requisitos Previos

- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: mysqli, pdo, curl, mbstring, json, zip

---

## Credenciales de Acceso

| Campo | Valor |
|-------|-------|
| Usuario | `admin` |
| Contraseña | `admin123` |

---

## Instalación con XAMPP (Windows)

### Paso 1: Descargar e instalar XAMPP
1. Descarga XAMPP desde: https://www.apachefriends.org/
2. Instala XAMPP (selecciona Apache y MySQL)
3. Inicia los servicios de Apache y MySQL desde el Panel de Control XAMPP

### Paso 2: Configurar la base de datos
1. Abre phpMyAdmin: http://localhost/phpmyadmin
2. Crea una nueva base de datos llamada `gym_ma_db`
3. Importa el archivo `db/gym_ma_db.sql`

### Paso 3: Deploy de la aplicación
1. Copia la carpeta del proyecto a `C:\xampp\htdocs\gym_mejoras`
2. Edita el archivo `config/AppConfig.php`:
   ```php
   const DB_HOST = 'localhost';
   const DB_NAME = 'gym_ma_db';
   const DB_USER = 'root';
   const DB_PASS = '';
   ```

### Paso 4: Acceder al sistema
- URL: http://localhost/gym_mejoras
- Ver credenciales en la sección "Credenciales de Acceso"

---

## Instalación con LAMP (Linux/Ubuntu)

### Paso 1: Instalar LAMP
```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
sudo apt install php-curl php-mbstring php-zip php-json
```

### Paso 2: Iniciar servicios
```bash
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

### Paso 3: Configurar MySQL
```bash
sudo mysql
CREATE DATABASE gym_ma_db;
CREATE USER 'gym_ma_db'@'localhost' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gym_ma_db'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Paso 4: Deploy de la aplicación
```bash
sudo cp -r gym_mejoras /var/www/html/
sudo chown -R www-data:www-data /var/www/html/gym_mejoras
sudo chmod -R 755 /var/www/html/gym_mejoras
```

### Paso 5: Configurar conexión
Edita `config/AppConfig.php`:
```php
const DB_HOST = 'localhost';
const DB_NAME = 'gym_ma_db';
const DB_USER = 'gym_ma_db';
const DB_PASS = 'tu_password';
```

### Paso 6: Permisos de carpetas
```bash
sudo chmod 777 /var/www/html/gym_mejoras/tmp
sudo chmod 777 /var/www/html/gym_mejoras/public/uploads
```

### Paso 7: Acceder al sistema
- URL: http://localhost/gym_mejoras
- Ver credenciales en la sección "Credenciales de Acceso"

---

## Instalación con LEMP (Linux/Ubuntu con Nginx)

### Paso 1: Instalar LEMP
```bash
sudo apt update
sudo apt install nginx mysql-server php-fpm php-mysql
sudo apt install php-curl php-mbstring php-zip php-json
```

### Paso 2: Configurar PHP-FPM
```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
# Cambiar: user = www-data, group = www-data
sudo systemctl restart php-fpm
```

### Paso 3: Configurar MySQL
```bash
sudo mysql
CREATE DATABASE gym_ma_db;
CREATE USER 'gym_ma_db'@'localhost' IDENTIFIED BY 'tu_password';
GRANT ALL PRIVILEGES ON gym_ma_db.* TO 'gym_ma_db'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Paso 4: Configurar Nginx
```bash
sudo nano /etc/nginx/sites-available/gym_ma_db
```

Contenido del archivo:
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/gym_mejoras;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### Paso 5: Activar sitio
```bash
sudo ln -s /etc/nginx/sites-available/gym_ma_db /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Paso 6: Deploy de la aplicación
```bash
sudo cp -r gym_mejoras /var/www/html/
sudo chown -R www-data:www-data /var/www/html/gym_mejoras
sudo chmod -R 755 /var/www/html/gym_mejoras
sudo chmod 777 /var/www/html/gym_mejoras/tmp
sudo chmod 777 /var/www/html/gym_mejoras/public/uploads
```

### Paso 7: Configurar conexión
Edita `config/AppConfig.php`:
```php
const DB_HOST = 'localhost';
const DB_NAME = 'gym_ma_db';
const DB_USER = 'gym_ma_db';
const DB_PASS = 'tu_password';
```

### Paso 8: Acceder al sistema
- URL: http://localhost
- Ver credenciales en la sección "Credenciales de Acceso"

---

## Primeros Pasos después de la Instalación

1. **Cambiar contraseña del administrador**
   - Ve a Configuración > Usuarios
   - Cambia la contraseña de 'admin123' a una segura

2. **Configurar el sistema**
   - Ve a Configuración > General
   - Configura el nombre del gym_ma_db y otros datos

3. **Crear planes de membresía**
   - Ve a Planes y crea los planes disponibles

4. **Agregar inventario inicial**
   - Ve a Inventario y registra productos

5. **Registrar socios**
   - Ve a Socios y registra los primeros clientes

---

## Solución de Problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté ejecutándose
- Verifica las credenciales en `config/AppConfig.php`
- Asegúrate de que la base de datos exista

### Error de permisos
- Verifica que las carpetas `tmp` y `public/uploads` tengan permisos de escritura
- En Linux: `chmod 777` o `chown www-data:www-data`

### Error 403 Forbidden
- Verifica los permisos de archivos
- Asegúrate de que `.htaccess` esté permitido (Apache)

### Error 404 en rutas
- Verifica la configuración de `mod_rewrite` (Apache)
- Verifica la configuración de Nginx

---

## Notas de Seguridad

1. Cambia la contraseña por defecto inmediatamente
2. En producción, usa HTTPS
3. Mantén PHP y las extensiones actualizadas
4. Haz backups regulares de la base de datos
5. No expongas archivos de configuración públicamente