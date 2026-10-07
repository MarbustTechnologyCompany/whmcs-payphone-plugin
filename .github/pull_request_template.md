<!-- Ábrelo como borrador (Draft) apenas empieces, con `Refs #<número>`. Cuando esté listo, cámbialo a `Closes #<número>` y márcalo como listo para revisión. La plantilla se llena completa también en borrador. Este repo es PÚBLICO y open-source: cuida lo que escribes. -->

## Resumen

<!-- Qué cambió y por qué. -->

## Novedad

<!-- Sale sola de este PR cuando se mergea. Una línea por público; "ninguna" si no aplica.
La línea pública la puede leer cualquiera (es un repo público): nada de vulnerabilidades sin parchar, credenciales ni datos de clientes. El check "Checks del PR" exige las tres líneas. -->

- pública:
- interna:
- hito: no

## Issue vinculado

<!-- `Closes #123` / `Refs #123`, en inglés. -->

## Cómo probar

<!-- En una instalación WHMCS de prueba (nunca una de producción de un cliente): configurar la pasarela con credenciales de SANDBOX de PayPhone, generar una factura y pagarla; verificar el flujo Prepare → redirección → Confirm. -->

1.

## Verificación

<!-- Solo lo que SÍ se corrió, con el output pegado. N/A con el motivo si no aplica. -->

- [ ] `php -l modules/gateways/payphone.php` y el callback sin errores de sintaxis
- [ ] Prueba en WHMCS de **prueba** con **sandbox** de PayPhone: pago OK registrado (`addInvoicePayment`), sin duplicados
- [ ] El token Bearer **no** se expone en el navegador (flujo server-side), el monto va firmado (HMAC-SHA256)
- [ ] CHANGELOG actualizado si cambia el comportamiento visible

## Seguridad y operaciones

- [ ] No hay credenciales de PayPhone ni de ningún WHMCS real en el diff (solo placeholders)
- [ ] No se debilita la verificación del callback (HMAC, anti-duplicados `checkCbTransID`)
- [ ] Compatibilidad declarada (WHMCS 7.x–9.x, PHP 7.x/8.x) sigue siendo cierta

---

> Este PR entra a `main` por **squash**. Un issue, un PR, un commit. Sin co-autoría de IA en commits ni en PRs.
