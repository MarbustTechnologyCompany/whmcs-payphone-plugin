# WHMCS PayPhone Payment Gateway

Pasarela de pago **PayPhone** (Ecuador) para **WHMCS** — **gratuita y de código abierto**.
Free & open-source **PayPhone** payment gateway for **WHMCS**.

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
![WHMCS](https://img.shields.io/badge/WHMCS-7.x%20–%209.x-005c9e)
![PHP](https://img.shields.io/badge/PHP-7.x%20%7C%208.x-777bb4)
![Status](https://img.shields.io/badge/probado%20en%20producción-✅-success)

> Una alternativa **libre** para que cualquier hoster o desarrollador en Ecuador
> pueda cobrar con PayPhone en WHMCS, sin pagar por un módulo comercial.
> Si te ayuda, considera una donación 💙 → **https://paypal.me/MarbustTechnology**

---

## 🇪🇸 Español

### ¿Qué hace?
Permite que tus clientes paguen sus facturas de WHMCS con **PayPhone** (tarjeta + 3D Secure).
Usa el flujo oficial **Prepare → redirección → Confirm**, todo **del lado del servidor**:
el **token nunca llega al navegador** del cliente.

### Características
- ✅ Cobro con PayPhone desde la factura del cliente.
- 🔒 Token Bearer **solo en el servidor** (no se expone en el navegador).
- 🧾 Registra el pago automáticamente en WHMCS al confirmarse (`addInvoicePayment`).
- 🛡️ Monto firmado con **HMAC-SHA256** (no se puede manipular).
- ♻️ Anti-duplicados con `checkCbTransID` (no registra dos veces la misma transacción).
- 📒 Deja trazas en el **Registro de la pasarela** (Prepare/Confirm/errores).
- 🧩 Sin tablas extra: el `invoiceId` viaja dentro del `clientTransactionId`.

### Requisitos
- WHMCS (probado en 9.x; compatible con la API de gateways estándar).
- PHP con **cURL** (estándar en WHMCS).
- Dominio con **HTTPS**.
- Una **aplicación/Botón de PayPhone** con su **token** (Bearer).
- Las facturas deben estar en **USD** (PayPhone procesa solo dólares).

### 🔑 Obtener tu token de PayPhone (¡lo único que necesitas!)
La **única** credencial requerida es el **token de la aplicación (Bearer)**.
Genéralo siguiendo la **documentación oficial de PayPhone**:
**https://docs.payphone.app/**
(Developer → crear tu aplicación / Botón de pagos → copiar el **token**).
Ese token es lo único que pegarás en la configuración del módulo.

> ⚠️ Es importante seguir esos pasos de PayPhone para generar el token correctamente;
> sin un token válido el módulo no puede cobrar.

### Instalación
1. Copia los archivos a tu WHMCS respetando las rutas:
   ```
   <whmcs>/modules/gateways/payphone.php
   <whmcs>/modules/gateways/callback/payphone.php
   ```
2. Admin → **Configuración → Pasarelas de pago → Todas las pasarelas** → activa **PayPhone**.
3. Pega tu **Token de Aplicación (Bearer)** y guarda.
   (Deja las URLs de *Prepare/Confirm* por defecto salvo que PayPhone te indique otras.)

### ¿Cómo funciona?
1. El cliente ve el botón **“Pagar con PayPhone”** en su factura.
2. Al hacer clic, WHMCS llama al callback (`action=prepare`), que ejecuta **Prepare**
   server-side y redirige a la página de pago de PayPhone (`payWithCard`).
3. Tras pagar, PayPhone regresa a
   `…/modules/gateways/callback/payphone.php?id=…&clientTransactionId=…`.
4. El callback ejecuta **Confirm**, valida la aprobación
   (`statusCode === 3` / `transactionStatus === 'Approved'`) y registra el pago.

### Configuración (campos del módulo)
| Campo | Descripción |
|---|---|
| **Token de Aplicación (Bearer)** | Token de tu app PayPhone (se guarda cifrado, solo server-side). |
| **Prepare URL** | `https://pay.payphonetodoesposible.com/api/button/Prepare` (por defecto). |
| **Confirm URL** | `https://pay.payphonetodoesposible.com/api/button/V2/Confirm` (por defecto). |

### Pruebas
1. Crea una factura **no pagada en USD**.
2. Como cliente, paga con **PayPhone**.
3. Verifica que la factura quede **Pagada** y revisa **Admin → Utilidades → Registros →
   Registro de la pasarela** (`Prepare OK` y `Success`).

### Solución de problemas
- **El botón avisa “solo USD”:** la factura no está en dólares; cambia la moneda.
- **PayPhone cobró pero la factura sigue pendiente:** revisa el *Registro de la pasarela*;
  abre un *issue* con la entrada (sin exponer tu token) y lo revisamos.
- **“Module Not Activated”:** activa el módulo en Pasarelas de pago.

---

## 🇬🇧 English

### What it does
Lets your WHMCS clients pay invoices with **PayPhone** (card + 3D Secure) using the
official **Prepare → redirect → Confirm** flow, fully **server-side** — the token never
reaches the customer's browser.

### Features
- ✅ Pay invoices with PayPhone from the client area.
- 🔒 Bearer token used **server-side only**.
- 🧾 Auto-registers the payment in WHMCS on confirmation.
- 🛡️ Amount signed with **HMAC-SHA256** (tamper-proof).
- ♻️ Duplicate-safe via `checkCbTransID`.
- 📒 Logs to the WHMCS **Gateway Log**.
- 🧩 No extra tables — the `invoiceId` is encoded in the `clientTransactionId`.

### Requirements
WHMCS (tested on 9.x) · PHP with **cURL** · **HTTPS** · a PayPhone **app token** ·
invoices in **USD** (PayPhone processes USD only).

### 🔑 Get your PayPhone token (the only thing you need!)
The **only** required credential is the **application token (Bearer)**.
Generate it by following the **official PayPhone documentation**:
**https://docs.payphone.app/**
(Developer → create your application / Payment Button → copy the **token**).
That token is the only value you'll paste into the module settings.

> ⚠️ It's important to follow PayPhone's steps to generate the token correctly;
> without a valid token the module cannot process payments.

### Installation
Copy the two files into your WHMCS, keeping the paths:
```
<whmcs>/modules/gateways/payphone.php
<whmcs>/modules/gateways/callback/payphone.php
```
Then activate **PayPhone** under *Setup → Payment Gateways → All Payment Gateways*,
paste your **Application Token (Bearer)**, and save.

### How it works
1. Client clicks **“Pay with PayPhone”** on the invoice.
2. WHMCS calls the callback (`action=prepare`) → server-side **Prepare** → redirect to
   PayPhone (`payWithCard`).
3. PayPhone returns to the callback with `id` + `clientTransactionId`.
4. The callback runs **Confirm**, verifies approval and records the payment.

---

## ⚠️ Aviso / Disclaimer
Proyecto **comunitario**, **no afiliado oficialmente a PayPhone**. Se ofrece “tal cual”,
sin garantías (ver [LICENSE](LICENSE)). PayPhone® es marca de su respectivo titular.

This is a **community** project, **not officially affiliated with PayPhone**. Provided
“as is”, without warranty.

## 🤝 Contribuir / Contributing
Issues y Pull Requests son bienvenidos. Reporta bugs con la entrada del *Gateway Log*
(sin compartir tu token). · Issues and PRs welcome.

## 💙 Donaciones / Donations
Si este módulo te ahorró tiempo o dinero, puedes apoyar el proyecto:
**https://paypal.me/MarbustTechnology**

## 👤 Créditos / Credits
- **Marbust Technology Company** — https://marbust.com
- **Marco Antonio Bustillos (MarAntBQ)** — https://marantbq.dev · https://github.com/MarAntBQ

## 📄 Licencia / License
**MIT** © Marbust Technology Company. Uso libre y gratuito reconociendo a los autores.
Ver [LICENSE](LICENSE).
