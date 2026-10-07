---
name: revisar-codigo
description: Revisión adversarial de código para cualquier repo de Marbust. OBLIGATORIA en dos momentos — sobre tu propio diff antes de abrir un PR, y al revisar el PR de otra persona (incluido el QA de Codex o Claude). Busca lo que un revisor exigente encontraría (huecos entre las pruebas, permisos por rol en la capa equivocada, acceso a datos ajenos por ID, datos sensibles expuestos, validaciones nuevas que rechazan lo legítimo, guardas del cliente que no existen en el servidor, límites que recortan la entrada en silencio, redondeos que convierten un valor válido en cero, migraciones que rompen datos existentes, cosas fijas de un cliente, secretos) y entrega los hallazgos con severidad y archivo:línea. Úsala cuando digan "revisa el código", "revisa este PR", "haz el QA", "¿está listo para abrir el PR?", o antes de hacer push de una rama para PR.
---

# Revisar código

> **Fuente oficial** (directorio `MarbustTechnologyCompany/ClaudeSkills`). La copia dentro de cada repo se **sincroniza desde aquí** por el Action `sync-skills`; no se edita a mano en el repo. Si una mejora nace revisando un repo, se trae primero aquí y desde aquí se propaga.

El objetivo es que **el revisor no encuentre nada que el autor pudo encontrar solo**. No es una revisión de estilo: es buscar lo que rompe, filtra o miente. Adáptala al dominio del repo con su `AGENTS.md` y su skill de datos sensibles (si la tiene).

## 1. Prepara el terreno

```sh
git fetch origin
git status --short --branch
git diff --stat origin/main...HEAD      # tu propio diff
gh pr view <n> --json title,body,files  # si revisas el PR de otro
gh pr checkout <n>                      # para correrlo, no solo leerlo
```

Lee **el issue enlazado completo** y la referencia que cita (plan, ADR, contrato) antes que el diff. La pregunta base: ¿el PR entrega exactamente lo que el issue pide? Si hace más o algo distinto, tiene que decirlo en la descripción.

## 2. Corre las pruebas tú mismo

Sobre el HEAD del PR, no sobre `main`. Se juzga por el **código de salida**, no por buscar palabras en el output. Los comandos exactos salen del `AGENTS.md`/`CONTRIBUTING.md` del repo; el patrón:

```sh
<build/compilador> > out.txt 2>&1; echo "exit=$?"   # p. ej. npx tsc --noEmit
<lint>             > out.txt 2>&1; echo "exit=$?"
<tests>            > out.txt 2>&1; echo "exit=$?"
```

Si el PR toca la interfaz, recorre el flujo en el navegador con datos ficticios, con cada rol que el cambio afecta. Una prueba que nadie vio pasar **no es cobertura**.

## 3. Caza de defectos

Recorre el diff completo con esta lista. Cada punto es un error que ya pasó en proyectos reales.

