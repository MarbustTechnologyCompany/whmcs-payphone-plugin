<?php
/**
 * WHMCS PayPhone — Callback (dos fases):
 *
 *  1) action=prepare  : ejecuta Prepare contra PayPhone y redirige a payWithCard.
 *  2) retorno PayPhone : ?id=<transactionId>&clientTransactionId=<clientTxId>
 *                        ejecuta Confirm, valida y registra el pago en WHMCS.
 *
 * Repositorio: https://github.com/MarbustTechnologyCompany/whmcs-payphone-plugin
 * Autor:       Marco Antonio Bustillos (MarAntBQ) — https://github.com/MarAntBQ
 * Licencia:    MIT (c) Marbust Technology Company — https://marbust.com
 * Donaciones:  https://paypal.me/MarbustTechnology
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Módulo activado.
if (!$gatewayParams['type']) {
    die('Module Not Activated');
}

$token = $gatewayParams['token'];
$prepareUrl = !empty($gatewayParams['prepareUrl'])
    ? $gatewayParams['prepareUrl']
    : 'https://pay.payphonetodoesposible.com/api/button/Prepare';
$confirmUrl = !empty($gatewayParams['confirmUrl'])
    ? $gatewayParams['confirmUrl']
    : 'https://pay.payphonetodoesposible.com/api/button/V2/Confirm';

/**
 * URL base del sistema (defensivo: getGatewayVariables suele traer 'systemurl',
 * pero si faltara se obtiene de la configuración de WHMCS).
 */
function payphone_system_url($gatewayParams)
{
    if (!empty($gatewayParams['systemurl'])) {
        return rtrim($gatewayParams['systemurl'], '/');
    }
    if (class_exists('\\WHMCS\\Config\\Setting')) {
        $url = \WHMCS\Config\Setting::getValue('SystemURL');
        if ($url) {
            return rtrim($url, '/');
        }
    }
    return '';
}

$systemUrl = payphone_system_url($gatewayParams);

/**
 * POST JSON a PayPhone con cURL (libcurl es aceptado por su backend IIS).
 */
function payphone_http_post($url, $token, array $body)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $status,
        'data' => json_decode((string) $raw, true),
        'raw' => $raw,
        'error' => $error,
    ];
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

/* ----------------------------------------------------------------------- */
/* FASE 1 — Prepare + redirección                                          */
/* ----------------------------------------------------------------------- */
if ($action === 'prepare') {
    $invoiceId = (int) (isset($_POST['invoiceid']) ? $_POST['invoiceid'] : 0);
    $amount = number_format((float) (isset($_POST['amount']) ? $_POST['amount'] : 0), 2, '.', '');
    $hash = isset($_POST['hash']) ? $_POST['hash'] : '';

    $expected = hash_hmac('sha256', $invoiceId . '|' . $amount, $token);
    if (!$invoiceId || !hash_equals($expected, $hash)) {
        die('Solicitud inválida.');
    }

    $amountCents = (int) round(((float) $amount) * 100);
    if ($amountCents <= 0) {
        header('Location: ' . $systemUrl . '/viewinvoice.php?id=' . $invoiceId);
        exit;
    }

    $clientTxId = substr('WHMCS-' . $invoiceId . '-' . time() . '-' . rand(1000, 9999), 0, 50);
    $responseUrl = $systemUrl . '/modules/gateways/callback/payphone.php';

    $body = [
        'amount' => $amountCents,
        'amountWithoutTax' => $amountCents,
        'amountWithTax' => 0,
        'tax' => 0,
        'service' => 0,
        'tip' => 0,
        'clientTransactionId' => $clientTxId,
        'responseUrl' => $responseUrl,
        'currency' => 'USD',
        'reference' => 'Factura ' . $invoiceId,
    ];

    $resp = payphone_http_post($prepareUrl, $token, $body);
    $data = is_array($resp['data']) ? $resp['data'] : [];
    $redirectUrl = isset($data['payWithCard'])
        ? $data['payWithCard']
        : (isset($data['payWithPayPhone']) ? $data['payWithPayPhone'] : '');

    if ($resp['status'] !== 200 || !$redirectUrl) {
        logTransaction($gatewayParams['name'], ['request' => $body, 'response' => $resp], 'Prepare Failed');
        die('No se pudo iniciar el pago con PayPhone. Intenta nuevamente o contacta a soporte.');
    }

    logTransaction(
        $gatewayParams['name'],
        ['clientTxId' => $clientTxId, 'paymentId' => isset($data['paymentId']) ? $data['paymentId'] : null],
        'Prepare OK'
    );

    header('Location: ' . $redirectUrl);
    exit;
}

/* ----------------------------------------------------------------------- */
/* FASE 2 — Retorno de PayPhone + Confirm                                  */
/* ----------------------------------------------------------------------- */
$transactionId = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
$clientTxId = isset($_REQUEST['clientTransactionId']) ? trim($_REQUEST['clientTransactionId']) : '';

if ($transactionId === '' || $clientTxId === '') {
    die('Parámetros de retorno inválidos.');
}

// El invoiceId va codificado en el clientTransactionId: WHMCS-<invoiceId>-...
if (!preg_match('/^WHMCS-(\d+)-/', $clientTxId, $m)) {
    die('No se pudo identificar la factura.');
}
$invoiceId = (int) $m[1];

$confirm = payphone_http_post($confirmUrl, $token, [
    'id' => (int) $transactionId,
    'clientTxId' => $clientTxId,
]);
$result = is_array($confirm['data']) ? $confirm['data'] : [];

$approved = (
    (isset($result['statusCode']) && (int) $result['statusCode'] === 3) ||
    (isset($result['transactionStatus']) && $result['transactionStatus'] === 'Approved')
);

// Valida que la factura exista y no esté ya pagada; evita doble registro.
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
checkCbTransID($transactionId);

if (!$approved) {
    logTransaction($gatewayParams['name'], $confirm, 'Unsuccessful');
    header('Location: ' . $systemUrl . '/viewinvoice.php?id=' . $invoiceId . '&paymentfailed=true');
    exit;
}

// PayPhone devuelve el monto en centavos.
$paidAmount = isset($result['amount']) ? ((float) $result['amount']) / 100 : 0;
$fee = 0;

addInvoicePayment($invoiceId, $transactionId, $paidAmount, $fee, $gatewayModuleName);
logTransaction($gatewayParams['name'], $result, 'Success');

header('Location: ' . $systemUrl . '/viewinvoice.php?id=' . $invoiceId . '&paymentsuccess=true');
exit;
