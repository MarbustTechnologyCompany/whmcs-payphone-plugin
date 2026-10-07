# Reglas del repositorio — WHMCS PayPhone Gateway

Este archivo es para **todas las personas y agentes de IA** que trabajan en esta pasarela (Claude Code, Codex, Cursor, Copilot u otros). Si usas Claude Code, se carga solo a través de `CLAUDE.md`.

- Si es tu primer día: [`docs/ONBOARDING.md`](docs/ONBOARDING.md).
- Cómo colaborar paso a paso: [`CONTRIBUTING.md`](CONTRIBUTING.md).
- Qué hace y cómo se instala: [`README.md`](README.md) (bilingüe) · historial: [`CHANGELOG.md`](CHANGELOG.md).
- El estándar común de todos los repos de Marbust: `MarbustTechnologyCompany/.github` → `ESTANDAR-REPOSITORIOS.md`.

**Clase del repo: producto de la empresa** (`.github/marbust.json`), **público y open-source (MIT)**. Puede publicar novedades (notas de versión); ver *Novedades* en `CONTRIBUTING.md`.

**Qué es:** una pasarela de pago **PayPhone** (Ecuador) **gratuita y open-source** para **WHMCS** (7.x–9.x, PHP 7.x/8.x). Cobra facturas con el flujo oficial **Prepare → redirección → Confirm**, **todo del lado del servidor**: el **token Bearer nunca llega al navegador**. Registra el pago con `addInvoicePayment`, firma el monto con **HMAC-SHA256** y evita duplicados con `checkCbTransID`.

> 🔴 **Dinero y seguridad de pagos.** Es un módulo de cobro en producción de terceros. El token va **solo en el servidor**; el monto va **firmado** (no manipulable); el callback valida HMAC y anti-duplicados. **No debilitar** ninguna de esas defensas. Público: nada de credenciales reales en el repo.

## Stack

| Pieza | Elección |
|---|---|
| Lenguaje | **PHP** (7.x / 8.x), API de módulos de **WHMCS** |
| Archivos | `modules/gateways/payphone.php` (gateway) · `modules/gateways/callback/payphone.php` (Confirm) |
| Flujo | Prepare → redirección a PayPhone → Confirm (server-side) |
| Despliegue | lo instala cada hoster/dev copiando el módulo a su WHMCS (no hay deploy automático) |

## Idiomas

- README **bilingüe** (español Ecuador + inglés), porque es público.
- Código y nombres en inglés; comentarios y commits en español.
- Issues y PRs en español (se puede responder a externos en su idioma).

## Reglas duras

1. **Flujo:** tarjeta → issue (lo abre `MarbustTechnologyCompany`) → rama → PR en borrador → QA → aprobación de la empresa → squash. Nadie hace push directo a `main`. Detalle en [`CONTRIBUTING.md`](CONTRIBUTING.md). (Contribuciones externas: ver CONTRIBUTING.)
2. **El issue se autocontiene.** Skill `escribir-un-issue`.
3. **Revisión obligatoria.** Antes del PR, corre `revisar-codigo`. **Codex participa siempre.**
4. **No debilitar la seguridad de pagos (regla que manda):** token Bearer solo en el servidor; monto firmado HMAC-SHA256; callback con validación y anti-duplicados (`checkCbTransID`). Un cambio que afloje esto es un `[bug]` que bloquea.
5. **Público: cero secretos.** Ninguna credencial de PayPhone ni de un WHMCS real en el repo o el diff; solo placeholders.
6. **Compatibilidad:** mantener WHMCS 7.x–9.x y PHP 7.x/8.x salvo que el issue decida subir el mínimo (y entonces se documenta).
7. **Verificar:** `php -l` sin errores y prueba en un WHMCS de **prueba** con **sandbox** de PayPhone (pago OK, sin duplicados, token no expuesto). El output va pegado en el PR.
8. **CHANGELOG** actualizado cuando cambia el comportamiento visible.
9. **Commits** con tipo (`feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `perf`) y en español. **Prohibido** co-autoría de IA en commits y en PRs.
10. **Nada de estado escrito a mano** en el README (el avance vive en issues/releases; el CHANGELOG sí es el historial de versiones, convención open-source).

## Seguridad (regla dura)

- **Pagos:** token server-side, monto firmado, callback validado y anti-duplicados. No se debilita.
- **Público:** nada de credenciales reales (PayPhone o WHMCS) en el repo ni en issues/PRs; solo placeholders.
- Una vulnerabilidad se reporta en privado: ver [`SECURITY.md`](SECURITY.md).

## Skills del repositorio

Hay **dos rutas**:

1. **Colaboradores nuevos o externos** usan las skills de este repo, en [`.claude/skills/`](.claude/skills). Son una **copia sincronizada** desde el directorio oficial por el Action `sync-skills`; **no se editan a mano aquí**.
2. **Colaboradores oficiales de Marbust** usan el **directorio oficial** (`MarbustTechnologyCompany/ClaudeSkills`, en `~/.claude/skills`). **Es la fuente de verdad.**

| Skill | Cuándo |
|---|---|
| `escribir-un-issue` | Al crear o corregir un issue |
| `trabajar-un-issue` | Al tomar un issue, de principio a fin |
| `revisar-codigo` | **Obligatoria** antes del PR y al revisar el de otro |
