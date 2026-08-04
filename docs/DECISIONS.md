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
