# Examen aprobatorio de manejo

Simulador de examen teórico para **saber si el alumno puede sacar su licencia**.
Funciona en modo instructor (el maestro lo aplica en la computadora de la escuela) y
el alumno responde **una pregunta a la vez**, **sin poder volver atrás**, con **30 minutos**
de tiempo límite y **nota guardada en la base de datos**.

## URLs

| URL | Quién la usa | Qué hace |
|---|---|---|
| `/examen-alumnos.php` | Instructor (sesión del panel admin) | Inscribe alumnos, define la hora de habilitación, re-habilita, revisa notas |
| `/examen.php` | Alumno | Entra con su código, espera su hora, responde el examen y ve su nota |

## Flujo del instructor

1. Entra al panel (`admin.php`) o directo a `/examen-alumnos.php` (misma sesión: `admin` / `1234`).
2. **Inscribir alumno**: nombre, DUI (opcional), código (se genera solo, editable) y la
   **hora en la que se habilita** su examen. El código se lo entregas al alumno.
3. El alumno entra a `/examen.php` con ese código (y opcionalmente su DUI).
   - Si todavía no es la hora → pantalla de espera con cuenta regresiva; la página
     se recarga sola y arranca el examen.
   - Si ya venció → pantalla de "tiempo agotado"; el instructor puede **re-habilitarlo**
     con una nueva hora (eso reinicia sus respuestas y su nota).
4. Al terminar, el instructor ve el **detalle pregunta por pregunta** del alumno
   (✓ correcto / ✗ incorrecto) desde la consola.

## Reglas del examen

- Las preguntas se muestran **de una en una** (50 preguntas del banco `modules/data/preguntas.php`).
- **No se puede volver a una pregunta anterior**: el servidor rechaza (`pregunta_bloqueada`)
  cualquier respuesta a una pregunta que ya se cerró, y el cliente atrapa el botón "atrás"
  del navegador. Tampoco se puede saltar a una pregunta futura.
- **Tiempo límite de 30 minutos** desde la hora de habilitación (la cuenta regresiva la
  calcula el servidor, no el reloj del equipo). Al agotarse, el examen se cierra solo y
  las preguntas sin responder cuentan como incorrectas.
- Cada respuesta se guarda en la base de datos al instante: si el alumno recarga la página
  o se cae la conexión, **retoma donde iba** (la pregunta abierta es la que el servidor tiene).
- Al finalizar se muestra la **nota (0–100)**, el sello Aprobado / No aprobado y el detalle
  de las preguntas falladas.
- **Las respuestas correctas nunca se envían al navegador**: el cliente solo recibe las
  preguntas y las opciones; la corrección es 100% del servidor.

### Parámetros configurables

| Constante | Archivo | Valor | Para que sirve |
|---|---|---|---|
| `EXAMEN_VENTANA_MINUTOS` | `config/examen_funciones.php` | `30` | Minutos desde la habilitación |
| `EXAMEN_UMBRAL` | `config/examen_funciones.php` | `80` | % mínimo para aprobar |
| `date_default_timezone_set` | `php/base.php` | `America/El_Salvador` | Hora local de la escuela |

## Base de datos

- Motor: **MySQL (WAMP)** · Base: `esdrive` · Conexión PDO en `config/conexion.php`
  (`root` sin contraseña, se crea sola la primera vez).
- Tabla: **`examen_alumnos`** (esquema en `config/examen.sql`).

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | |
| `nombre` | VARCHAR(120) | Nombre del alumno |
| `dui` | VARCHAR(20) | Opcional; si se captura, el alumno debe escribirlo para entrar |
| `codigo` | VARCHAR(32) UNIQUE | Código de acceso del alumno |
| `habilitado_desde` | DATETIME | Hora en la que se habilita su examen |
| `vence` | DATETIME | `habilitado_desde` + 30 minutos |
| `inicio_real` | DATETIME | Primer acceso real del alumno |
| `estado` | ENUM | `pendiente`, `en_curso`, `aprobado`, `reprobado`, `expirado` |
| `aciertos` / `total` | INT | Respuestas correctas y total de preguntas |
| `porcentaje` | DECIMAL(5,2) | |
| `nota` | TINYINT | 0–100 |
| `respuestas` | LONGTEXT | JSON con lo que respondió: `[1,1,0,…]` |
| `pregunta_actual` | INT | Índice de la pregunta abierta |
| `terminado_en` | DATETIME | Cuándo lo terminó |
| `creado_en` | DATETIME | Cuándo se inscribió |

Consultas que puede usar la vista de examenes del panel:

```sql
-- Notas de todos los alumnos
SELECT nombre, dui, codigo, estado, nota, aciertos, total, porcentaje,
       habilitado_desde, terminado_en
FROM examen_alumnos
ORDER BY terminado_en DESC;

-- Solo los que ya rindieron el examen
SELECT * FROM examen_alumnos WHERE estado IN ('aprobado','reprobado');

-- Pendientes de tomar
SELECT * FROM examen_alumnos WHERE estado = 'pendiente';
```

## Qué NO se tocó (para tu compañero)

- `modules/views/admin/examenes.php` — **sin cambios**, es donde se van a mostrar las notas.
- `modules/views/admin/dashboard.php` y las demás vistas del panel.
- La vista de examenes puede leer de `examen_alumnos` tal cual; si quiere mostrar el detalle
  de respuestas, el JSON está en la columna `respuestas` (índice = número de pregunta, base 0).

Único enlace agregado en el panel: el ítem **"Simulador"** del sidebar
(`modules/views/layouts/admin_sidebar.php`) que abre la consola del instructor.
Si en su rama ya existe ese ítem, se puede quitar sin romper nada.

## Archivos de esta función

```
examen.php                    Examen del alumno (acceso, espera, examen, resultado)
examen-alumnos.php            Consola del instructor
config/conexion.php           Conexion PDO + creacion de la BD y la tabla
config/examen_funciones.php   Reglas del examen (ventana, bloqueo, nota)
config/examen.sql             Esquema de la tabla (referencia para el panel)
modules/data/preguntas.php    Banco de 50 preguntas
assets/css/examen.css         Estilos (navy + dorado)
```

## Pendientes opcionales

- Timer visible con aviso de los últimos 5 minutos (ya cambia de color) y cierre automático (ya).
- Barajar el orden de las preguntas.
- Banco de preguntas editable desde el panel (hoy es un PHP fijo de 50 preguntas).
- Historial de intentos por alumno (varios intentos con su nota).
