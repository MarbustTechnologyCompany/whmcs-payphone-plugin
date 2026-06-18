# Changelog

Todas las versiones notables de este proyecto se documentan aquí.
All notable changes to this project are documented here.

## [1.0.0] — 2026-06-17
### Añadido / Added
- Primera versión pública del módulo de pago **PayPhone** para WHMCS.
- Flujo server-side **Prepare → redirección → Confirm** (token solo en el servidor).
- Registro automático del pago en WHMCS (`addInvoicePayment`) al confirmarse.
- Firma **HMAC-SHA256** del monto y protección anti-duplicados (`checkCbTransID`).
- Trazas en el **Registro de la pasarela** de WHMCS.
- Nombre visible para el cliente: *PayPhone (Pagos con Tarjeta de Crédito / Débito
  Visa, MasterCard, Diners, Discover)*.
- Probado en producción (factura pagada de punta a punta).
