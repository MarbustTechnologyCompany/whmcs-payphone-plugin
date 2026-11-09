---
name: trabajar-un-issue
description: Flujo completo para tomar un issue de cualquier repo de Marbust y entregarlo — elegir uno disponible, confirmar que se autocontiene, tomarlo, crear la rama, implementar solo el alcance, verificar ejecutando, revisar con la skill revisar-codigo, pasar el QA, abrir el PR con la plantilla y responder la revisión hasta el squash. Úsala cuando digan "toma el issue #N", "trabaja el issue", "soy nuevo, ¿por dónde empiezo?", o al empezar cualquier cambio en el repo.
---

# Trabajar un issue

> **Copia sincronizada:** la fuente es el directorio de skills de la organización (lo indica el `AGENTS.md` del repo). El Action `sync-skills` la copia a cada repo; no se edita a mano en el repo.

Si es tu primer día en el proyecto, lee antes `docs/ONBOARDING.md` del repo y vuelve aquí para el flujo de cada issue. Los detalles del stack, los comandos y las reglas del dominio salen del `AGENTS.md`/`CONTRIBUTING.md` del repo.

## 1. Elegir

```sh
gh issue list --state open --search "no:assignee -label:bloqueado -label:necesita-diseño"
gh issue view <n>
```

Un issue está disponible si **no tiene a nadie asignado**, **no tiene `en-progreso`**, **no tiene `bloqueado` ni `necesita-diseño`**, y todo lo que dice en sus dependencias está cerrado. **Un issue a la vez por persona**, y nunca se trabaja un issue asignado a otra.

## 2. Confirmar que se autocontiene

Lee el issue completo, la referencia que enlaza (plan/ADR/contrato) y los archivos que menciona. Antes de escribir código, responde:

- ¿Sé qué tengo que entregar y cómo se comprueba?
- ¿Sé qué **no** entra?
- ¿Sé qué roles pueden hacer qué, y qué datos sensibles toca?
- ¿Hay alguna decisión que tendría que adivinar?

Si algo falta, **pregunta en el issue**, no por chat, y espera a que el issue se corrija. Así la respuesta queda para el siguiente.

## 3. Tomarlo

```sh
gh issue comment <n> --body "Lo tomo."
```

Quien mantiene el repo te lo asigna y le pone `en-progreso`. **Espera la asignación antes de programar.** Mueve la tarjeta de Trello del issue a *In Progress*, comenta tus avances en el issue, y si no puedes seguir, dilo para que lo liberen.

## 4. Rama y PR en borrador

Rama siempre desde `main` actualizado, con el tipo y el número del issue; y dentro de las 48 horas el **PR en borrador** (que necesita la rama ya empujada):

```sh
git fetch origin
git switch -c feat/<n>-titulo-corto origin/main     # o fix/, docs/, chore/, refactor/, test/, perf/
git push -u origin feat/<n>-titulo-corto
gh pr create --draft --body "..."                    # con Refs #<n> y la plantilla completa
```

## 5. Implementar

- **Solo el alcance del issue.** Si encuentras otro problema, abre una tarjeta y un issue nuevos (skill `escribir-un-issue`) y sigue con el tuyo.
- Respeta el `AGENTS.md`: stack, idiomas, glosario, reglas del producto, seguridad e interfaz.
- Si tocas datos sensibles, aplica la skill de datos del repo (p. ej. `datos-de-pacientes`) **mientras diseñas**, no al final.
- Si cambias el esquema, la migración tiene que servir para una base vacía **y** para una con datos reales de forma.
- Commits pequeños, con tipo y en español: `feat: agenda por doctor con tipos de cita`. **Sin líneas de co-autoría de herramientas de IA.**

## 6. Verificar ejecutando

Corre el compilador, el lint y los tests del repo, y recorre el flujo real con **datos ficticios** (escritorio, y teléfono si tocaste algo móvil). Si el cambio depende de una condición del negocio (licencia, estado, permiso), pruébalo en sus variantes. **Guarda el output: va pegado en el PR.** Verificar es ejecutar, no leer.

## 7. Revisar antes del PR

Corre la skill `revisar-codigo` sobre tu diff y resuelve lo que encuentre. **Es obligatorio.**

## 8. QA

**Codex participa siempre:** si tú (o Claude) implementaste, el QA lo hace Codex; si implementó Codex, lo hace Claude. Revisa una IA **distinta** de la que escribió el código (ver `revisar-codigo` §5, *Quién revisa*). El QA corre las pruebas sobre el HEAD del PR, **en local y antes de pedir la revisión externa** si el repo la tiene, y publica su revisión con la **cuenta revisora** del repo (la que indica su `AGENTS.md`; nunca la del autor), con el formato de `revisar-codigo`:

```sh
GH_TOKEN=<token de la cuenta revisora> gh pr review <n> --request-changes --body-file hallazgos.md
# o, sin hallazgos:
GH_TOKEN=<token de la cuenta revisora> gh pr review <n> --approve --body "<qué verificó y cómo>"
```

## 9. Abrir el PR para revisión

```sh
git push -u origin <rama>
gh pr ready <n>
```

La plantilla completa:
- `Closes #<n>` (en inglés, para que GitHub cierre el issue).
- **Cómo probar**, paso a paso, con el rol y el entorno.
- **Novedad:** línea interna siempre; en repos de clase `producto` además la línea pública (para el usuario; nada de vulnerabilidades ni datos internos) e hito. `ninguna` si no aplica. El check `Checks del PR` la exige según la clase.
- Verificación con el output pegado. Marca solo lo que corriste; lo que no aplica, N/A con el motivo.
- Si hiciste algo más o distinto de lo que pedía el issue, dilo en el resumen.

## 10. Responder la revisión

Cada hallazgo se contesta en el PR con `CORREGIDO` (y el commit) o con la evidencia `archivo:línea` de por qué no aplica. Nada de "tienes razón" sin cambio. Cuando terminas:

```sh
gh pr comment <n> --body-file respuesta.md   # termina con "Listo para revisar"
```

El QA vuelve a revisar y publica otra vez *Request changes* o *Approve*. El ciclo se repite hasta el *Approve*. **Un commit después del *Approve* lo invalida:** vuelves a comentar «Listo para revisar».

## 11. Cierre

Con el *Approve* de la cuenta revisora sobre el último commit, y la herramienta externa en **5/5** si el repo la tiene, el merge es **squash**; la rama se borra sola. Sin eso no se mergea. Después:

```sh
git switch main && git pull
git branch -d <rama>
```

La tarjeta pasa a la columna de verificación del tablero (la que indique el `AGENTS.md` del repo) con el "cómo probar". **La cierra el responsable del producto**, no quien la trabajó. El despliegue sigue las reglas del repo (auto-deploy al mergear, o con el OK de quien aprueba producción).
