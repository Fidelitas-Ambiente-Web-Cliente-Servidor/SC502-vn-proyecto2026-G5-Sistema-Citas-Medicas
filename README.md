# Sistema de Citas Médicas Inteligentes — CCSS
**SC-502 Ambiente Web Cliente Servidor | Grupo 5 | Universidad Fidélitas**

Aplicación web para la gestión inteligente de citas médicas en la CCSS, con priorización de pacientes por gravedad de síntomas.

---

## Tecnologías

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Backend | PHP 8.2 |
| Base de datos | MySQL 8.0 |
| Servidor web | Apache (dentro de Docker) |
| Contenedores | Docker + Docker Compose |

---

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo
- Puerto **80** y **8080** libres en tu máquina (si están ocupados, cambialos en `docker-compose.yml`)

---

## Levantar el proyecto (primera vez)

Abrí una terminal en la carpeta `G5-AmbienteWeb-SV/` y ejecutá:

```bash
docker-compose up -d --build
```

Esto hace tres cosas automáticamente:
1. Construye la imagen PHP + Apache con el código del proyecto
2. Levanta MySQL 8.0 y ejecuta `bd.sql` (crea tablas e inserta datos de prueba)
3. Levanta phpMyAdmin para administrar la base de datos visualmente

La primera vez tarda ~1-2 minutos mientras descarga las imágenes.

---

## URLs del sistema

| Servicio | URL |
|---|---|
| **Aplicación web** | http://localhost:8080 |
| **phpMyAdmin** | http://localhost:8082 |

---

## Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@ccss.cr | 12345 |
| Médico | carlos@ccss.cr | 12345 |
| Paciente | sara@ccss.cr | 12345 |

---

## Comandos útiles de Docker

```bash
# Levantar en segundo plano (sin ver los logs)
docker compose up -d --build

# Ver logs en tiempo real
docker compose logs -f

# Ver logs solo de la app
docker compose logs -f app

# Ver logs solo de la base de datos
docker compose logs -f db

# Detener los contenedores (sin borrar datos)
docker compose stop

# Detener Y eliminar contenedores (sin borrar la BD)
docker compose down

# Detener, eliminar contenedores Y borrar la base de datos
docker compose down -v

# Reconstruir la imagen (después de cambios en Dockerfile)
docker compose up --build

# Ver contenedores corriendo
docker compose ps
```

---

## Estructura del proyecto (patrón MVC)

```
SC502-vn-proyecto2026-G5-Sistema-Citas-Medicas/
├── app/                                # Código de la aplicación (MVC)
│   ├── controllers/                    # Capa Controlador — recibe solicitudes y coordina
│   │   ├── UserController.php          # Login, registro, logout, perfil de usuario
│   │   ├── CitasController.php         # Agendar, cancelar, reprogramar citas
│   │   ├── DiagnosticoController.php   # Guardar y consultar historial médico
│   │   └── PersonalmedicoController.php # Directorio de médicos
│   ├── models/                         # Capa Modelo — acceso a la base de datos
│   │   ├── User.php                    # Operaciones sobre g5_users
│   │   ├── Cita.php                    # Operaciones sobre g5_citas
│   │   ├── Diagnostico.php             # Operaciones sobre g5_historial_medico
│   │   └── Personalmedico.php          # Operaciones sobre g5_personalmedico
│   └── views/                          # Capa Vista — páginas HTML que ve el usuario
│       ├── auth/
│       │   ├── login.php               # Pantalla de inicio de sesión
│       │   └── registro.php            # Registro de nuevos pacientes
│       ├── citas/
│       │   └── citas.php               # Módulo gestión de citas (Persona 2)
│       ├── diagnosticos/
│       │   └── diagnosticos.php        # Módulo diagnósticos e historial (Persona 4)
│       ├── personalmedico/
│       │   └── personalmedico.php      # Módulo agenda y directorio médico (Persona 3)
│       ├── main.php                    # Panel principal (dashboard con estadísticas)
│       ├── perfil.php                  # Perfil del usuario (editar datos y contraseña)
│       └── 404.php                     # Página de error — ruta no encontrada
├── config/
│   └── database.php                    # Configuración de conexión a MySQL
├── public/                             # Archivos accesibles directamente desde el navegador
│   ├── css/
│   │   └── style.css                   # Estilos globales + dark mode + responsive
│   ├── img/
│   │   └── ccss.png                    # Logo de la CCSS
│   └── js/
│       ├── modal.js                    # Modal de confirmación reutilizable
│       ├── main.js                     # Dashboard — carga estadísticas vía API
│       ├── citas.js                    # Lógica de citas (Persona 2)
│       ├── diagnosticos.js             # Diagnósticos + historial + notificaciones (Persona 4)
│       ├── personalmedico.js           # Agenda médica (Persona 3)
│       └── usuarios.js                 # Validaciones cliente — login/registro (Persona 1)
├── bd.sql                              # Esquema de base de datos + datos de prueba
├── index.php                           # Front Controller — único punto de entrada
├── Dockerfile                          # Imagen PHP 8.2 + Apache
├── docker-compose.yml                  # Orquestación: app + MySQL + phpMyAdmin
└── .dockerignore                       # Archivos excluidos de la imagen Docker
```

---

## Base de datos

El esquema completo está en [`bd.sql`](bd.sql). Se carga automáticamente al levantar Docker.

### Tablas principales

| Tabla | Descripción |
|---|---|
| `g5_users` | Usuarios del sistema (pacientes, médicos, admin) |
| `g5_personalmedico` | Catálogo de médicos disponibles |
| `g5_citas` | Citas médicas con prioridad y estado |
| `g5_historial_medico` | Historial clínico de diagnósticos por paciente |
| `g5_diagnosticos` | Diagnósticos detallados vinculados a citas |

### Ver la base de datos visualmente
Abrí **phpMyAdmin** en http://localhost:8082 — las credenciales ya están precargadas.

---

## Integrantes

| Nombre | Módulo |
|---|---|
| Araya Sancho Francisco Josué |
| Gabriel Villalobos Centeno |
| Michelle Guerrero Brenes |
| Julián Ortiz Barrantes |

---

*Proyecto académico — SC-502 Ambiente Web Cliente Servidor — Universidad Fidélitas 2026*
