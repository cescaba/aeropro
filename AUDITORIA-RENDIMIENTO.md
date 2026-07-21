# Auditoría de Rendimiento - Plugin vc-onboarding-wizard

Fecha del análisis: 2026-07-21
Alcance: exclusivamente rendimiento (TTFB, LCP, CLS, INP, carga de red, CPU/memoria en servidor y en el navegador). No se evalúa seguridad ni arquitectura salvo cuando impacta directamente en velocidad.

---

## 1. Resumen ejecutivo

`vc-onboarding-wizard` es un plugin pequeño (~2.800 líneas entre PHP y JS) que implementa un wizard de registro multi-paso, verificación de email, un dashboard de socio (PMPro) y un login personalizado. A diferencia de `vc-flashcards` (que tiene módulos grandes de agregación de datos), aquí la superficie de "trabajo pesado" es mucho más acotada: no hay bucles sobre catálogos de contenido, no hay `WP_Query` con `posts_per_page => -1`, y el propio plugin ya resuelve bien el problema de "no volver a parsear el CSV en cada request" (la importación de `data/countries.csv` y `data/states.csv` a la tabla `wp_vc_locations` está protegida con un `get_option()`/`filemtime()` idempotente, por lo que solo se ejecuta una vez por versión de archivo, nunca en cada carga de página — buen patrón, documentado como referencia positiva).

Dicho esto, el análisis completo de los 9 archivos PHP, los 3 módulos JS y las 3 hojas CSS reveló **8 hallazgos reales**, no 20: este plugin es sencillamente demasiado pequeño para acumular esa cantidad de problemas distintos sin inventar hallazgos triviales, así que se reporta el número real en vez de forzar la lista. Se prioriza honestidad sobre volumen, tal como se pidió.

El hallazgo más severo (H-01) es que `get_profile_states()` (`includes/traits/trait-vc-onboarding-helpers.php`) hace un `SELECT ... GROUP BY` sobre **toda** la tabla `wp_vc_locations` —que contiene 5.309 filas de estados/provincias de 230 países, sembradas desde `data/states.csv` (186 KB)— sin ningún `WHERE country_code = %s` y sin ninguna capa de caché. Esta función se invoca en cada render de la vista "Profile" del dashboard **y** de nuevo en cada envío del formulario de perfil (`handle_save_account_profile()`), donde el PHP filtra en memoria las ~20-50 filas relevantes de un solo país después de haber traído las 5.309. Es el mismo patrón de "recalcular/releer un catálogo completo sin cachear" que se documentó como hallazgo #1 en la auditoría de `vc-flashcards`, aplicado aquí a datos geográficos en vez de flashcards.

El segundo hallazgo relevante es que `enqueue_public_assets()` (mismo archivo) encola `onboarding.js` y `dashboard.js` en **cualquier página de tipo `page` del sitio** (`is_page()`), no solo en las páginas del propio wizard/dashboard — el mismo patrón que el "help-fab" de `vc-flashcards`, aunque aquí con ~20 KB combinados de JS sin minificar en vez de un widget de ayuda.

También se confirmó, igual que en `vc-flashcards`, que el pipeline de despliegue (`.github/workflows/deploy.yml`) es un FTP directo sin ningún paso de build/minificación, y que la imagen de fondo del panel izquierdo del onboarding (`onboarding-left.png`, 897 KB) se sirve sin ninguna variante optimizada/responsive, además de convivir con un duplicado exacto sin usar (`onboarding-left.jpg`, 912 KB) que solo pesa en el repositorio/deploy, nunca se descarga en el navegador porque ningún archivo lo referencia.

Ninguno de los 8 hallazgos es una emergencia: no hay bucles sobre miles de registros de usuario, ni N+1 real sobre contenido dinámico. Es, en conjunto, una cantidad moderada de trabajo evitable concentrado en dos puntos calientes (la vista de Perfil y la carga global de JS) más el housekeeping habitual de assets sin build.

---

## 2. Hallazgos detallados

### 2.1 `includes/traits/trait-vc-onboarding-helpers.php`

