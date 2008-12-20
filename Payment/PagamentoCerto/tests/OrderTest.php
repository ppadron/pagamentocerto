<?php
/**
 * Unit test for the class Payment_PagamentoCerto_Order
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

require_once 'PHPUnit/Framework.php';
require_once 'Payment/PagamentoCerto/PagamentoCerto.php';
require_once 'Payment/PagamentoCerto/Order.php';


/**
 * Unit test for the class Payment_PagamentoCerto_Order
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class OrderTest extends PHPUnit_Framework_TestCase
{

    /**
     * Setting up basic data
     *
     * @return void
     */
    function setUp()
    {
        // basic setup
        $this->process = new Payment_PagamentoCerto_Order();

        // buyer information
        $this->buyerInfo['name']     = 'Pedro Padron';
        $this->buyerInfo['cpf']      = '00000000000';
        $this->buyerInfo['rg']       = '99.999.999-9';
        $this->buyerInfo['email']    = 'ppadron@w3p.com.br';
        $this->buyerInfo['areaCode'] = '11';
        $this->buyerInfo['phone']    = '55505423';

        // shipping address
        $this->shippingAddress['address']           = 'Rua Invalida';
        $this->shippingAddress['addressNumber']     = '405';
        $this->shippingAddress['addressComplement'] = '10o Andar - Apartamento 103';
        $this->shippingAddress['district']          = 'Vila Inexistente';
        $this->shippingAddress['city']              = 'Sao Paulo';
        $this->shippingAddress['zipCode']           = 02462081;
        $this->shippingAddress['state']             = 'SP';

        // billing address
        $this->billingAddress = $this->shippingAddress;
    }

    /**
     * Checking for required fields in buyer information
     *
     * @return void
     */
    function testRequiredBuyerInfoPerson()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        // name
        $buyerInfo = $this->buyerInfo;
        unset($buyerInfo['name']);
        $this->expectedExceptionMessage = "buyer's name is required";
        $this->process->setBuyerInfo($buyerInfo);

        // cpf
        $buyerInfo = $this->buyerInfo;
        unset($buyerInfo['cpf']);
        $this->expectedExceptionMessage = "buyer's cpf is required";
        $this->process->setBuyerInfo($buyerInfo);

        // email
        $buyerInfo = $this->buyerInfo;
        unset($buyerInfo['email']);
        $this->expectedExceptionMessage = "buyer's email is required";
        $this->process->setBuyerInfo($buyerInfo);
    }

    /**
     * Checking for company info: CNPJ
     *
     * @return void
     */
    function testRequiredBuyerInfoCompanyCnpj()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';


        $buyerInfo = $this->buyerInfo;

        $this->expectedExceptionMessage = "buyer's cnpj is required";

        $this->process->setBuyerInfo(
            $buyerInfo,
            Payment_PagamentoCerto_Order::BUYER_TYPE_COMPANY
        );

    }

    /**
     * Checking for company info: Name
     *
     * @return void
     */
    function testRequiredBuyerInfoCompanyName()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $buyerInfo         = $this->buyerInfo;
        $buyerInfo['cnpj'] = '00.000.000-00';

        $this->expectedExceptionMessage
            = "buyer's companyName is required";

        $this->process->setBuyerInfo(
            $buyerInfo,
            Payment_PagamentoCerto_Order::BUYER_TYPE_COMPANY
        );
    }

    /**
     * Checking for required product information
     *
     * @return void
     */
    function testAddProductRequiredInfo()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $id          = '12';
        $quantity    = 3;
        $description = 'Outro produto legal';
        $value       = 30.00;

        $this->process->addProduct(null, $quantity, $description, $value);
        $this->process->addProduct($id,  0        , $description, $value);
        $this->process->addProduct($id,  $quantity, ''          , $value);
        $this->process->addProduct($id,  $quantity, $description, 0);
    }

    /**
     * Checking if total amount is calculated correctly
     *
     * @return void
     */
    function testAddProductTotalAmount()
    {
        $id          = '666a';
        $quantity    = 7;
        $description = 'Um produto muito bom!';
        $value       = 23.00;

        $this->process->addProduct($id, $description, $quantity, $value);

        $totalAmount = $this->process->getProductTotalAmount($id);

        $this->assertEquals(161.00, $totalAmount);
    }

    /**
     * Checking if product quantity is a valid parameter
     *
     * @return void
     */
    function testAddProductInvalidQuantity()
    {
        $id          = '13';
        $description = 'Um produto muito bom!';
        $value       = 23.00;

        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';

        // float quantity is invalid
        $quantity = 31.4;
        $this->process->addProduct($id, $description, $quantity, $value);

        // string quantity is invalid
        $quantity = '2';
        $this->process->addProduct($id, $description, $quantity, $value);

        // int quantity is ok!
        $this->expectedException = '';

        $quantity = 3;

        $this->process->addProduct($id, $description, $quantity, $value);

    }

    /**
     * Checking if the product value is correctly rounded
     *
     * @return void
     */
    function testAddProductRoundingValue()
    {
        $id          = '26';
        $description = 'Um produto muito bom!';
        $quantity    = 3;
        $value       = 14.256371;

        $this->process->addProduct($id, $description, $quantity, $value);

        $totalAmount = $this->process->getProductTotalAmount($id);

        $this->assertEquals(42.77, $totalAmount);
    }

    /**
     * Checking if the total value for a product is calculated correctly
     *
     * @return void
     */
    function testAddProductQuantitySum()
    {
        $id          = '2';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;

        // adds the same product 3 times
        $this->process->addProduct($id, $description, $quantity, $value);
        $this->process->addProduct($id, $description, $quantity, $value);
        $this->process->addProduct($id, $description, $quantity, $value);

        $items = $this->process->getProductQuantity($id);

        // do we have 6 items?
        $this->assertEquals(6, $items);

    }

    /**
     * Checking if the sub total is calculated correctly for multiple products
     *
     * @return void
     */
    function testAddMultipleProductSubTotal()
    {
        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        $this->assertEquals(108.8, $this->process->getOrderSubTotal());
    }

    /**
     * Checks that the product value is the same when a product is added
     * more than once
     *
     * @return void
     */
    function testAddProductValueCheck()
    {
        $id          = '3';
        $description = 'Um produto muito bom!';
        $quantity    = 1;
        $value       = 21.50;

        $this->process->addProduct($id, $description, $quantity, $value);

        $this->expectedException
            = 'Payment_PagamentoCerto_ProductValueMismatchException';

        // now we change the product value and try to add it again
        $value = 32.50;
        $this->process->addProduct($id, $description, $quantity, $value);
    }

    /**
     * Checks that the address contains the field: address
     *
     * @return void
     */
    function testRequiredShippingAddressAddress()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['address']);
        $this->process->setShippingAddress($shippingAddress);

    }

    /**
     * Checks that the address contains the field: address number
     *
     * @return void
     */
    function testRequiredShippingAddressAddressNumber()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['addressNumber']);
        $this->process->setShippingAddress($shippingAddress);
    }

    /**
     * Checks that the address contains the field: district
     *
     * @return void
     */
    function testRequiredShippingAddressDistrict()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['district']);
        $this->process->setShippingAddress($shippingAddress);

    }

    /**
     * Checks that the address contains the field: address
     *
     * @return void
     */
    function testRequiredShippingAddressCity()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['city']);
        $this->process->setShippingAddress($shippingAddress);

    }

    /**
     * Checks that the address contains the field: zip code
     *
     * @return void
     */
    function testRequiredShippingAddressZipCode()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['zipCode']);
        $this->process->setShippingAddress($shippingAddress);

    }

    /**
     * Checks that the address contains the field: state
     *
     * @return void
     */
    function testRequiredShippingAddressState()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_MissingParameterException';

        $shippingAddress = $this->shippingAddress;
        unset($shippingAddress['state']);
        $this->process->setShippingAddress($shippingAddress);

    }

    /**
     * Checks if the shipping address is set
     *
     * @return void
     */
    function testSetShippingAddress()
    {
        $this->process->setShippingAddress($this->shippingAddress);
        $this->assertEquals(
            $this->shippingAddress,
            $this->process->getShippingAddress()
        );
    }

    /**
     * Checks if the shipping value is set
     *
     * @return void
     */
    function testSetShippingValue()
    {
        // float is ok
        $this->process->setShippingValue(15.20);
        $this->assertEquals(15.20, $this->process->getShippingValue());

        // integer is ok
        $this->process->setShippingValue(40);
        $this->assertEquals(40, $this->process->getShippingValue());
    }

    /**
     * Checks that an invalid value cannot be set as shipping value
     *
     * @return void
     */
    function testSetShippingValueInvalidParameter()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';
        $this->process->setShippingValue(null);
    }

    /**
     * Checks if the billing address is set
     *
     * @return void
     */
    function testSetBillingAddress()
    {
        $this->process->setBillingAddress($this->shippingAddress);
        $this->assertEquals(
            $this->shippingAddress,
            $this->process->getBillingAddress()
        );
    }

    /**
     * Checks if it's possible to set payment method as invoice
     *
     * @return void
     */
    function testSetPaymentMethodAsInvoice()
    {
        $this->process->setPaymentMethodAsInvoice();
        $this->assertEquals(
            Payment_PagamentoCerto_Order::PAYMENT_METHOD_INVOICE,
            $this->process->getPaymentMethod()
        );
    }

    /**
     * Checks if it's possible to set payment method as Visa credit card
     *
     * @return void
     */
    function testSetPaymentMethodAsCreditCardVisa()
    {
        $this->process->setPaymentMethodAsCreditCardVisa();
        $this->assertEquals(
            Payment_PagamentoCerto_Order::PAYMENT_METHOD_CC_VISA,
            $this->process->getPaymentMethod()
        );
    }

    /**
     * Checks if the subtotal is calculated correctly
     *
     * @return void
     */
    function testGetOrderSubTotal()
    {
        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        $this->assertEquals(108.8, $this->process->getOrderSubTotal());
    }

    /**
     * Checks if it's possible to set a value as discount
     *
     * @return void
     */
    function testSetDiscountValue()
    {
        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        $this->process->setDiscount(
            10,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_VALUE
        );

        $this->assertEquals(10, $this->process->getDiscountValue());

        $this->assertEquals(
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_VALUE,
            $this->process->getDiscountType()
        );

        $subTotal = $this->process->getOrderSubTotal();

        $this->assertEquals(
            $subTotal - 10,
            $this->process->applyDiscount($subTotal)
        );

    }

    /**
     * Checks if it's possible to set a percentage as discount
     *
     * @return void
     */
    function testSetDiscountPercentage()
    {
        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        $this->process->setDiscount(
            15,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE
        );

        $this->assertEquals(15, $this->process->getDiscountValue());

        $this->assertEquals(
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE,
            $this->process->getDiscountType()
        );

        $subTotal = $this->process->getOrderSubTotal();

        $this->assertEquals(
            92.48,
            $this->process->applyDiscount($subTotal)
        );

    }

    /**
     * Checks if an exception will be thrown when trying to set discount
     * without products
     *
     * @return void
     */
    function testSetDiscountNoProducts()
    {
        $this->expectedException = 'Payment_PagamentoCerto_NoProductsException';
        $this->process->setDiscount(
            10,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE
        );
    }

    /**
     * Checks that an invalid parameter can't be set as discount
     *
     * @return void
     */
    function testSetDiscountInvalidParameter()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';

        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        $this->process->setDiscount(
            null,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE
        );
    }

    /**
     * Checks if an exception will be thrown when trying to set a discount
     * greater than the total amount of the order
     *
     * @return void
     */
    function testSetDiscountGreaterThanTotalAmount()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';

        // first product
        $id          = '1';
        $description = 'Um produto muito bom!';
        $quantity    = 2;
        $value       = 21.50;
        $this->process->addProduct($id, $description, $quantity, $value);

        // second product
        $id          = '2';
        $description = 'Outro produto da minha loja';
        $quantity    = 1;
        $value       = 44.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // third product
        $id          = '3';
        $description = 'Outro produto da minha loja';
        $quantity    = 7;
        $value       = 3.10;
        $this->process->addProduct($id, $description, $quantity, $value);

        // total amount is 108.8 - trying to give 110 as discount
        $this->process->setDiscount(
            110,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_VALUE
        );

    }

    /**
     * Checks that other charges are set
     *
     * @return void
     */
    function testSetOtherCharges()
    {
        $this->process->setOtherCharges(42.5);
        $this->assertEquals(42.5, $this->process->getOtherCharges());
    }
    /**
     * Checks for invalid parameters when setting a value for other charges
     *
     * @return void
     */
    function testSetOtherChargesInvalidParameter()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';
        $this->process->setOtherCharges(null);
    }

    /**
     * Checks if the total amount is calculated correctly
     *
     * @return void
     */
    function testGetTotalAmount()
    {
        $id          = '12';
        $quantity    = 3;
        $description = 'Outro produto legal';
        $value       = 30.00;

        // adds the product
        $this->process->addProduct($id, $description, $quantity, $value);

        // sets a 10 percent discount over the products
        $this->process->setDiscount(
            10,
            Payment_PagamentoCerto_Order::DISCOUNT_TYPE_PERCENTAGE
        );

        // sets the shipping value
        $this->process->setShippingValue(15.20);

        // sets other charges
        $this->process->setOtherCharges(5.30);

        $this->assertEquals(101.5, $this->process->getTotalAmount());

    }

    /**
     * Checks for an invalid order ID
     *
     * @return void
     */
    function testSetOrderIdInvalidParameter()
    {
        $this->expectedException
            = 'Payment_PagamentoCerto_InvalidParameterException';
        $this->process->setOrderId('oa4frd');
        $this->assertEquals('oa4frd', $this->process->getOrderId());
    }

    /**
     * Checks if an order ID is set
     *
     * @return void
     */
    function testSetOrderId()
    {
        $this->process->setOrderId(40);
        $this->assertEquals(40, $this->process->getOrderId());
    }

}

?>
