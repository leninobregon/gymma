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

Ejecutar el Instalador: Visita http://localhost/gym_ma/instalar.php en tu navegador. El script creará automáticamente la base de datos gym_db y todas sus tablas.

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