#### H-01. `get_profile_states()` trae las 5.309 filas de la tabla completa sin `WHERE` ni caché, en cada render y cada guardado del Perfil
- **Archivo y ruta:** `includes/traits/trait-vc-onboarding-helpers.php`
- **Clase/función afectada:** `VC_Onboarding_Wizard_Helpers::get_profile_states()` (línea ~172), invocada desde `render_dashboard_profile_view()` (trait-vc-onboarding-shortcodes.php, línea ~473) y desde `handle_save_account_profile()` (trait-vc-onboarding-handlers.php, línea ~210)
- **Descripción técnica:** La consulta es:
  ```sql
  SELECT country_code, state_code, state_name
  FROM wp_vc_locations
  WHERE state_code <> '' AND state_name <> ''
  GROUP BY country_code, state_code, state_name
  ORDER BY country_code ASC, state_name ASC
  ```
  No filtra por `country_code`, así que MySQL siempre escanea y agrupa las 5.309 filas sembradas desde `data/states.csv` (230 países), y PHP recibe el array completo. Tanto en `render_dashboard_profile_view()` (para pintar el `<select>` de estados) como en `handle_save_account_profile()` (para validar que el `state_code` enviado pertenezca al país elegido) el código luego hace un `foreach` en PHP para quedarse solo con las filas del país activo (ver `trait-vc-onboarding-handlers.php` líneas 217-225). Es decir: se trae y transfiere 100% del catálogo geográfico mundial para usar típicamente menos del 1% de las filas, y se repite esta traída completa en cada carga de la vista y en cada submit del formulario, sin transient ni caché de ningún tipo (`get_profile_countries()`, línea ~151, tiene el mismo problema mas su tabla es pequeña: 251 filas).
- **Nivel de impacto:** Alto
- **Impacto estimado en el rendimiento:** Es la consulta más pesada de todo el plugin. Afecta el TTFB de la vista "Profile" del dashboard (una de las pantallas de uso más frecuente, ya que ahí vive también el cambio de datos de cuenta) y se duplica en cada guardado de perfil (hasta 2 llamadas completas: countries + states). Con objeto-caché persistente (Redis/Memcached) el impacto real cae bastante tras la primera lectura por request, pero sin él (WP por defecto, hosting compartido típico) cada request repite el escaneo completo de la tabla.
- **Solución recomendada:** Dos cambios independientes y complementarios: (1) agregar `WHERE country_code = %s` cuando se conoce el país (en `handle_save_account_profile()` ya se sabe qué país se está validando, no hace falta traer los otros 229); (2) cachear el resultado completo de `get_profile_countries()`/`get_profile_states()` con `wp_cache_get()`/`set_transient()`, invalidando la caché solo cuando cambie `vc_ow_states_csv_imported`/`vc_ow_countries_csv_imported` (el propio plugin ya tiene ese versionado, solo falta reutilizarlo como clave de caché).
- **Ejemplo de código optimizado:**
```php
private function get_profile_states(): array {
  $cache_key = 'vc_ow_profile_states_' . get_option('vc_ow_states_csv_imported', '0');
  $cached = get_transient($cache_key);
  if (is_array($cached)) {
    return $cached;
  }

  global $wpdb;
  $table_name = vc_ow_locations_table_name();
  $rows = $wpdb->get_results(
    "SELECT country_code, state_code, state_name
    FROM {$table_name}
    WHERE state_code <> '' AND state_name <> ''
    GROUP BY country_code, state_code, state_name
    ORDER BY country_code ASC, state_name ASC",
    ARRAY_A
  );
  $rows = is_array($rows) ? $rows : [];

  set_transient($cache_key, $rows, DAY_IN_SECONDS);
  return $rows;
}

// Y en handle_save_account_profile(), filtrar en SQL en vez de en PHP cuando se conoce el país:
private function get_profile_states_for_country(string $country_code): array {
  global $wpdb;
  $table_name = vc_ow_locations_table_name();
  return (array) $wpdb->get_results($wpdb->prepare(
    "SELECT state_code, state_name FROM {$table_name}
     WHERE country_code = %s AND state_code <> '' AND state_name <> ''
     ORDER BY state_name ASC",
    $country_code
  ), ARRAY_A);
}
```

