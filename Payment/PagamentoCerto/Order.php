<?php
/**
 * This file contains the Services_PagamentoCerto_Order class
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

require_once 'Exceptions.php';

/**
 * Class representing an Order
 *
 * This class provides an easy-to-use collection of methods that
 * will help with the process of submitting an order to the payment
 * gateway. It works as a shopping cart, so you add products,
 * select payment method, collect buyer information, apply discounts
 * and calculate the total amount of the order.
 *
 * When you want to begin a transaction with the payment gateway,
 * an instance of this class is one of the parameters, since it will
 * contain all the order information.
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_Order
{
    /**
     * Constants representing payment methods
     */
    const PAYMENT_METHOD_INVOICE = 0;
    const PAYMENT_METHOD_CC_VISA = 1;

    /**
     * Constants representing buyer types
     */
    const BUYER_TYPE_PERSON  = 0;
    const BUYER_TYPE_COMPANY = 1;

    /**
     * Constants representing address types
     */
    const ADDRESS_TYPE_SHIPPING = 0;
    const ADDRESS_TYPE_BILLING  = 1;

    /**
     * Constants representing discount types
     */
    const DISCOUNT_TYPE_VALUE      = 0;
    const DISCOUNT_TYPE_PERCENTAGE = 1;

    /**
     * Buyer type
     *
     * @property int
     */
    protected $buyerType;

    /**
     * Shipping value for the order
     *
     * @property float
     */
    protected $shippingValue;

    /**
     * Selected payment method
     *
     * @property int
     */
    protected $paymentMethod;

    /**
     * Array contaning all the products of the order
     *
     * @property array
     */
    protected $products = array();

    /**
     * Array containing the buyer's personal info
     *
     * @property array
     */
    protected $buyerInfo = array();

    /**
     * Array containing shipping ingo
     *
     * @property array
    */
    protected $shippingAddress = array();

    /**
     * Array describing current payment methods
     *
     * @property array
     */
    protected $supportedPaymentMethods = array(
        self::PAYMENT_METHOD_INVOICE,
        self::PAYMENT_METHOD_CC_VISA
    );

    /**
     * Additional charges of the order
     *
     * @property float
     */
    protected $otherCharges;

    /**
     * The order ID
     *
     * @property int
     */
    protected $orderId;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        // nothing to see here
    }

    /**
     * Defines the order ID
     *
     * @param int $id Order id
     *
     * @return void
     */
    public function setOrderId($id)
    {
        if (!is_int($id)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'order id must be an integer or floating point number'
            );
        }
        $this->orderId = $id;
    }

    /**
     * Returns the order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Sets all buyer-related info, except for shipping
     *
     * @param array  $buyerInfo Array containing the buyer's personal info
     * @param string $buyerType The buyer type (use a class constant)
     *
     * @return void
     */
    public function setBuyerInfo(array $buyerInfo, $buyerType = self::BUYER_TYPE_PERSON)
    {
        $required = array('name', 'email', 'cpf');

        $this->buyerType = $buyerType;

        if ($this->buyerType === self::BUYER_TYPE_COMPANY) {
            $required[] = 'cnpj';
            $required[] = 'companyName';
        }

        foreach ($required as $field) {
            if (!array_key_exists($field, $buyerInfo) || empty($buyerInfo[$field])) {
                throw new Payment_PagamentoCerto_MissingParameterException(
                    "buyer's {$field} is required"
                );
            }
        }

        $this->buyerInfo = $buyerInfo;
    }

    /**
     * Returns an array containing the buyer's personal info
     *
     * @return array
     */
    public function getBuyerInfo()
    {
        return $this->buyerInfo;
    }

    /**
     * Returns the buyer type
     *
     * @return int
     */
    public function getBuyerType()
    {
        return $this->buyerType;
    }

    /**
     * Sets the payment info
     *
     * @param int $paymentMethod The selected payment method (use a class constant)
     *
     * @return void
     */
    public function setPaymentMethod($paymentMethod)
    {
        if (in_array($paymentMethod, $this->supportedPaymentMethods)) {
            $this->paymentMethod = $paymentMethod;
        } else {
            throw new Payment_PagamentoCerto_InvalidPaymentMethodException(
                'specified payment method is invalid'
            );
        }

    }

    /**
     * Adds a product to the shopping cart
     *
     * @param string $id          Product ID
     * @param string $description Short description of the product
     * @param int    $quantity    Number of items for this product
     * @param float  $value       Product value
     *
     * @return void
     */
    public function addProduct($id, $description, $quantity, $value)
    {
        if (empty($id)) {
            throw new Payment_PagamentoCerto_MissingParameterException(
                'product id not specified'
            );
        }

        if (empty($description)) {
            throw new Payment_PagamentoCerto_MissingParameterException(
                'product description not specified'
            );
        }

        if (empty($quantity)) {
            throw new Payment_PagamentoCerto_MissingParameterException(
                'product quantity not specified'
            );
        }

        if (empty($value)) {
            throw new Payment_PagamentoCerto_MissingParameterException(
                'product value not specified'
            );
        }

        if (!is_int($quantity)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'product quantity must be an integer number'
            );
        }

        if (!is_int($value) && !is_float($value)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'product value must be an integer or floating point number'
            );
        }

        $totalAmount = round($quantity * $value, 2);


        if (isset($this->products[$id])) {
            if ($this->products[$id]['value'] !== $value) {
                throw new Payment_PagamentoCerto_ProductValueMismatchException(
                    'the product with id ' . $id .
                    ' has value ' . $this->products[$id]['value']
                );
            }
            $this->products[$id]['quantity']    += $quantity;
            $this->products[$id]['totalAmount'] += $totalAmount;
        } else {
            $this->products[$id]['value']       = $value;
            $this->products[$id]['quantity']    = $quantity;
            $this->products[$id]['description'] = $description;
            $this->products[$id]['totalAmount'] = $totalAmount;
        }

    }

    /**
     * Returns an array of all the products of the order
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Returns the total amount for a specified product
     *
     * @param string $id Product ID
     *
     * @return float
     */
    public function getProductTotalAmount($id)
    {
        if (isset($this->products[$id]['totalAmount'])) {
            return $this->products[$id]['totalAmount'];
        } else {
            return null;
        }
    }

    /**
     * Returns the current quantity for the specified product
     *
     * @param string $id Product ID
     *
     * @return int
     */
    public function getProductQuantity($id)
    {
        if (isset($this->products[$id]['quantity'])) {
            return $this->products[$id]['quantity'];
        } else {
            return null;
        }
    }

    /**
     * Sets the shipping info
     *
     * @param array $shippingAddress Array containing shipping info
     *
     * @return void
     */
    public function setShippingAddress(array $shippingAddress = array())
    {
        $this->setAddress(
            Payment_PagamentoCerto_Order::ADDRESS_TYPE_SHIPPING,
            $shippingAddress
        );
    }

    /**
     * Sets the billing information
     *
     * @param array $billingAddress Array containing billing info
     *
     * @return void
     */
    public function setBillingAddress(array $billingAddress = array())
    {
        $this->setAddress(
            Payment_PagamentoCerto_Order::ADDRESS_TYPE_BILLING,
            $billingAddress
        );
    }

    /**
     * Returns the billing information
     *
     * @return array
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Sets the payment method as invoice
     *
     * @return void
     */
    public function setPaymentMethodAsInvoice()
    {
        $this->setPaymentMethod(
            Payment_PagamentoCerto_Order::PAYMENT_METHOD_INVOICE
        );
    }

    /**
     * Sets the payment method as VISA credit card
     *
     * @return void
     */
    public function setPaymentMethodAsCreditCardVisa()
    {
        $this->setPaymentMethod(
            Payment_PagamentoCerto_Order::PAYMENT_METHOD_CC_VISA
        );
    }

    /**
     * Returns the payment method
     *
     * @return int
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Returns the shipping info of the order
     *
     * @return array
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Returns the shipping value
     *
     * @return float
     */
    public function getShippingValue()
    {
        return $this->shippingValue;
    }

    /**
     * Defines the shipping value for the order
     *
     * @param float $value Shipping value
     *
     * @return void
     */
    public function setShippingValue($value)
    {
        if (!is_int($value) && !is_float($value)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'shipping value ' . $value . ' is not valid'
            );
        } else {
            $this->shippingValue = $value;
        }
    }

    /**
     * Defines additional charges for the order
     *
     * @param float $value Additional charges
     *
     * @return void
     */
    public function setOtherCharges($value)
    {
        if (!is_int($value) && !is_float($value)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'other charges value ' . $value . ' is not valid'
            );
        } else {
            $this->otherCharges = $value;
        }
    }

    /**
     * Returns the additional charges value
     *
     * @return float
     */
    public function getOtherCharges()
    {
        return $this->otherCharges;
    }

    /**
     * Returns the sub total of the order (only product values)
     *
     * @return float
     */
    public function getOrderSubTotal()
    {
        $subTotal = null;

        foreach ($this->products as $product) {
            $subTotal += $product['totalAmount'];
        }

        return $subTotal;
    }

    /**
     * Returns the final total amount of the order
     *
     * Calculates: products, shipping, other charges and discount
     *
     * @return int|float
     */
    public function getTotalAmount()
    {
        $products = $this->applyDiscount(
            $this->getOrderSubTotal()
        );

        $totalAmount =
            $products + $this->getShippingValue() + $this->getOtherCharges();

        return $totalAmount;
    }

    /**
     * Applies discount to a given value and returns the result
     *
     * @param int|float $value Discount value
     *
     * @return int|float
     */
    public function applyDiscount($value)
    {
        if (!is_int($value) && !is_float($value)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'value ' . $discount . ' is not valid'
            );
        }

        $result = null;

        if (isset($this->discountType) && isset($this->discountValue)) {

            switch ($this->discountType) {
            case self::DISCOUNT_TYPE_PERCENTAGE:
                $result = $this->applyDiscountPercentage($value);
                break;
            case self::DISCOUNT_TYPE_VALUE:
            default:
                $result = $this->applyDiscountValue($value);
                break;
            }

        } else {
            // if there's no discount, return the same value
            return $value;
        }

        return $result;

    }

    /**
     * Applies a flat value discount and returns the result
     *
     * @param int|float $value Value that will recieve the discount
     *
     * @return int|float
     */
    protected function applyDiscountValue($value)
    {
        if ($this->discountValue > $value) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'discount should not be greater than the value'
            );
        }

        return ($value - $this->discountValue);
    }

    /**
     * Applies a discount set as a percentage
     *
     * @param int|float $value Value that will recieve the discount
     *
     * @return int|float
     */
    protected function applyDiscountPercentage($value)
    {
        $multiplier = $this->discountValue / 100;
        $percentage = $value * $multiplier;

        return round($value - $percentage, 2);
    }

    /**
     * Applies the discount to the order sub total and returns the result
     *
     * @return int|float
     */
    public function getCalculatedDiscount()
    {
        $value = $this->getOrderSubTotal();
        return $value - $this->applyDiscount($value);
    }

    /**
     * Sets discount for the order
     *
     * @param float|int $discount     Discount value
     * @param int       $discountType Discount type (use a class constant)
     *
     * @return void
     */
    public function setDiscount($discount, $discountType = self::DISCOUNT_TYPE_VALUE)
    {
        if (!is_int($discount) && !is_float($discount)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'shipping value ' . $discount . ' is not valid'
            );
        }

        // if we have no products, discount can't be set
        if (count($this->products) === 0) {
            throw new Payment_PagamentoCerto_NoProductsException(
                'cannot set discount without products'
            );
        }

        switch ($discountType) {
        case self::DISCOUNT_TYPE_PERCENTAGE:
            $this->setDiscountPercentage($discount);
            break;
        case self::DISCOUNT_TYPE_VALUE:
        default:
            $this->setDiscountValue($discount);
            break;
        }

    }

    /**
     * Defines the discount as a flat value
     *
     * @param int|float $discount Discount value
     *
     * @return void
     */
    public function setDiscountValue($discount)
    {
        if (!is_int($discount) && !is_float($discount)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'discount value ' . $discount . ' is not valid'
            );
        }

        // discount must be a positive value
        if ($discount >= 0) {

            // discount cannot be greater than the subtotal
            if ($discount < $this->getOrderSubTotal()) {
                $this->discountType  = self::DISCOUNT_TYPE_VALUE;
                $this->discountValue = $discount;
            } else {
                throw new Payment_PagamentoCerto_InvalidParameterException(
                    'discount cannot be greater than the subtotal'
                );
            }

        } else {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'discount must be a positive value'
            );
        }
    }

    /**
     * Defines the discount as percentage
     *
     * @param int|float $discount Discount percentage
     *
     * @return void
     */
    public function setDiscountPercentage($discount)
    {
        if (!is_int($discount) && !is_float($discount)) {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'discount value ' . $discount . ' is not valid'
            );
        }

        // is it a valid percentage?
        if (($discount >= 0) && ($discount <= 100)) {
            $this->discountType  = self::DISCOUNT_TYPE_PERCENTAGE;
            $this->discountValue = $discount;
        } else {
            throw new Payment_PagamentoCerto_InvalidParameterException(
                'discount value ' . $discount . ' is not valid'
            );
        }

    }

    /**
     * Returns the discount type
     *
     * @return int
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * Returns the discount value
     *
     * @return int|float
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * Sets address information based on $addressType
     *
     * @param int   $addressType The address type (use a class constant)
     * @param array $addressInfo Array containing the address
     *
     * @return void
     */
    protected function setAddress($addressType, $addressInfo = array())
    {
        // required fields
        $required = array(
            'address',
            'addressNumber',
            'district',
            'city',
            'zipCode',
            'state'
        );

        // do we have all the required fields?
        foreach ($required as $field) {

            if (!array_key_exists($field, $addressInfo) ||
                empty($addressInfo[$field])) {

                throw new Payment_PagamentoCerto_MissingParameterException(
                    "{$field} parameter is mandatory."
                );
            }

        }

        // which type of address are we setting?
        switch ($addressType) {
        case self::ADDRESS_TYPE_BILLING:
            $this->billingAddress = $addressInfo;
            break;
        case self::ADDRESS_TYPE_SHIPPING:
            $this->shippingAddress = $addressInfo;
            break;
        default:
            throw new Payment_PagamentoCerto_Exception(
                'Unknown address type: ' . $addressType
            );
            break;
        }
    }

}

?>