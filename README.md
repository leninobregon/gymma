Sistema GYM MA, basado en el análisis de cada uno de los archivos que has proporcionado. Este es el manual maestro de tu software.

1. 🗄️ Arquitectura de Datos (El Corazón del Sistema)
El sistema se apoya en una base de datos relacional robusta definida en gym_ma_db.sql.

Tabla usuarios: Controla el acceso. Utiliza cifrado BCRYPT para contraseñas. Roles: ADMIN y CAJA.

Tabla socios: Almacena expedientes, contactos de emergencia y condiciones de salud.

Tabla planes: Define la duración (en días) y el precio.

Tabla ventas: El registro histórico. Es vital porque guarda la tasa_cambio_momento, permitiendo reportes financieros precisos aunque el dólar fluctúe.

Tabla cajas: Gestiona los turnos (Apertura, Monto Esperado, Monto Real, Cierre).

Tabla inventario: Control de suplementos o productos con alertas de stock bajo.

2. 💰 Ciclo de Caja y Ventas (Flujo Operativo)
El sistema está diseñado para que no existan fugas de dinero mediante un flujo obligatorio:

Apertura (apertura_caja.php): El cajero debe iniciar sesión y declarar con cuánto dinero empieza (Córdobas y Dólares). Si no abre caja, el sistema le bloquea el acceso al POS.

Punto de Venta (punto_venta.php):

Búsqueda Inteligente: Usa buscar_socio.php para encontrar clientes por nombre o cédula mediante AJAX.

Multimoneda: Permite cobrar en COR o USD. Calcula el vuelto automáticamente basado en la tasa configurada.

Tipos de Venta: Puede vender "Planes" (suscripciones) o "Productos" (inventario).

Cierre de Turno (cerrar_caja.php): El sistema suma las ventas a la apertura y le dice al cajero cuánto "Debe Haber". El cajero ingresa el "Monto Real" y el sistema calcula automáticamente el faltante o sobrante.

3. 👥 Gestión de Socios y Membresías
Control de Vigencia: El sistema no solo guarda la fecha, sino que calcula dinámicamente si un socio está Activo, Vencido o Próximo a vencer (Badge naranja) comparando la fecha de ingreso + días del plan contra la fecha actual (socios.php).

Expediente Detallado: En editar_socio.php se capturan datos críticos como enfermedades y contactos de emergencia, esenciales para la seguridad civil dentro del gimnasio.

4. 📊 Administración y Reportes (Solo ADMIN)
El panel de control (dashboard.php) ofrece una vista ejecutiva:

Gráficas: Usa Chart.js para mostrar los ingresos de los últimos 7 días.

Alertas: Avisa de inmediato si hay productos con menos de 5 unidades en stock.

Historial de Cajas: Permite auditar cada turno de cada cajero de forma individual (historial_cajas.php).

Respaldo: El archivo respaldar.php genera un volcado completo de la base de datos en formato .sql para descargar, asegurando la información ante fallos del servidor.

5. 🛠️ Herramientas de Configuración e Instalación
Instalación Automática (instalar.php): Un script que crea la base de datos, las tablas y el usuario administrador inicial (admin / admin123) desde cero.

Configuración Global (configuracion.php): Aquí se centraliza el nombre del gimnasio, el logo, la moneda base y, lo más importante, la Tasa de Cambio BCN.

6. 📝 El Ticket de Venta (imprimir_recibo.php)
Diseñado para impresoras térmicas (80mm):

Muestra el concepto de venta.

Muestra el total en Córdobas y su equivalente en Dólares.

Incluye mensaje de agradecimiento y datos de contacto del gimnasio.

🔍 Diagnóstico Técnico (Observaciones de Gemini)
Seguridad: El sistema es seguro al usar PDO para prevenir Inyecciones SQL y session_start para proteger las rutas.

Escalabilidad: Al estar basado en clases (Socio.php, Inventario.php, etc.), es fácil añadir módulos nuevos (como control de asistencia por huella o QR).

Portabilidad: Al ser PHP puro con CSS nativo, corre en cualquier servidor local (XAMPP) o hosting económico.