#### H-02. `get_profile_location_label()` agrega 1-2 consultas más por cada guardado de perfil
- **Archivo y ruta:** `includes/traits/trait-vc-onboarding-helpers.php`, función `get_profile_location_label()` (línea ~193), invocada desde `handle_save_account_profile()` (trait-vc-onboarding-handlers.php, líneas ~241 y ~243)
- **Descripción técnica:** Cada vez que se guarda el perfil con un país/estado válido, se ejecutan hasta dos consultas adicionales (`SELECT country_name ...` y, si hay estado, `SELECT state_name ...`) solo para construir la etiqueta legible (`"California, United States"`) que se guarda en `user_meta`. Sumado a H-01, un solo submit del formulario de perfil puede disparar hasta 4 consultas contra `wp_vc_locations` (2 de validación + 2 de esta función), todas contra datos que no cambian entre requests.
- **Nivel de impacto:** Medio
- **Impacto estimado en el rendimiento:** Bajo en aislamiento (son consultas por clave/índice, no full-scan como H-01), pero se acumula con H-01 en la misma acción de usuario (guardar perfil), aumentando la latencia total de esa petición POST.
- **Solución recomendada:** Reutilizar el resultado ya cacheado de `get_profile_countries()`/`get_profile_states()` (una vez aplicado H-01) para resolver `country_name`/`state_name` en PHP con un `array_filter`, en vez de volver a golpear la base de datos.

#### H-03. `enqueue_public_assets()` carga `onboarding.js` y `dashboard.js` en **cualquier página** del sitio, no solo en las del wizard/dashboard
- **Archivo y ruta:** `includes/traits/trait-vc-onboarding-helpers.php`, función `enqueue_public_assets()` (línea ~6), enganchada a `wp_enqueue_scripts` en el constructor de `VC_Onboarding_Wizard_PMPro` (`includes/class-vc-onboarding-wizard-pmpro.php`, línea 62)
- **Descripción técnica:** La única condición es `if (is_page())`, es decir, se cumple en **toda** página de tipo `page` de todo el sitio (about, contacto, términos, etc.), no solo en `/register/`, `/dashboard/` o las páginas de pasos del wizard. Ambos scripts se encolan con `true` como último argumento de `wp_enqueue_script()` (carga en `wp_footer`), así que **no son render-blocking** en sentido estricto — esto es un punto a favor real. Aun así, el navegador descarga y ejecuta ~20 KB sin comprimir de JS (`dashboard.js` 15,4 KB / 455 líneas + `onboarding.js` 4,4 KB / 135 líneas) en páginas que no tienen ninguno de los formularios/paneles que ese JS manipula; el código internamente hace early-return cuando no encuentra los selectores (`if (!sidebar || !toggle) return;`), por lo que el costo de CPU real es mínimo, pero la descarga y el parseo no lo son.
- **Nivel de impacto:** Medio
- **Impacto estimado en el rendimiento:** Al estar en el footer, no afecta directamente el LCP/render inicial. Sí es peso de red y trabajo de parseo/compilación de JS repetido en cada página del sitio completo, no solo en las relacionadas con onboarding — mismo patrón exacto que el "help-fab" documentado en la auditoría de `vc-flashcards` (H-04 allá).
- **Solución recomendada:** Condicionar el enqueue a las páginas reales del wizard/dashboard, usando `has_shortcode()` sobre el contenido de la página (el propio plugin ya usa ese patrón en `use_blank_template_for_onboarding()`) o una lista explícita de slugs.
- **Ejemplo de código optimizado:**
```php
public function enqueue_public_assets() {
  if (!is_page() || !$this->current_page_uses_onboarding_assets()) {
    return;
  }
  // ... resto igual
}

private function current_page_uses_onboarding_assets(): bool {
  global $post;
  if (!$post instanceof WP_Post) {
    return false;
  }

  $slugs = ['register', 'registro-email', 'registro-datos', 'registro-final', 'verificar', self::DASHBOARD_SLUG];
  if (in_array($post->post_name, $slugs, true)) {
    return true;
  }

  return has_shortcode($post->post_content, 'vc_member_dashboard')
    || has_shortcode($post->post_content, 'vc_custom_login');
}
```

