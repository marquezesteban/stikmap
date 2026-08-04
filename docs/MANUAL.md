# Manual de uso de StikMap

## Canciones y audio

Una canción necesita un título. El artista, la letra y el archivo MP3 son opcionales y pueden agregarse o modificarse después.

El MP3 habilita la forma de onda, el reproductor y la creación de marcas temporales.

## Letra

La letra conserva sus líneas y renglones vacíos. Desde **Editar datos** se puede pegar el texto completo y agregar secciones musicales en la posición actual del cursor.

## Secciones entre corchetes

StikMap interpreta una línea como sección musical cuando:

1. La anotación ocupa una línea completa.
2. Comienza con `[` y termina con `]`.
3. El texto interior tiene entre 1 y 80 caracteres.

Ejemplos reconocidos:

```text
[INTRO]
[VERSO 1]
[ESTRIBILLO]
[INSTRUMENTAL CON FILL]
[CORTE DE BANDA]
[FINAL]
```

Estos casos no se interpretan como secciones:

```text
Comienza el [VERSO 1]
[INTRO
ESTRIBILLO]
[]
```

Los corchetes se conservan en el editor. En la vista de lectura, StikMap muestra la sección como un encabezado destacado y sin los corchetes.

El selector **Agregar sección** ofrece Intro, Verso, Pre-estribillo, Estribillo, Puente, Instrumental, Solo, Corte, Interludio y Final. También se puede escribir cualquier anotación personalizada respetando la misma regla.

Al insertar **Verso**, StikMap busca las etiquetas existentes y propone automáticamente `[VERSO 1]`, `[VERSO 2]` y las siguientes.

## Asociar una sección o línea con una marca

Al crear o editar una marca, el campo **Línea de letra** distingue:

- `SECCIÓN · VERSO 1`: anotación estructural o pasaje instrumental.
- `LETRA 12 · El problema es que te espero`: línea cantada y su posición original.

La asociación es opcional. Una marca siempre conserva su tiempo, tipo y nota aunque no tenga una referencia de letra.

Si una línea o sección mantiene exactamente el mismo texto y cambia de posición, StikMap conserva la asociación. Si se elimina o se modifica su texto, la marca queda sin esa referencia, pero no se elimina ni pierde sus demás datos.

## Marcas

El botón **Marcar ahora** captura el instante actual sin detener automáticamente el audio. Después se puede elegir el tipo, corregir el tiempo con milisegundos, escribir una nota y asociar una línea o sección.

Tocar una marca del listado o su punto de color lleva el reproductor a ese instante. Desde el mismo punto se puede abrir su edición.
