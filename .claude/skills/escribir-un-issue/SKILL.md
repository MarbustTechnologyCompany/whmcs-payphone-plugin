---
name: escribir-un-issue
description: Cómo escribir un issue AUTOCONTENIDO para cualquier repo de Marbust — que alguien que no estuvo en ninguna conversación pueda tomarlo y entregarlo sin preguntar. Incluye la regla, qué debe llevar, la comprobación final, los antipatrones, el tamaño correcto y las etiquetas. Úsala al crear un issue nuevo, al dividir una propuesta de módulo o una sección del plan en tareas, o cuando alguien pregunta algo en un issue que el issue debió responder (entonces se corrige el issue).
---

# Escribir un issue

> **Fuente oficial** (directorio `MarbustTechnologyCompany/ClaudeSkills`). La copia dentro de cada repo se **sincroniza desde aquí** por el Action `sync-skills`; no se edita a mano en el repo.

## La regla

> **El issue se autocontiene.** Quien lo tome tiene que poder terminarlo leyendo solo el issue, la referencia que enlaza y los archivos que menciona, sin preguntar nada a nadie.

Quien lo toma no estuvo en la conversación, puede no ver el tablero de Trello y no leyó el chat. Lo que no está escrito en el issue, para esa persona no existe.

## Antes de escribirlo

1. **Tarjeta en Trello** (el tablero del repo, con marca + severidad + rol + ejecutor).
2. El issue lo abre la cuenta **`MarbustTechnologyCompany`**, con una plantilla: *Work item*, *Reporte de bug* o *Propuesta de módulo*.

## Qué tiene que llevar

| Sección | Pregunta que responde |
|---|---|
| Resultado | ¿Qué cambia cuando esté terminado, y para quién (qué rol/usuario)? |
| Alcance | ¿Qué entra? |
| Exclusiones | ¿Qué parece que entra pero no? |
| Especificación (dentro de *Alcance*) | Datos, campos, reglas de negocio con sus números y casos borde, pantallas, rutas, **permisos por rol** y comportamiento en las condiciones del negocio |
| Datos sensibles | ¿Cuáles toca, quién los ve, cómo se protegen? ("No aplica" si no toca) |
| Criterios de aceptación | ¿Qué se puede observar para decir "listo"? |
| Verificación | ¿Con qué comandos o pasos se demuestra? Para trabajo sensible (dinero, impuestos, identidad, proveedores), lista los **gates externos por separado**: un PR mergeado no prueba un deploy, una migración, un smoke autenticado ni un UAT |
| Contexto y referencias | Sección del plan/ADR, decisiones tomadas, dependencias, la tarjeta de Trello |

**Textos de la interfaz:** escríbelos en el issue, en el idioma del producto (en Marbust, español con tuteo).

## Comprobación final

Léelo como si no supieras nada del proyecto:

- [ ] ¿Está de acuerdo con el plan/ADR? Si lo contradice, primero se cambia el plan (con su PR).
- [ ] ¿Podría empezar a programar ahora mismo, sin preguntar?
- [ ] ¿Cada regla de negocio está escrita con sus números y casos borde?
- [ ] ¿Dice qué puede hacer cada rol, y qué pasa en las condiciones del negocio (suspendido, vencido, sin permiso)?
- [ ] ¿Cada criterio de aceptación se puede comprobar mirando algo?
- [ ] ¿No depende de algo privado como única fuente (un chat, una llamada, una tarjeta)?
- [ ] ¿No hay datos reales de clientes/personas ni secretos?
- [ ] ¿Cabe en un solo PR que alguien pueda revisar en una sentada?

## Antipatrones

| Así no | Así sí |
|---|---|
| "Como lo hablamos" | La decisión escrita en el issue o en el plan, con su sección |
| "Hacer la agenda" | Un issue por resultado: tipos, horario, agendar, cancelar, recordatorios |
| "Arreglar el cálculo" | El caso que falla, el esperado y el real |
| Criterio "funciona bien" | "Con 3 ítems de 19.50, el total muestra 58.50 y el IVA 8.78" |
| "El usuario X puede ver lo necesario" | "El rol recepcionista ve nombre, cédula y citas; `GET /records/:id` le responde 403" |

## Tamaño

Un issue = **un resultado** que se entrega y se revisa solo. Señales de que hay que dividirlo: la especificación pasa de una pantalla, tiene más de un "y además", o mezcla modelo de datos, API e interfaz de dos funcionalidades distintas.

## Ideas que todavía no se pueden especificar

Si faltan decisiones, **no** es un *Work item*: usa **Propuesta de módulo** (etiquetas `módulo-nuevo` y `necesita-diseño`). Cuando sus preguntas se responden, se agrega al plan y se divide en work items autocontenidos que la propuesta enlaza.

## Etiquetas

Las plantillas ponen solas `bug`, `enhancement` o `módulo-nuevo` + `necesita-diseño`. El resto las agrega quien crea o mantiene el issue: `documentation`, `datos-sensibles`, `infraestructura`, `bloqueado`, `en-progreso`, y las del dominio del repo.

## Cuando alguien pregunta en un issue

Responde **y corrige el cuerpo del issue** con la respuesta. Si solo contestas en un comentario, el siguiente que lo lea vuelve a tener la misma duda.