---

### 2.2 `templates/vc-blank-page.php` / `templates/vc-dashboard-page.php`

#### H-04. Google Fonts cargado con `<link rel="stylesheet">` clásico y bloqueante en cada página del embudo de onboarding
- **Archivo y ruta:** `templates/vc-blank-page.php`, líneas 84-86
- **Descripción técnica:** El plantilla en blanco que reemplaza el template estándar de WordPress para las páginas `register`, `registro-email`, `registro-datos`, `registro-final`, `verificar` y la página de login personalizado imprime:
  ```html
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  ```
  Los `preconnect` mitigan parte de la latencia de conexión, y el propio parámetro `&display=swap` de Google Fonts ya evita el bloqueo de texto invisible (FOIT) del lado del navegador que carga la fuente. Sin embargo, la tercera línea es un `<link rel="stylesheet">` síncrono a un dominio externo: el navegador debe esperar esa respuesta antes de terminar de construir el CSSOM del documento, lo cual retrasa el primer render en redes lentas o cuando Google Fonts tarda en responder.
- **Nivel de impacto:** Bajo/Medio
- **Impacto estimado en el rendimiento:** Con los `preconnect` ya presentes el impacto está parcialmente mitigado; el costo residual es la espera de la respuesta CSS de Google Fonts antes de poder pintar texto con la tipografía correcta, en cada una de las 6+ páginas que usan este template. No es render-blocking del árbol completo (el HTML sigue parseándose), pero sí retrasa el CSSOM y por tanto el primer paint con la fuente correcta.
- **Solución recomendada:** Cargar el CSS de Google Fonts de forma asíncrona (patrón `media="print" onload="this.media='all'"` o `rel="preload" as="style"` + `onload`) o, mejor aún dado que solo se usan 4 pesos de una sola familia, auto-hospedar los archivos `.woff2` de Poppins en el propio plugin con `font-display: swap`, eliminando la dependencia de red externa por completo.

#### H-05. CSS del onboarding/dashboard insertado con `<link>` manual en vez de `wp_enqueue_style()`
- **Archivo y ruta:** `templates/vc-blank-page.php` línea 88, `templates/vc-dashboard-page.php` línea 14
- **Descripción técnica:** Ambos templates imprimen su hoja principal (`onboarding.css`, `dashboard.css`) con un `<link>` construido a mano vía `add_query_arg('ver', $onboarding_css_ver, $onboarding_css)`, en vez de `wp_enqueue_style()`. El *cache-busting* por `filemtime()` está bien implementado, así que no hay bug funcional, pero al no pasar por la cola de encolado de WordPress este CSS no es visible para plugins de optimización (minificadores, combinadores de archivos, critical CSS) que dependen del sistema estándar de `wp_enqueue_style`/`wp_enqueue_script`.
- **Nivel de impacto:** Bajo
- **Impacto estimado en el rendimiento:** Ninguno por sí solo; es una limitación para futura optimización automática, no un problema de velocidad actual.
- **Solución recomendada:** Mover ambas hojas a `wp_enqueue_style()` enganchado a `wp_enqueue_scripts`, conservando el mismo `filemtime()` como versión.

---

### 2.3 Imágenes (`templates/assets/onboarding-left.jpg` / `.png`)