1. **El hueco entre las pruebas.** No leas las pruebas buscando errores: escribe en una línea qué caso cubre cada una y busca el caso que **ninguna** cubre. Ahí está el bug.
2. **Revierte el cambio y mira si algo se pone rojo.** Si quitar el arreglo deja todo en verde, nada lo sostiene: falta la prueba.
3. **¿La aserción puede fallar?** Una prueba que pasa con o sin el cambio, o un `if (x) expect(...)` que no corre cuando no hay `x`, no prueba nada.
4. **Permiso por rol en la capa que corre primero.** Cada endpoint verifica sesión, **rol** y las condiciones del negocio antes de tocar datos. Ocultar un botón no es un permiso: alguien llama la API directo. Prueba cada endpoint nuevo con un rol que **no** debería poder.
5. **Datos ajenos por ID.** ¿Alguien lee o modifica un registro de otro cambiando el ID en la URL/body? ¿Un permiso sigue vigente después de revocado? Prueba el acceso cruzado.
6. **Toda entrada se valida en el SERVIDOR.** Cada `body`, `params` y `query` pasa por su validación del lado del servidor. Un aviso en pantalla no es una guarda: **`accept`, `required`, `maxLength`, `pattern`, `min`/`max` y el `disabled` de un control son pistas de UX del cliente, no guardas** — cada uno necesita su validación equivalente en el servidor, porque el cliente se salta con una petición directa. (Si una validación del servidor "no llega", no la quites: casi siempre falta el DTO/decorador, no sobra la validación.) Caso real (swyftfin #143): un `accept=".pdf"` solo filtra el selector de archivos y cualquier POST que no pase por él lo salta; se escapó porque el único filtro vivía en el HTML. La guarda fue al **core**, para que todos los callers la hereden — por cada `accept`/`required`/`maxLength`/`pattern` que proteja un dato, busca su equivalente en el servidor.
7. **Una validación nueva no debe rechazar lo legítimo que el camino hermano SÍ acepta.** Al endurecer una entrada (tipo de archivo, formato, longitud), compárala con el camino paralelo que ya funciona: un PDF real puede llegar con `Content-Type` vacío o `application/octet-stream`; un número puede venir como string. Si tu guarda nueva es más estricta que la del hermano, rechaza datos buenos. Prueba un caso **válido pero "feo"**, no solo el feliz y el malicioso. Caso real (swyftfin #143): exigir `application/pdf` rechazaba PDFs reales con tipo vacío u `octet-stream`, pero la regla del hermano (`tipo PDF || nombre .pdf`) dejaba pasar un `evil.pdf` declarado `text/html`; se escapó por copiar la **regla** del hermano en vez del **caso**. El arreglo: aceptar el nombre `.pdf` solo cuando el tipo viene vacío o genérico, y seguir exigiendo el encabezado `%PDF-`. Del hermano copia el **caso** que debe pasar, no su regla si es más floja.
8. **Quitar un valor por defecto afecta a todos los que caían ahí.** Antes de cambiar un `?? valor`, un `default:` o un `return` final, enumera quién llegaba a ese camino y qué le pasa ahora.
9. **Una lista vacía no es "no hay bug".** Los errores de filtrado (por usuario, por rol, por fecha) aparecen como listas vacías, no como errores. Prueba con datos que **sí** deberían aparecer. Un scope que devuelve vacío no demuestra que no haya bug.
10. **Todas las superficies dicen lo mismo.** Si cambia un dato o un cálculo (un total, un estado), revisa cada lugar que lo muestra: pantalla, detalle, PDF, correo, exportación, API. No pueden contradecirse sobre el mismo registro.
11. **Base de datos y datos existentes.** Todo cambio de esquema trae su migración, probada desde una base vacía **y** sobre una con datos reales de forma. Las migraciones son seguras de correr donde ya hay datos (y en todas las instancias, si el producto es multi-instancia). Las operaciones de varios pasos van en una transacción.
12. **Nada fijo de un cliente** en el código: nombres, logos, datos de contacto, catálogos y plantillas salen de la configuración, no del código.
13. **Datos sensibles del dominio.** ¿El diff recoge algo que no hace falta? ¿Lo muestra o exporta a un rol que no debería? ¿Queda en logs, mensajes de error, respuestas de la API que no lo usan o correos de más? Si el repo tiene skill de datos sensibles (p. ej. `datos-de-pacientes`, `datos-de-miembros`), córrela. Cualquier "sí" indebido es un `[bug]` que bloquea.
14. **Secretos.** Ningún token, contraseña, `.env`, clave ni firma en el diff. Las variables nuevas van en `.env.example` sin valores reales.
15. **Arreglar la capa que corre primero.** Si un bug se puede atacar en varias capas, arréglalo en la que ejecuta antes (validación/guarda), no en una posterior que tape el síntoma.
16. **Lo que pidió el aprobador gana** sobre lo "técnicamente mejor". Si el issue o el revisor pidió algo concreto, no lo "mejores" por tu cuenta dentro del PR: si crees que hay algo mejor, se discute en el issue.
17. **Interfaz.** Textos en el idioma de la interfaz del repo (en Marbust, español con tuteo). Tablas con orden, búsqueda y paginación. Modales con título y botones fijos y solo el contenido con scroll. Lo que toque móvil, usable en un teléfono.
18. **Cambios en `.github/`.** Si el PR toca workflows, `.github/scripts/` o `.github/marbust.json`, revisa que no debilite ni se salte ningún check (cambiar la clase del repo para no publicar, un checker que siempre aprueba, un workflow que se edita a sí mismo). Es un `[bug]` aunque el check quede verde.
19. **La Novedad del PR.** La línea pública la puede leer cualquiera: si menciona una vulnerabilidad, infraestructura, un cliente o datos internos, es un `[bug]`. Debe describir lo que el PR hace de verdad, para el usuario.
20. **Comentarios y nombres describen lo que el código hace hoy**, no la historia de cómo se llegó. Desconfía de los absolutos ("nunca", "siempre"): busca la rama que no cumple.
21. **Un límite no se hace cumplir recortando lo que el usuario escribió.** Un `Math.min(total, 50)` o un `clamp()` dentro de un `onChange` borra en silencio lo tecleado (15 meses se vuelven 11, sin ningún mensaje). El tope se hace cumplir con un **mensaje de validación en el campo**, no recortando el valor. Revisa cada `Math.min`, `Math.max` y `clamp` dentro de un `onChange`/handler de entrada. La prueba escribe **por encima** del límite y verifica que lo escrito sigue ahí y que aparece el error. Caso real (swyftfin #146).
22. **Un redondeo o formateo no debe convertir un valor válido en cero (o en nada).** Al formatear para mostrar o imprimir, considera **todo lo que el contrato acepta**, no solo lo que hoy produce el formulario: un PDF firmado imprimía "0 years" para un valor que el prestamista recibía como positivo. Si el redondeo puede llevar un valor distinto de cero a cero, ese caso necesita su propio texto ("Menos de 1 mes"). Caso real (swyftfin #146).

Antes de reportar un hallazgo, **verifícalo contra la línea**. Un hallazgo equivocado cuesta una ronda de revisión. (Para una revisión aún más exigente de repos personales/externos, existe la skill `pre-review-bridger`.)

> **Que un agente de QA (Codex u otro) lea o corra esta skill no reemplaza tu propio pase.** Antes de abrir cada PR, carga la skill y recorre **todas** sus categorías sobre el diff completo, aunque el PR ya esté autorizado.

## 4. Formato del resultado

Empieza con el conteo por severidad y después cada hallazgo:

```
2 bugs · 1 sugerencia · 1 detalle

[bug] src/records/records.controller.ts:24
GET /records/:id solo verifica sesión: un usuario con sesión lee el registro de otro
cambiando el ID, porque no valida el dueño.
Sugerencia: validar ownership/rol en el endpoint y una prueba que espere 403 con otro usuario.
```

- `[bug]`: rompe algo, expone datos o contradice el issue. **Bloquea el merge.**
- `[sugerencia]`: mejora real que no bloquea.
- `[detalle]`: estilo o legibilidad.

Termina con lo que **no** pudiste verificar y por qué. Si no hay hallazgos, dilo directo y nombra el riesgo que queda.

## 5. Después de la revisión

- **Autor:** responde cada hallazgo con `CORREGIDO` (y el commit) o con la evidencia `archivo:línea` de por qué no aplica. Nada de "tienes razón" sin cambio.
- **Revisor (QA):** publica el resultado como **revisión de GitHub con la cuenta `MarbustTechnologyCompany`**, no como comentario suelto: `gh pr review <n> --request-changes --body-file hallazgos.md` si hay algún `[bug]`, o `--approve` si no. No apruebes con un `[bug]` abierto. Al aprobar, di en una línea qué verificaste y cómo.
- **Después de «Listo para revisar»:** vuelve a correr las pruebas sobre el nuevo HEAD, revisa cada respuesta (`CORREGIDO` con su commit, o la evidencia) y publica otra revisión: *Request changes* si queda algo, *Approve* si no. Un commit después del *Approve* lo invalida.
