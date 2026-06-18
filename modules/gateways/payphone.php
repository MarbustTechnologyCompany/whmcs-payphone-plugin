<?php
/**
 * WHMCS PayPhone Payment Gateway — third-party, redirect server-side.
 * Pasarela de pago PayPhone (Ecuador) para WHMCS — gratuita y de código abierto.
 *
 * Flujo: Prepare (servidor) -> redirige a payWithCard -> el cliente paga ->
 * PayPhone regresa al callback -> Confirm (servidor) -> registra el pago.
 * El token Bearer NUNCA llega al navegador (solo se usa en el servidor).
 *
 * Repositorio: https://github.com/MarbustTechnologyCompany/whmcs-payphone-plugin
 * Autor:       Marco Antonio Bustillos (MarAntBQ) — https://marantbq.dev — https://github.com/MarAntBQ
 * Licencia:    MIT (c) Marbust Technology Company — https://marbust.com
 *              Uso libre y gratuito reconociendo a los autores.
 * Donaciones:  https://paypal.me/MarbustTechnology
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * Metadatos del módulo.
 */
function payphone_MetaData()
{
    return [
        'DisplayName' => 'PayPhone (Pagos con Tarjeta de Crédito / Débito Visa, MasterCard, Diners, Discover)',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    ];
}

/**
 * Campos de configuración (Admin > Configuración > Pasarelas de pago).
 */
function payphone_config()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'PayPhone (Pagos con Tarjeta de Crédito / Débito Visa, MasterCard, Diners, Discover)',
        ],
        'token' => [
            'FriendlyName' => 'Token de Aplicación (Bearer)',
            'Type' => 'password',
            'Size' => '60',
            'Description' => 'Token de tu aplicación PayPhone (el mismo del Botón/API). Se guarda cifrado y solo se usa en el servidor.',
        ],
        'prepareUrl' => [
            'FriendlyName' => 'Prepare URL',
            'Type' => 'text',
            'Size' => '70',
            'Default' => 'https://pay.payphonetodoesposible.com/api/button/Prepare',
        ],
        'confirmUrl' => [
            'FriendlyName' => 'Confirm URL',
            'Type' => 'text',
            'Size' => '70',
            'Default' => 'https://pay.payphonetodoesposible.com/api/button/V2/Confirm',
        ],
    ];
}

/**
 * Botón de pago que se muestra en la factura.
 *
 * No llama a PayPhone aquí (para no crear una sesión de pago en cada carga
 * de la factura). En su lugar muestra un botón que, al hacer clic, hace POST
 * al callback con action=prepare; ese paso ejecuta Prepare y redirige.
 *
 * La firma HMAC evita que un tercero inicie un pago con un monto manipulado.
 */
function payphone_link($params)
{
    $invoiceId = $params['invoiceid'];
    $amount = number_format((float) $params['amount'], 2, '.', '');
    $currency = isset($params['currency']) ? $params['currency'] : '';
    $token = $params['token'];
    $systemUrl = rtrim($params['systemurl'], '/');
    $callbackUrl = $systemUrl . '/modules/gateways/callback/payphone.php';
    $langPayNow = !empty($params['langpaynow']) ? $params['langpaynow'] : 'Pagar con PayPhone';

    if ($currency && strtoupper($currency) !== 'USD') {
        return '<div class="alert alert-warning">PayPhone solo procesa pagos en <strong>USD</strong>. '
            . 'Esta factura está en ' . htmlspecialchars($currency) . '.</div>';
    }

    $hash = hash_hmac('sha256', $invoiceId . '|' . $amount, $token);

    $html = '<form method="post" action="' . htmlspecialchars($callbackUrl) . '" style="display:inline">';
    $html .= '<input type="hidden" name="action" value="prepare" />';
    $html .= '<input type="hidden" name="invoiceid" value="' . htmlspecialchars($invoiceId) . '" />';
    $html .= '<input type="hidden" name="amount" value="' . htmlspecialchars($amount) . '" />';
    $html .= '<input type="hidden" name="hash" value="' . htmlspecialchars($hash) . '" />';
    $html .= '<input type="submit" value="' . htmlspecialchars($langPayNow) . '" class="btn btn-success" />';
    $html .= '</form>';

    return $html;
}