#### H-06. Imagen de fondo de 897 KB sin variante optimizada, más un duplicado de 912 KB sin usar
- **Archivo y ruta:** `templates/assets/onboarding-left.png` (897 KB), referenciada desde `templates/vc-blank-page.php` línea 11 (`background-image:url(...)` en el `<aside class="vc-onb-left">`); `templates/assets/onboarding-left.jpg` (912 KB) existe en el mismo directorio pero **no aparece referenciada en ningún PHP, JS ni CSS del plugin** (se verificó con búsqueda global de ambos nombres de archivo en todo el plugin).
- **Descripción técnica:** `onboarding-left.png` se usa como imagen de fondo CSS de todo el panel izquierdo, visible en las 6 páginas del embudo de registro/login — es, en la práctica, el elemento más pesado del LCP de esas pantallas. No existe ninguna variante `.webp`/`.avif`, ni distintos tamaños para pantallas pequeñas (se sirve el mismo archivo de escritorio en cualquier viewport vía CSS, sin `srcset` — es un `background-image`, no un `<img>`, así que ni siquiera puede beneficiarse de `srcset` nativo sin refactor a `<picture>`/`<img>`). El archivo `.jpg` gemelo (912 KB) no se descarga nunca porque nada lo enlaza, pero infla innecesariamente el peso del repositorio y del paquete que se sube por FTP en cada deploy.
- **Nivel de impacto:** Alto (por la imagen activa) / Bajo (por el duplicado muerto)
- **Impacto estimado en el rendimiento:** La imagen activa de 897 KB es, con alta probabilidad, el mayor contribuyente individual al peso de red y al tiempo de LCP de las páginas de registro/login — mucho más que todo el JS/CSS del plugin combinado. Una versión WebP/AVIF bien comprimida del mismo diseño normalmente pesa 70-90% menos sin pérdida visual perceptible. El duplicado `.jpg` no afecta a los visitantes (nunca se transfiere), solo al tiempo de deploy y al tamaño del repositorio.
- **Solución recomendada:** Recomprimir/convertir `onboarding-left.png` a WebP (con fallback JPEG si se requiere compatibilidad extrema) apuntando el `background-image` al nuevo archivo, y evaluar generar 1-2 tamaños adicionales servidos vía `background-image` con media queries para viewports móviles/tablet. Eliminar `onboarding-left.jpg` si en efecto no se usa, o documentar por qué se conserva.

---

### 2.4 Pipeline de despliegue (`.github/workflows/deploy.yml`)

#### H-07. Sin paso de build/minificación antes del deploy por FTP
- **Archivo y ruta:** `.github/workflows/deploy.yml`
- **Descripción técnica:** El workflow solo hace `actions/checkout` y luego `SamKirkland/FTP-Deploy-Action` subiendo `local-dir: ./` tal cual. No hay `npm install`, ni `esbuild`/`webpack`/`terser`/`cssnano`, ni ningún paso intermedio. Las tres hojas CSS principales (`dashboard.css` 88 KB/3.156 líneas, `login.css` 37 KB/1.311 líneas, `onboarding.css` 29 KB/1.166 líneas — 154 KB combinados) y los tres módulos JS (`dashboard.js`, `onboarding.js`, `login.js` — ~29 KB combinados) se sirven exactamente como viven en el repositorio, sin minificar.
- **Nivel de impacto:** Medio
- **Impacto estimado en el rendimiento:** Igual razonamiento que en la auditoría de `vc-flashcards`: la compresión gzip/brotli del servidor ya reduce buena parte del peso de transferencia de texto repetitivo, pero minificar reduce adicionalmente el tamaño antes de comprimir y el tiempo de parseo en el navegador. Con 154 KB de CSS sin minificar, el ahorro potencial (20-35% adicional sobre la compresión HTTP) es más relevante aquí que en el JS, dado el tamaño de `dashboard.css`.
- **Solución recomendada:** Agregar un paso de build (esbuild/Terser + cssnano o similar) al workflow antes de la acción de FTP, generando `.min.css`/`.min.js` y apuntando los `<link>`/`wp_enqueue_*` de producción a esos archivos (condicionado por `WP_DEBUG` para mantener las versiones legibles en desarrollo).

---

### 2.5 `includes/traits/trait-vc-onboarding-shortcodes.php`

