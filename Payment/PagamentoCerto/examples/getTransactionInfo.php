<?php
/**
 * This example demonstrates how to fetch transaction information
 * from the payment gateway.
 *
 * Your store postback URL should be a script that looks like this, so
 * the user can view all the information of the transaction.
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

require_once 'Payment/PagamentoCerto/PagamentoCerto.php';

$sellerApiKey = '3821023c-1602-499b-a220-80e4eb83c13b';

$obj = new Payment_PagamentoCerto($sellerApiKey);

// The payment gateway must send the transaction ID via GET
$transactionId = isset($_GET["tid"])
    ? $_GET["tid"]
    : 'c22f95f5-6d2e-4e0f-bc93-5678b116bbbd';

try {
    // Fetching the transaction information
    $transaction = $obj->getTransactionInfo($transactionId);

    echo "<pre>";
    print_r($transaction);
    echo "</pre>";

} catch (Payment_PagamentoCerto_Exception $e) {
    echo $e->getMessage();
}





?>