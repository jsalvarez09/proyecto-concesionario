# 🚗 Motors And Dealers - Plataforma de Gestión Vehicular

![Estado del Proyecto](https://img.shields.io/badge/Estado-Terminado-success?style=flat-square)
![PHP](https://img.shields.io/badge/Backend-PHP-blue?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/Database-MySQL-orange?style=flat-square&logo=mysql)
![Frontend](https://img.shields.io/badge/Frontend-HTML5%20%7C%20CSS3%20%7C%20JS-yellow?style=flat-square)

**Motors And Dealers** es una solución web integral para la compra y venta de vehículos. Diseñada para conectar concesionarios y vendedores independientes con potenciales compradores, ofreciendo una experiencia de usuario fluida y un panel de administración robusto para la gestión del inventario.

---

## ✨ Características Principales

### 🔐 Seguridad y Autenticación
* **Sistema de Roles:** Diferenciación clara entre `Cliente`, `Vendedor` y `Administrador`.
* **Protección Robusta:** Uso de sentencias preparadas (`mysqli`) para prevenir inyecciones SQL.
* **Cifrado:** Contraseñas almacenadas de forma segura utilizando `password_hash` (Bcrypt).
* **Sesiones Seguras:** Validación de autenticación en todas las rutas protegidas.

### 🚘 Gestión de Vehículos (Inventario)
* **CRUD Completo:** Publicar, Editar, Visualizar y Eliminar vehículos.
* **Galería Multimedia:** Carga múltiple de imágenes con validación de tipos MIME (seguridad anti-malware).
* **Estado del Vehículo:** Flujo lógico de estados (`Disponible` -> `Vendido`).
* **Comparador:** Herramienta interactiva para comparar características de hasta 4 vehículos simultáneamente.

### 📊 Dashboard y Analítica
* **Panel de Usuario:** Vista personalizada según el rol.
* **KPIs en Tiempo Real:** Estadísticas de vehículos activos, ventas realizadas y ganancias totales.
* **Gestión de Perfil:** Actualización de datos personales y contacto.

---

## 🛠️ Stack Tecnológico

El proyecto ha sido construido utilizando tecnologías nativas para garantizar rendimiento y facilidad de despliegue:

* **Backend:** PHP 8+ (Programación estructurada y orientada a seguridad).
* **Base de Datos:** MySQL / MariaDB (Relacional).
* **Frontend:**
    * HTML5 Semántico.
    * CSS3 (Diseño Responsivo y Variables CSS).
    * JavaScript (Vanilla JS para interactividad y validaciones).
* **Servidor Web:** Apache (XAMPP/WAMP/LAMP).

---

## 📂 Estructura del Proyecto

```text
/
├── 📁 assets/           # Recursos estáticos (CSS, JS, Imágenes del sitio)
├── 📁 includes/         # Lógica reutilizable (Conexión DB, Auth, Header)
├── 📁 pages/
│   └── 📁 motors/       # Módulos principales (Inventario, Panel, CRUD)
├── 📁 uploads/          # Almacenamiento de imágenes de vehículos
├── 📄 database.sql      # Script de importación de la Base de Datos
├── 📄 index.php         # Página de inicio (Landing Page)
└── 📄 README.md         # Documentación del proyecto

🚀 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en tu entorno local:

1. Prerrequisitos

Tener instalado XAMPP, WAMP o similar.

Navegador web moderno.

2. Clonar el Repositorio

Descarga el código o clónalo en tu carpeta de servidor (ej. htdocs):

git clone [https://github.com/TU_USUARIO/motors-and-dealers.git](https://github.com/TU_USUARIO/motors-and-dealers.git)

3. Configurar la Base de Datos

1. Abre phpMyAdmin (o tu gestor SQL preferido).

2. Crea una nueva base de datos llamada concesionario_db.

3. Importa el archivo database.sql que se encuentra en la raíz del proyecto.

4. Configurar la Conexión

Abre el archivo includes/conexion.php y ajusta la constante BASE_URL según el nombre de tu carpeta:

PHP
// Ejemplo: Si tu carpeta se llama "mi-proyecto"
define('BASE_URL', '/mi-proyecto/');

Nota: Si usas XAMPP por defecto (user: root, pass: vacío), no necesitas cambiar las credenciales de la BD.

5. ¡Listo!

Abre tu navegador e ingresa a: http://localhost/NOMBRE_DE_TU_CARPETA/📸 

## 👤 Autor

### Juan Sebastián Alvarez

<div align="left">

<a href="https://github.com/jsalvarez09" target="_blank">
  <img src="https://img.shields.io/badge/GitHub-000000?style=for-the-badge&logo=github&logoColor=white"/>
</a>

<a href="https://www.linkedin.com/in/juan-sebastian-alvarez-huertas-a76b5a379" target="_blank">
  <img src="https://img.shields.io/badge/LinkedIn-0A66C2?style=for-the-badge&logo=linkedin&logoColor=white"/>
</a>

<a href="mailto:sebasalvare9707@gmail.com">
  <img src="https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white"/>
</a>

</div>

<br>

Desarrollador apasionado por crear soluciones web eficientes y escalables.