#### H-08. `render_dashboard_subscription_view()`: patrón N+1 acotado al construir la lista de facturas
- **Archivo y ruta:** `includes/traits/trait-vc-onboarding-shortcodes.php`, función `render_dashboard_subscription_view()` (línea ~488)
- **Descripción técnica:** Tras obtener hasta 3 órdenes con `MemberOrder::get_orders(['limit' => 3, ...])`, el código crea una instancia nueva de `MemberOrder` y llama `getMemberOrderByID()` **por cada orden** (línea ~568-569) para poder acceder a `get_formatted_total()`/`getTimestamp()`, en vez de reutilizar los datos ya traídos por `get_orders()`. Es un N+1 clásico, pero está estrictamente acotado a `limit => 3`, así que en la práctica nunca son más de 3 consultas adicionales por carga de la vista "Subscription".
- **Nivel de impacto:** Bajo
- **Impacto estimado en el rendimiento:** Insignificante en términos absolutos (máximo 3 consultas extra, y solo en la vista de Subscription, no en el resto del dashboard). Se documenta por completitud y consistencia con el resto de la auditoría, no porque represente una prioridad real.
- **Solución recomendada:** Si `MemberOrder::get_orders()` ya devuelve suficientes campos para `code`/`status`/fecha, evaluar evitar la segunda consulta por orden; si `get_formatted_total()` requiere el objeto completo, dejarlo como está — el volumen (máx. 3) no justifica una refactorización.

---

### Nota positiva: importación de CSV con idempotencia correcta

`vc-onboarding-wizard.php`, funciones `vc_ow_seed_countries_from_csv()`, `vc_ow_seed_states_from_csv()` y `vc_ow_maybe_install_locations_table()` (líneas 113-283): la importación de `data/countries.csv` (251 filas) y `data/states.csv` (5.309 filas) a la tabla `wp_vc_locations` está protegida por un `get_option('vc_ow_countries_csv_imported') === $csv_mtime` (y su equivalente para states), enganchado a `plugins_loaded` mediante `vc_ow_maybe_install_locations_table()`. Esto garantiza que el CSV completo solo se abre, parsea fila por fila (`fgetcsv`) e inserta (`vc_ow_insert_location_if_missing()`, que además verifica existencia antes de insertar) **una sola vez por versión de archivo**, nunca en cada request normal. Es exactamente el tipo de protección que le faltaba a H-01/H-02 aplicada a nivel de importación, y se documenta como buen patrón a replicar.

También se revisaron completos los 3 módulos JS (`dashboard.js`, `onboarding.js`, `login.js`): todas las referencias DOM se resuelven una vez al inicio de cada función de inicialización, no hay `setInterval`/`setTimeout` de polling (solo dos `setTimeout` de disparo único en `login.js` para revalidar el estado del botón tras el autofill del navegador, sin repetición), y no se detectó *layout thrashing* (lecturas de `offsetWidth`/`getBoundingClientRect` intercaladas con escrituras de estilo).

---

## 3. Top 8 problemas priorizados

1. **H-01** — `get_profile_states()` escanea las 5.309 filas completas de la tabla de ubicaciones sin `WHERE` ni caché, en cada render y cada guardado de Perfil.
2. **H-06** — Imagen de fondo `onboarding-left.png` de 897 KB sin variante WebP/responsive, probable mayor contribuyente al LCP del embudo de registro/login; más el duplicado muerto `onboarding-left.jpg` (912 KB) que solo pesa en el deploy.
3. **H-07** — Sin paso de build/minificación en `.github/workflows/deploy.yml` (~154 KB de CSS y ~29 KB de JS sin minificar).
4. **H-03** — `onboarding.js` + `dashboard.js` se encolan en absolutamente cualquier página del sitio (`is_page()`), no solo en las páginas del wizard/dashboard.
5. **H-02** — `get_profile_location_label()` suma hasta 2 consultas adicionales por guardado de perfil, encima de H-01.
6. **H-04** — Google Fonts cargado con `<link rel="stylesheet">` bloqueante (parcialmente mitigado por `preconnect` y `display=swap`).
7. **H-05** — CSS insertado con `<link>` manual en vez de `wp_enqueue_style()`, fuera del sistema estándar de optimización de WordPress.
8. **H-08** — N+1 acotado (máx. 3 consultas) en `render_dashboard_subscription_view()`.

