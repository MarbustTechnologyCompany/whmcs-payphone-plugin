# Tu primer día en WHMCS PayPhone Gateway

Guía para quien empieza a mantener esta pasarela. Si algo no alcanza, es un error de la guía: dilo en un issue.

## 1. Qué es, en un minuto

Una pasarela de pago **PayPhone** (Ecuador) **open-source (MIT)** para **WHMCS** (7.x–9.x, PHP 7.x/8.x). Cobra facturas con el flujo oficial **Prepare → redirección → Confirm**, **todo del lado del servidor**: el **token Bearer nunca llega al navegador**. Registra el pago (`addInvoicePayment`), firma el monto con **HMAC-SHA256** y evita duplicados con `checkCbTransID`. Dos archivos PHP: el gateway y el callback.

- 🔴 **Es un módulo de cobro en producción.** Token server-side, monto firmado, callback validado y anti-duplicados: no se debilitan.
- **Repo público:** cero credenciales reales (PayPhone o WHMCS); solo placeholders.

## 2. Qué leer, en este orden

1. [`README.md`](../README.md) — qué hace e instalación (bilingüe).
2. Esta guía.
3. [`CONTRIBUTING.md`](../CONTRIBUTING.md) — el recorrido de cada cambio (incluye contribuciones externas).
4. [`AGENTS.md`](../AGENTS.md) — stack, seguridad de pagos y skills.
5. [`CHANGELOG.md`](../CHANGELOG.md) — historial de versiones.
6. Las skills de [`.claude/skills/`](../.claude/skills) (copia para colaboradores nuevos; los oficiales usan el directorio de la empresa).
7. [`SECURITY.md`](../SECURITY.md).

## 3. Accesos que debes pedir

Los concede Marco Antonio Bustillos (indica tu usuario de GitHub y correo):

| Acceso | Para qué |
|---|---|
| Colaborador del repo `MarbustTechnologyCompany/whmcs-payphone-plugin` (equipo Marbust) | Ramas y PRs (externos: fork + PR) |
| Un WHMCS de prueba + credenciales de sandbox de PayPhone | Probar los pagos |

## 4. Herramientas

- **git** y **GitHub CLI** (`gh`). **PHP** (`php -l`). Un WHMCS de prueba.

## 5. Tu primer issue

Sigue la skill [`trabajar-un-issue`](../.claude/skills/trabajar-un-issue/SKILL.md): elige uno chico, confirma en el issue, rama desde `main` (o fork), PR borrador, verifica (`php -l` + pago en WHMCS de prueba con sandbox), revisa tu diff con `revisar-codigo`, pasa el QA, responde la revisión hasta el squash.

## 6. Cuentas en GitHub

`MarbustTechnologyCompany` crea los issues y aprueba los PRs. Quien implementa (`MarAntBQ`, el equipo, o un externo desde un fork) hace ramas/forks y PRs. Nadie aprueba su propio PR. Merge por squash.

## 7. Lo que nunca se hace

- Debilitar la seguridad de pagos (token server-side, HMAC, anti-duplicados).
- Credenciales reales (PayPhone o WHMCS) en el repo, issues o PRs.
- Romper la compatibilidad declarada sin decidirlo en el issue.
- Push directo a `main`; trabajar sin issue.
- Co-autoría de IA en commits o PRs.
