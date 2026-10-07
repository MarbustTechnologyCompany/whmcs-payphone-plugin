# Seguridad

Esta pasarela mueve **pagos reales** en WHMCS. Si encuentras una falla de seguridad, gracias por avisarnos. **No la publiques en un issue:** escríbenos de forma privada (es un repo público — una vulnerabilidad en un issue la ve cualquiera).

## Cómo reportarla

Escribe a **supportcenter@marbust.com** con el asunto "Seguridad WHMCS PayPhone: …".

Incluye qué encontraste y dónde (archivo, línea, commit), cómo reproducirlo y el impacto.

## Lo que pedimos

- No pruebes contra un WHMCS de producción ni con credenciales reales de PayPhone: usa una instalación de prueba y el sandbox.
- No degrades el servicio.
- Danos tiempo razonable para corregir antes de hacerlo público.

## Cómo se protege

- **Token Bearer solo en el servidor:** nunca llega al navegador del cliente.
- **Monto firmado con HMAC-SHA256:** no se puede manipular el valor a cobrar.
- **Callback validado y anti-duplicados** (`checkCbTransID`): no se registra dos veces la misma transacción, y se verifica la firma antes de dar por pagada una factura.
- **Repo público sin secretos:** ninguna credencial de PayPhone ni de un WHMCS real está en el código; solo placeholders.

Si un cambio debilitara cualquiera de estas defensas, es un `[bug]` que bloquea el merge.

## Versiones con soporte

Solo la rama `main` (última versión publicada).
