# 🐾 Almitas Peludas

Sistema de gestión para peluquería canina a domicilio y pet shop.

## Stack

- **Backend:** PHP 8 (Vanilla)
- **Frontend:** HTML, CSS, JavaScript
- **Base de Datos:** MySQL

## Estructura

```
/
├── index.php          # Landing page
├── agendar.php        # Formulario de turnos
├── tienda.php         # Catálogo de productos
├── carrito.php        # Carrito de compras
├── admin/             # Panel administrativo
├── api/               # Endpoints REST
├── config/            # Configuración BD
├── includes/          # Componentes PHP
└── assets/            # CSS, JS, imágenes
```

## Instalación en Ferozo

1. **Subir archivos** por FTP a `public_html/`
2. **Crear base de datos** en cPanel → MySQL
3. **Importar SQL** en phpMyAdmin: `almitas_db.sql`
4. **Configurar conexión** en `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'tu_usuario_almitas_db');
   define('DB_USER', 'tu_usuario_db');
   define('DB_PASS', 'tu_password');
   ```

## Credenciales de Prueba

- **Admin:** admin@almitaspeludas.com / password
- **Cliente:** cliente@test.com / password

## Módulos

- **Peluquería:** Turnos a domicilio por zonas (Lun=Oeste, Mié=Centro, Vie=Norte)
- **Pet Shop:** Productos a pedido (dropshipping interno)
- **Admin:** Dashboard y lista de compras por proveedor
