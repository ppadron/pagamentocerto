<?php
/**
 * This file contains the Services_PagamentoCerto_XmlBuilder class
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

/**
 * Class that handles the creation of an XML according to
 * PagamentoCerto's API specs.
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_XmlBuilder
{
    private $_order;

    /**
     * Class constructor
     *
     * @param Payment_PagamentoCerto_Order $order Object containing the order data
     */
    function __construct(Payment_PagamentoCerto_Order $order)
    {
        $this->_order = $order;
    }

    /**
     * Returns a well-formed XML according to PagamentoCerto's API specs
     *
     * @return string
     */
    function getXml()
    {
        $xml = '';

        $xml .= $this->_getHeaderXml();

        $xml .= $this->_getBuyerInfoXml(
            $this->_order->getBuyerInfo(),
            $this->_order->getBuyerType()
        );

        // all values must be converted to cents
        $orderValues = array(
            'subTotal'      => $this->_order->getOrderSubTotal()      * 100,
            'shippingValue' => $this->_order->getShippingValue()      * 100,
            'otherCharges'  => $this->_order->getOtherCharges()       * 100,
            'discount'      => $this->_order->getCalculatedDiscount() * 100,
            'totalAmount'   => $this->_order->getTotalAmount()        * 100
        );

        $xml .= $this->_getOrderInfoXml(
            $this->_order->getOrderId(),
            $this->_order->getProducts(),
            $orderValues,
            $this->_order->getBillingAddress(),
            $this->_order->getShippingAddress()
        );

        $xml .= $this->_getFooterXml();

        return $xml;
    }

    /**
     * Returns the XML header
     *
     * @return string
     */
    private function _getHeaderXml()
    {
        // header
        $xml  = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= '<LocaWeb>';
        return $xml;
    }

    /**
     * Returns the buyer information part of the XML
     *
     * @param array $buyerInfo Buyer information
     * @param int   $buyerType Buyer type
     *
     * @return string
     */
    private function _getBuyerInfoXml($buyerInfo, $buyerType)
    {
        $xml  = "<Comprador>";
        $xml .= "<Nome>{$buyerInfo["name"]}</Nome>";
        $xml .= "<Email>{$buyerInfo["email"]}</Email>";
        $xml .= "<Cpf>{$buyerInfo["cpf"]}</Cpf>";

        if (array_key_exists('rg', $buyerInfo)) {
            $xml .= '<Rg>' . $buyerInfo["rg"] . '</Rg>';
        }

        $phoneExists    = array_key_exists('phone', $buyerInfo);
        $areaCodeExists = array_key_exists('areaCode', $buyerInfo);

        if ($phoneExists && $areaCodeExists) {
            $xml .= '<Ddd>' . $buyerInfo["areaCode"] . '</Ddd>';
            $xml .= "<Telefone>{$buyerInfo["phone"]}</Telefone>";
        }

        if ($buyerType === Payment_PagamentoCerto_Order::BUYER_TYPE_COMPANY) {
            $xml .= "<TipoPessoa>Juridica</TipoPessoa>";
            $xml .= "<RazaoSocial>{$buyerInfo["company_name"]}</RazaoSocial>";
            $xml .= "<Cnpj>{$buyerInfo["cnpj"]}</Cnpj>";
        } else {
            $xml .= "<TipoPessoa>Fisica</TipoPessoa>";
        }

        $xml .= "</Comprador>";

        return $xml;

    }

    /**
     * Returns the payment info part of the XML
     *
     * @param int $paymentType Payment type (PagamentoCerto_Order class constant)
     *
     * @return string
     */
    private function _getPaymentInfoXml($paymentType)
    {
        $xml = "<Pagamento>";

        switch ($paymentType) {
        case Payment_PagamentoCerto_Order::PAYMENT_METHOD_CC_VISA:
            $xml .= "<Modulo>CartaoCredito</Modulo>";
            $xml .= "<Tipo>Visa</Tipo>";
            break;
        case Payment_PagamentoCerto_Order::PAYMENT_METHOD_INVOICE:
        default:
            $xml .= "<Modulo>Boleto</Modulo>";
            break;
        }

        $xml .= "</Pagamento>";

        return $xml;

    }

    /**
     * Returns the order information as XML
     *
     * @param string $orderId         The order ID
     * @param array  $products        Array contaning all the products
     * @param array  $values          Array containing the values of the order
     * @param array  $billingAddress  Billing address
     * @param array  $shippingAddress Shipping address
     *
     * @return string
     */
    private function _getOrderInfoXml($orderId, $products, $values,
        $billingAddress, $shippingAddress
    )
    {
        $xml = '';

        // order
        $xml .= "<Pedido>";
        $xml .= "<Numero>{$orderId}</Numero>";
        $xml .= "<ValorSubTotal>{$values["subTotal"]}</ValorSubTotal>";

        $xml .= !empty($values["shippingValue"])?
            "<ValorFrete>{$values["shippingValue"]}</ValorFrete>" :
            '<ValorFrete>000</ValorFrete>';

        $xml .= !empty($values["otherCharges"])?
            "<ValorAcrescimo>{$values["otherCharges"]}</ValorAcrescimo>" :
            '<ValorAcrescimo>000</ValorAcrescimo>';

        $xml .= !empty($values["discount"])?
            "<ValorDesconto>{$values["discount"]}</ValorDesconto>" :
            '<ValorDesconto>000</ValorDesconto>';

        $xml .= "<ValorTotal>{$values["totalAmount"]}</ValorTotal>";

        // products
        $xml .= "<Itens>";

        foreach ($products as $id => $product) {

            $product["value"]       *= 100;
            $product["totalAmount"] *= 100;

            $xml .= "<Item>";
            $xml .= "<CodProduto>{$id}</CodProduto>";
            $xml .= "<DescProduto>{$product["description"]}</DescProduto>";
            $xml .= "<Quantidade>{$product["quantity"]}</Quantidade>";
            $xml .= "<ValorUnitario>{$product["value"]}</ValorUnitario>";
            $xml .= "<ValorTotal>{$product["totalAmount"]}</ValorTotal>";
            $xml .= "</Item>";
        }

        $xml .= "</Itens>";

        $xml .= $this->_getBillingAddressXml($billingAddress);

        $xml .= $this->_getShippingAddressXml($shippingAddress);

        $xml .= "</Pedido>";

        return $xml;

    }

    /**
     * Returns the billing information as XML
     *
     * @param array $billingAddress Billing address
     *
     * @return string
     */
    private function _getBillingAddressXml($billingAddress)
    {
        $xml  = '';
        $xml .= "<Cobranca>";
        $xml .= $this->_getAddressXml($billingAddress);
        $xml .= "</Cobranca>";
        return $xml;
    }

    /**
     * Returns the shipping address as XML
     *
     * @param array $shippingAddress Array containing the shipping address
     *
     * @return string
     */
    private function _getShippingAddressXml($shippingAddress)
    {
        $xml  = '';
        $xml .= "<Entrega>";
        $xml .= $this->_getAddressXml($shippingAddress);
        $xml .= "</Entrega>";
        return $xml;
    }

    /**
     * Returns the XML footer
     *
     * @return string
     */
    private function _getFooterXml()
    {
        $xml = "</LocaWeb>";
        return $xml;
    }

    /**
     * Returns an address as XML
     *
     * @param array $address Array contaning an address
     *
     * @return string
     */
    private function _getAddressXml($address)
    {
        $xml  = '';
        $xml .= "<Endereco>{$address["address"]}</Endereco>";
        $xml .= isset($address["addressAdditional"]) ?
            '<Complemento>' . $address["addressAdditional"] . '</Complemento>' :
            '';
        $xml .= "<Numero>{$address["addressNumber"]}</Numero>";
        $xml .= "<Bairro>{$address["district"]}</Bairro>";
        $xml .= "<Cidade>{$address["city"]}</Cidade>";
        $xml .= "<Cep>{$address["zipCode"]}</Cep>";
        $xml .= "<Estado>{$address["state"]}</Estado>";
        return $xml;
    }

}

?>