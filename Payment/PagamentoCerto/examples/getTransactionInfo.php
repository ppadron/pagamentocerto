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

require_once 'Payment/PagamentoCerto.php';

$sellerApiKey = '3821023c-1602-499b-a220-258aeb83c13b';

$obj = new Payment_PagamentoCerto($sellerApiKey);

if (!isset($_GET["tid"])) {
    // if the transaction id wasn't specified, you should do something more
    // user-friendly than throwing an exception
    throw new Payment_PagamentoCerto_Exception('Transaction ID was not specified');
}

// The payment gateway must send the transaction ID via GET
$transactionId = $_GET["tid"];

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
