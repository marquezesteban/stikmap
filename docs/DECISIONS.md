# Decisiones de producto y técnicas

Este registro evita reabrir decisiones sin contexto. Pueden revisarse si aparece evidencia nueva, dejando anotado el motivo.

## D-001 — Producto local y sin usuarios

**Decisión:** el MVP funciona sobre XAMPP, sin login, cuentas ni servicios en la nube.

**Motivo:** validar primero la herramienta musical y mantener instalación, datos y operación simples.

## D-002 — Interfaz mobile-first con identidad propia

**Decisión:** Bootstrap se usa como base responsive y de accesibilidad, mientras que los componentes visibles reciben estilos propios de StikMap.

**Motivo:** la aplicación debe sentirse como una herramienta musical táctil, no como un panel administrativo genérico.

## D-003 — Tiempo almacenado en milisegundos

**Decisión:** las marcas usan enteros en milisegundos, no números decimales en segundos.

**Motivo:** simplifica comparaciones, ordenamiento y persistencia sin errores de punto flotante.

## D-004 — Sprints cerrados antes de ampliar alcance

**Decisión:** cada sprint se implementa, prueba, documenta y valida antes de comenzar el siguiente.

**Motivo:** conservar la finalidad del producto y evitar funciones incompletas o mejoras que distraigan del MVP.

## D-005 — MP3 locales con nombres internos aleatorios

**Decisión:** los archivos se guardan en `public/uploads/`, fuera de Git, conservando el nombre original sólo como metadato.

**Motivo:** evitar colisiones, exposición de nombres inseguros y crecimiento accidental del repositorio.

## D-006 — Dependencias sin proceso de compilación

**Decisión:** Bootstrap 5.3 y WaveSurfer.js 7 se cargan desde CDN durante el MVP.

**Motivo:** mantener el stack solicitado sin Node.js ni bundler. Antes del cierre del MVP se evaluará empaquetarlas localmente para uso sin Internet.

## D-007 — Las marcas son instantes, no regiones

**Decisión:** una marca representa un único instante temporal. No tiene duración ni se arrastra sobre la forma de onda.

**Motivo:** el objetivo inicial es anticipar entradas, cortes y cambios, no editar segmentos de audio.

## D-008 — StikMap como identidad definitiva

**Decisión:** el producto y el repositorio adoptan el nombre StikMap antes de comenzar el Sprint 3.

**Motivo:** combina `stick` y `map`, se pronuncia de forma similar en español e inglés y describe una herramienta de mapas para bateristas sin depender de un término genérico completo.

## D-009 — AGPL para el código y política separada para la marca

**Decisión:** el código se publica bajo GNU AGPL v3. El nombre, el logotipo y la identidad visual no se conceden bajo esa licencia.

**Motivo:** permitir uso y mejoras open source, exigir que las versiones web modificadas ofrezcan su código y evitar que un fork se presente como una versión oficial de StikMap.

## D-010 — Autoría y licencia visibles

**Decisión:** todas las pantallas incluyen un pie discreto con la atribución `Diseñado y creado por Esteban Marquez · © 2026`, el correo `marquezesteban@gmail.com` y un enlace a la licencia GNU AGPL v3.

**Motivo:** conservar la autoría y hacer que las condiciones del código sean fáciles de encontrar sin interferir con el uso de la aplicación.

## D-011 — Versionado automático de recursos locales

**Decisión:** las URLs de CSS y JavaScript propios incluyen la fecha de modificación del archivo como versión.

**Motivo:** evitar que navegadores de escritorio, tablets o teléfonos conserven una interfaz anterior después de una actualización local.

## D-012 — Versionado del producto durante el MVP

**Decisión:** StikMap usa versionado semántico desde `v0.3.2`. Cada sprint principal incrementa la versión menor, las mejoras o correcciones incrementan el parche y `v1.0.0` queda reservado para el MVP completo, probado y documentado.

**Motivo:** identificar con claridad qué avance está instalado, mantener un historial entendible y facilitar futuras actualizaciones.

## D-013 — Letra completa antes de asociar líneas

**Decisión:** la entrega 4.1 guarda la letra completa en la canción y la presenta respetando su formato. La selección de líneas desde las marcas se implementa por separado en la entrega 4.2.

**Motivo:** probar primero el ingreso, la persistencia y la lectura en pantallas táctiles antes de sumar relaciones entre letra y mapa temporal.

## D-014 — Asociación de marcas por identidad de línea

**Decisión:** cada línea no vacía de la letra tiene una identidad interna y una posición visible. Las marcas guardan opcionalmente esa identidad. Al editar la letra, las líneas con el mismo texto conservan su identidad aunque cambien de posición; las que desaparecen se desvinculan sin eliminar la marca.

**Motivo:** evitar que insertar un verso al comienzo mueva silenciosamente las referencias y proteger siempre el mapa temporal del baterista.

## D-015 — Secciones musicales explícitas en la letra

**Decisión:** las anotaciones escritas en una línea propia entre corchetes, como `[VERSO 1]` o `[INSTRUMENTAL CON FILL]`, se consideran secciones musicales. El editor ofrece diez nombres frecuentes, pero las etiquetas continúan siendo texto editable.

**Motivo:** representar entradas, fills y pasajes sin letra sin intentar adivinar automáticamente la estructura de cada canción.

## D-016 — Cancionero anotado como vista de impresión

**Decisión:** la impresión usa la letra completa como eje y coloca cada marca encima de la línea o sección asociada. Las marcas sin asociación se informan aparte y las canciones sin letra conservan una lista cronológica alternativa.

**Motivo:** permitir que el baterista siga la canción leyendo y encuentre cada indicación en contexto, sin depender de recordar constantemente el minuto y segundo actual.
