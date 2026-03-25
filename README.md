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
| **JavaScript** | Interactividad (AJAX) |
| **Chart.js** | Visualización de datos |
| **Font Awesome 6** | Iconos profesionales |

---

## 🚀 Instalación Rápida

### En XAMPP (Windows)

1. **Preparar el Servidor**
   - Inicia Apache y MySQL desde el Panel de Control de XAMPP

2. **Instalar el Proyecto**
   - Copia la carpeta `gym_mejoras` en `C:\xampp\htdocs\`

3. **Ejecutar el Instalador**
   ```
   http://localhost/gym_mejoras/instalar.php
   ```
   El script creará automáticamente la base de datos `gym_ma_db` y todas sus tablas.

4. **⚠️ SEGURIDAD**
   > Una vez finalizada la instalación, elimina el archivo `instalar.php` de tu servidor para evitar reinicios accidentales de la base de datos.

### En Linux (Debian/Ubuntu)

```bash
# Instalar dependencias
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 mariadb-server php php-mysql php-cli php-zip php-curl php-xml php-mbstring unzip git -y

# Iniciar servicios
sudo systemctl start apache2 mariadb

# Clonar proyecto
cd /var/www/html
sudo git clone https://github.com/tu-repositorio.git gym_ma
sudo chown -R www-data:www-data /var/www/html/gym_ma
sudo chmod -R 755 /var/www/html/gym_ma

# Instalar
http://localhost/gym_ma/instalar.php
```

---

## 🔑 Acceso Inicial

| Campo | Valor |
|-------|-------|
| **Usuario** | admin |
| **Contraseña** | admin123 |

> ⚠️ **IMPORTANTE:** Cambia la contraseña del usuario admin inmediatamente después del primer inicio de sesión.

---

## 📋 Flujo Operativo

### 1. Inicio de Jornada
El cajero debe realizar la **Apertura de Caja** declarando el monto inicial. El acceso al Punto de Venta permanece bloqueado hasta que se complete este paso.

### 2. Ventas y Cobros
- **Planes:** Busca al socio por nombre o cédula. El sistema calcula automáticamente la nueva fecha de vigencia.
- **Productos:** Selecciona artículos del inventario y la cantidad; el stock se descuenta en tiempo real.
- **Multimoneda:** Permite pagos en Córdobas o Dólares, calculando el vuelto exacto según la tasa del BCN configurada.

### 3. Cierre de Turno
Al finalizar, el cajero ingresa el **Monto Real** físico. El sistema compara esto con el **Monto Esperado** (Apertura + Ventas) y registra cualquier faltante o sobrante para auditoría.

---

## 📊 Reportes Disponibles

1. **Reporte Financiero** - Ventas por período con filtros
2. **Ingresos/Egresos** - Balance, métodos de pago, categorías
3. **Rendimiento Cajeros** - Ventas por empleado
4. **Socios por Vencer** - Alertas de membresías próximas a vencer
5. **Clientes Frecuentes** - Top clientes por número de compras
6. **Inventario** - Stock de productos
7. **Cajas** - Historial de cajas abiertas/cerradas
8. **Egresos** - Registro de gastos del gym
9. **Buscar por ID** - Encontrar ventas específicas

Todos los reportes incluyen **Exportación a Excel**.

---

## 📁 Estructura del Proyecto

```
gym_mejoras/
├── config/
│   ├── Database.php       # Conexión PDO
│   └── AppConfig.php     # Configuración del gym
├── classes/
│   ├── Dashboard.php     # Estadísticas
│   ├── Reporte.php       # Métodos de reportes
│   ├── Socio.php         # Lógica de socios
│   ├── Inventario.php   # Gestión de productos
│   └── Plan.php         # Planes de membresía
├── controllers/
│   ├── AuthController.php
│   ├── VentaController.php
│   ├── SocioController.php
│   ├── CajaController.php
│   └── ExportController.php
├── views/
│   ├── login.php
│   ├── dashboard.php
│   ├── admin/           # Módulos admin
│   ├── caja/            # Punto de venta
│   └── includes/        # Componentes
├── public/
│   ├── css/estilos.css
│   └── img/
├── README.md
└── instalar.php
```

---

## 🗄️ Tablas de la Base de Datos

| Tabla | Descripción |
|-------|-------------|
| `usuarios` | Empleados (ADMIN/CAJA) |
| `socios` | Clientes del gym |
| `ventas` | Transacciones |
| `cajas` | Sesiones de caja |
| `inventario` | Productos |
| `planes` | Membresías |
| `caja_egresos` | Gastos |
| `configuracion` | Ajustes del sistema |

---

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con BCRYPT
- ✅ Consultas preparadas (PDO) contra SQL Injection
- ✅ Sesiones seguras con validación
- ✅ Roles: ADMIN y CAJA
- ✅ Validación de acceso en cada página

---

## 💾 Respaldo y Mantenimiento

- Utiliza la opción **"Respaldar Base de Datos"** en el panel de administración regularmente.
- Mantén actualizado PHP y MySQL/MariaDB.
- Elimina el archivo `instalar.php` después de la instalación.

---

## 📝 Licencia

Este proyecto está bajo la **Licencia MIT**.

Copyright (c) 2026 GYM MA DB

---

## ✉️ Soporte

Para consultas técnicas o personalizaciones, contacta al administrador del sistema.

---

**Versión:** 2.0  
**Desarrollado para:** GYM MA  
**Fecha:** Marzo 2026