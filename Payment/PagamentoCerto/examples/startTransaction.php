<?php
/**
 * This example demonstrates how to start a transaction with the payment
 * gateway.
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
require_once 'Payment/PagamentoCerto/Order.php';

$order = new Payment_PagamentoCerto_Order();

/**
 * Defining buyer information.
 *
 * This info could be provided by the user through a form, or maybe
 * from a database
 */
$buyerInfo['name']     = 'Pedro Padron';
$buyerInfo['cpf']      = '99999999999';
$buyerInfo['rg']       = '999999999';
$buyerInfo['email']    = 'ppadron@w3p.com.br';
$buyerInfo['areaCode'] = '11';
$buyerInfo['phone']    = '55505423';
$order->setBuyerInfo($buyerInfo);

/**
 * Defining the shipping address
 */
$shippingAddress['address']           = 'Rua Inválida';
$shippingAddress['addressNumber']     = '10';
$shippingAddress['addressAdditional'] = '10o Andar';
$shippingAddress['district']          = 'Meu Bairro';
$shippingAddress['city']              = 'Sao Paulo';
$shippingAddress['zipCode']           = '02462081';
$shippingAddress['state']             = 'SP';
$order->setShippingAddress($shippingAddress);

/**
 * In this case, billing address is the same as the shipping address
 */
$order->setBillingAddress($shippingAddress);

/**
 * Product information.
 *
 * This could also be stored in a database, and could be fetched just
 * by the product id.
 *
 * The product ID can be a string
 */
$id          = '12';
$description = 'Pacote de Dadinhos Dizioli';
$quantity    = 5;
$value       = 9.90;

/**
 * Adding a product to the order.
 */
$order->addProduct($id, $description, $quantity, $value);

/**
 * Adding the same product twice
 *
 * If the same product is added several times, the "quantity" field
 * will be updated. In this case, now we would have 10 items of the
 * same product:
 *
 * $order->addProduct($id, $description, $quantity, $value);
 */


/**
 * Defining an order ID (must be an integer)
 */
$order->setOrderId(66);

/**
 * Defining the shipping value
 */
$order->setShippingValue(5.40);

/**
 * Setting a discount (percentage) as 7%
 */
$order->setDiscount(7, Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE);

/**
 * Setting a discount as a flat value:
 *
 * $order->setDiscount(15, Payment_PagamentoCerto_Order::DISCOUNT_TYPE_VALUE);
 */


/**
 * Defining the payment method
 *
 * Both are valid:
 * $order->setPaymentMethodAsInvoice();
 * $order->setPaymentMethodAsCreditCardVisa();
 */
$order->setPaymentMethodAsInvoice();


/**
 * This is where the user will be redirected to by the payment gateway.
 * In this page, the user should be able to see the transaction result.
 * An example of how this can be done can be found in the file:
 * examples/getTransactionInfo.php
 */
$postbackUrl = 'http://www.example.com/transactionResult.php';

$sellerApiKey = '3821023c-1602-499b-a220-258aeb83c13b';

$process = new Payment_PagamentoCerto($sellerApiKey, $postbackUrl);

try {

    $transactionId = $process->startTransaction($order);

    /**
     * Now the user is redirected to the payment gateway to proceed
     * with the payment
     */
    $process->redirectToPaymentGateway($transactionId);

} catch (Payment_PagamentoCerto_Exception $e) {

    echo $e->getMessage();

}

?>
