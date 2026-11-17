# ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO — Sitio web + CMS

Sitio institucional con panel administrador (CMS) para la **Asociación Civil Amigos por el Mundo**.
Arquitectura **MVC propia en PHP puro**: sin framework, sin Composer obligatorio y sin build de Node.

> Dominio de producción: **amigosporelmundo.org**

---

## 1. Stack técnico

| Capa        | Tecnología                                                        |
|-------------|-------------------------------------------------------------------|
| Lenguaje    | PHP 8.x (probado en PHP 8.2 local y PHP 8.3 en VPS)               |
| Base datos  | MySQL 5.6+ / MariaDB 10.x (InnoDB, `utf8mb4`)                     |
| Servidor    | Apache + `mod_rewrite` (hosting compartido) o Nginx + PHP-FPM (VPS) |
| Frontend    | CSS y JS propios (`amigos.css`, `app.js`), sin CDN externas       |
| Editor      | WYSIWYG propio (`contenteditable`), sin dependencias externas     |

---

## 2. Estructura del proyecto

```
AMIGOS PARA EL MUNDO/
├── index.php              ← Front controller / detector de raíz (local y producción)
├── .htaccess              ← Redirección a public/ + hardening (solo Apache)
├── app/
│   ├── Controllers/       ← Controladores público + admin
│   ├── Core/              ← MVC propio (Router, DB, Auth, CSRF, View, helpers…)
│   ├── Models/            ← Modelos (News, Video, Ad, SiteSetting, User…)
│   └── Services/          ← UploadService (subida de archivos)
├── config/
│   ├── .env               ← Credenciales locales (NO se sube a Git)
│   ├── .env.example       ← Plantilla de credenciales
│   ├── env.php            ← Cargador simple de variables de entorno
│   └── config.php         ← Configuración general
├── database/
│   ├── schema.sql         ← Estructura de tablas + datos seed
│   └── migrations/        ← Migraciones incrementales
├── public/                ← DocumentRoot (apuntar el dominio AQUÍ)
│   ├── index.php          ← Punto de entrada
│   ├── assets/            ← CSS, JS, fuentes e imágenes del tema
│   └── uploads/           ← Archivos subidos (media del sitio)
├── storage/               ← Sesiones y datos privados (NO público)
├── views/                 ← Plantillas (partials / public / admin)
└── README.md
```

---

## 3. Puesta en marcha local (Windows, portable)

El proyecto incluye PHP y MariaDB portables dentro de `.tools/` (no se versionan en Git).

```powershell
# 1) Arrancar MariaDB (datadir: .tools/mariadb/data)
Start-Process ".tools\mariadb\bin\mariadbd.exe" `
  -ArgumentList '--defaults-file=".tools\mariadb\my.ini"' -WindowStyle Hidden

# 2) Servidor PHP built-in (http://127.0.0.1:8000)
.tools\php\php.exe -S 127.0.0.1:8000 index.php
```

| URL                              | Qué muestra           |
|----------------------------------|-----------------------|
| http://127.0.0.1:8000/           | Home público          |
| http://127.0.0.1:8000/admin/login| Login del panel admin |

Base de datos local: **`amigos_mundo`** · host `127.0.0.1:3306` · usuario `root` · sin contraseña.

---

## 4. Despliegue en VPS (Nginx + PHP-FPM + MariaDB)

1. **Clonar** el repositorio en el servidor (ej. `/home/felix/amigosparaelmundo`).
2. **Base de datos**: crear BD y usuario, e importar el esquema o el dump de producción.
3. **`config/.env`**: copiar de `.env.example` y editar con los datos reales
   (`APP_URL=http://amigosporelmundo.org`, credenciales de BD, `APP_KEY` propia).
4. **vhost Nginx**: el `root` debe apuntar a `.../amigosparaelmundo/public` y PHP vía
   `fastcgi_pass` al socket del pool de PHP-FPM. El archivo se sirve con el
   front controller `index.php` (`try_files $uri /index.php?$query_string`).
5. **Permisos**: `storage/` y `public/uploads/` escribibles por el usuario del proceso web.
6. **SSL**: emitir el certificado (Certbot) una vez el DNS del dominio apunte al servidor.

---

## 5. Credenciales iniciales

| Usuario                        | Contraseña   | Rol   |
|--------------------------------|--------------|-------|
| `admin@amigosporelmundo.org`   | `Amigos2026` | Admin |

> 🔐 Cambiar la contraseña inmediatamente después del primer inicio de sesión.

---

## 6. Rutas principales

### Público
- `GET /` — Home
- `GET /noticias`, `GET /noticia/{slug}` — Noticias
- `GET /videos`, `GET /video/{slug}` — Videos
- `GET /radio`, `GET /radio/programa/{slug}` — Radio / programas
- `GET /podcast`, `GET /podcast/{slug}` — Podcast
- `GET /seccion/{slug}` — Sección
- `GET /pagina/{slug}` y catch-all `/{slug}` — Páginas estáticas
- `GET /buscar` — Buscador

### Admin
- `GET|POST /admin/login`, `GET /admin/logout`
- `GET /admin` — Dashboard
- Noticias, Secciones, Etiquetas, Videos, Programas, Páginas, Publicidad (CRUD)
- `GET /admin/ajustes` — Ajustes del sitio (solo Admin)
- `GET /admin/usuarios` — Usuarios (solo Admin)
- `GET /admin/auditoria` — Auditoría

---

## 7. Seguridad

- Contraseñas con `password_hash()` (bcrypt) y PDO con *prepared statements*.
- CSRF tokens en formularios y sanitización de salida con `htmlspecialchars()`.
- Middleware por rol (`auth`, `admin`, `editor_or_admin`).
- Rate limit de intentos de login.
- `storage/` bloqueado al acceso web; `public/uploads/` sin ejecución de PHP.
- Registro de auditoría (usuario, acción, entidad, IP, fecha).