---

## 4. Plan de optimización por fases

### Fase rápida (horas)
- Condicionar `enqueue_public_assets()` a las páginas reales del wizard/dashboard en vez de `is_page()` genérico (H-03).
- Eliminar `onboarding-left.jpg` si se confirma que no se usa, o documentar por qué se conserva (parte de H-06).
- Mover los `<link>` manuales de CSS a `wp_enqueue_style()` (H-05).

### Fase media (días)
- Cachear `get_profile_countries()`/`get_profile_states()` con `set_transient()` invertido a la versión del CSV importado, y filtrar por país en SQL en vez de en PHP dentro de `handle_save_account_profile()` (H-01, H-02).
- Recomprimir/convertir `onboarding-left.png` a WebP y evaluar tamaños adicionales para móvil (H-06).
- Agregar un paso de build (esbuild/Terser + cssnano) al pipeline de deploy (H-07).
- Cambiar la carga de Google Fonts a un patrón asíncrono o auto-hospedar los `.woff2` de Poppins (H-04).

### Fase avanzada (estructural, requiere pruebas cuidadosas)
- Evaluar si `render_dashboard_subscription_view()` puede evitar la segunda consulta por orden reutilizando los datos de `MemberOrder::get_orders()` (H-08) — baja prioridad dado el volumen acotado.

---

## 5. Mejora estimada

Estas cifras son estimaciones cualitativas basadas en los hallazgos concretos de este análisis, no mediciones de un profiler real (Query Monitor, WebPageTest) sobre el sitio en producción. Se recomienda validar con mediciones reales antes/después de cada cambio.

- **TTFB de la vista "Profile" del dashboard y de cada guardado de perfil:** al cachear `get_profile_countries()`/`get_profile_states()` y filtrar por país en SQL (H-01, H-02), las consultas contra `wp_vc_locations` pasarían de escanear ~5.500 filas a leer de un transient o, en el peor caso sin caché, a un `SELECT` acotado por índice de unas pocas decenas de filas. Reducción esperada en esa porción específica del tiempo de generación: probablemente de 3x a 10x, ya que se elimina tanto el escaneo completo de la tabla como la transferencia y el filtrado en PHP del 99% de filas irrelevantes.
- **LCP de las páginas de registro/login:** convertir `onboarding-left.png` a WebP (H-06) es, de los 8 hallazgos, el que tiene mayor potencial de impacto en Core Web Vitals percibidos por el usuario, dado que es una imagen visible de inmediato (above the fold) de casi 900 KB. Una reducción de 70-90% en el peso de esa imagen específica debería traducirse directamente en una mejora proporcional del tiempo hasta LCP en conexiones no-broadband.
- **Peso de red en cada página del sitio (no solo del plugin):** condicionar `enqueue_public_assets()` a las páginas reales del wizard (H-03) elimina ~20 KB sin comprimir de JS y su ejecución en páginas del sitio que no tienen relación con onboarding — no dramático, pero 100% de ahorro donde antes era 100% desperdicio, igual que el hallazgo equivalente en `vc-flashcards`.
- **Peso de transferencia de CSS/JS del plugin:** minificar (H-07) debería reducir el peso adicional entre 20-35% sobre lo que ya logra la compresión del servidor, más relevante en `dashboard.css` (88 KB) que en el resto.
- **Primer render con tipografía correcta en el embudo de onboarding:** cargar Google Fonts de forma asíncrona o auto-hospedada (H-04) elimina la espera de una respuesta de red externa antes de completar el CSSOM, con mejora perceptible sobre todo en redes lentas o cuando la CDN de Google Fonts tiene latencia elevada.

En conjunto, el plugin no tiene deuda de rendimiento estructural: los 8 hallazgos son ajustes localizados y de bajo riesgo de regresión (caché de datos estáticos, condicionar un enqueue, optimizar una imagen, agregar un paso de build), no una reescritura arquitectónica.
