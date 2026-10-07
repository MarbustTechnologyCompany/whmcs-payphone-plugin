# Cómo colaborar — WHMCS PayPhone Gateway

Esta guía es el recorrido de cada cambio. Las reglas técnicas y de seguridad están en [`AGENTS.md`](AGENTS.md); si es tu primer día, parte de [`docs/ONBOARDING.md`](docs/ONBOARDING.md).

Este repo es **público y open-source**. Las contribuciones externas son bienvenidas: abre un issue describiendo el problema o la mejora, y un PR desde un fork. El mantenedor (Marbust Technology Company) revisa y mergea.

## El flujo (equipo Marbust)

1. **Tarjeta en Trello** — marca MBHostCloud/PayPhone, severidad, ejecutor, cómo probar.
2. **Issue** — lo abre `MarbustTechnologyCompany` con la plantilla. Se autocontiene. Skill `escribir-un-issue`.
3. **Rama** — desde `main`, nunca push directo a `main`.
4. **PR en borrador** — con la plantilla (`Refs #N`); al terminar, `Closes #N` y listo.
5. **Verificación real** — corre lo que aplique y **pega el output** en el PR.
6. **Revisión / QA** — antes del PR corre `revisar-codigo`. **Codex participa siempre.** Si el QA encuentra bugs, la empresa comenta **solicitando cambios**; se corrige, se responde, y recién si pasa se aprueba.
7. **Aprobación** — la da `MarbustTechnologyCompany` (el autor no se auto-aprueba).
8. **Squash** — un issue, un PR, un commit.

## Instalación local / pruebas

- Una instalación de **WHMCS de prueba** (nunca la de producción de un cliente) y credenciales de **sandbox** de PayPhone.
- Copiar el módulo (`modules/gateways/payphone.php` + `callback/payphone.php`) a la instalación, activarlo y probar un pago.

## Verificación (antes de pedir revisión)

| Qué | Para qué |
|---|---|
| `php -l` en los dos archivos | sintaxis PHP sin errores |
| Pago en WHMCS de prueba + sandbox | flujo Prepare→Confirm OK, pago registrado, sin duplicados |
| Token no expuesto / monto firmado | el Bearer no sale al navegador; HMAC-SHA256 intacto |
| CHANGELOG | actualizado si cambia el comportamiento visible |

## Novedades (producto público)

Cada PR declara su **Novedad** (pública / interna / hito). La línea pública la puede leer cualquiera (es un repo público): **no** menciona vulnerabilidades sin parchar, credenciales ni datos de clientes. El check "Checks del PR" exige las tres líneas.

## Reglas que no se discuten dentro de un PR

- **No debilitar la seguridad de pagos:** token server-side, monto firmado, callback validado y anti-duplicados.
- **Cero credenciales reales** (PayPhone o WHMCS) en el repo o el diff; solo placeholders.
- Mantener la compatibilidad declarada (WHMCS 7.x–9.x, PHP 7.x/8.x) salvo decisión en el issue.
- Commits con tipo, en español. **Prohibida la co-autoría de IA** en commits y PRs.
