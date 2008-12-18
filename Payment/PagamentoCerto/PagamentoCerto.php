<?php
/**
 * This file contains all the Payment_PagamentoCerto class
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

require 'Payment/PagamentoCerto/XmlBuilder.php';
require 'Payment/PagamentoCerto/Transaction.php';
require 'Payment/PagamentoCerto/Order.php';

/**
 * This class is responsible for sending/receiving requests to/from the
 * payment gateway.
 *
 * The basic flow of the process of a payment transaction is:
 *
 * 1 - Create an order (Payment_PagamentoCerto_Order)
 * 2 - Provide the order object to this class constructor
 * 3 - Start a transaction with startTransaction() method
 * 4 - Retrieve the transaction id (in case of failure, an exception is thrown)
 * 5 - Redirect the user to the payment gateway URL
 * 6 - After the user is done in the payment gateway, he/she will be redirected
 *     back to your store.
 * 7 - Fetch the transaction ID sent by the payment gateway ($_GET["tid"]) and
 *     display information about the order (getTransactionInfo())
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto
{
    /**
     * Object containing order information
     *
     * @property Payment_PagamentoCerto_Order
     */
    protected $order;

    /**
     * Transaction ID
     *
     * @property string
     */
    protected $transactionId;

    /**
     * Seller API key
     *
     * @property string
     */
    protected $sellerApiKey;

    /**
     * Postback URL of the store
     *
     * This is where the payment gateway will redirect the user to, providing
     * the transaction ID via GET
     *
     * @property string
     */
    protected $storePostbackUrl;

    /**
     * Payment gateway webservice URL
     *
     * @property string
     */
    protected $sellerWebserviceUrl
        = 'https://www.pagamentocerto.com.br/vendedor/vendedor.asmx?WSDL';

    /**
     * Payment gateway webservice URL
     *
     * @property string
     */
    protected $paymentGatewayUrl
        = 'https://www.pagamentocerto.com.br/pagamento/pagamento.aspx';

    /**
     * Class constructor
     *
     * @param string $sellerApiKey        API key (provided by PagamentoCerto)
     * @param string $storePostbackUrl    Store postback URL
     * @param string $sellerWebserviceUrl Seller webservice URL
     * @param string $paymentGatewayUrl   Payment gateway URL
     *
     * @return void
     */
    public function __construct($sellerApiKey, $storePostbackUrl = '',
        $sellerWebserviceUrl = '', $paymentGatewayUrl = ''
    )
    {
        $this->sellerApiKey     = $sellerApiKey;
        $this->storePostbackUrl = $storePostbackUrl;

        if ($sellerWebserviceUrl !== '') {
            $this->sellerWebserviceUrl = $sellerWebserviceUrl;
        }

        if ($paymentGatewayUrl !== '') {
            $this->paymentGatewayUrl = $paymentGatewayUrl;
        }

    }

    /**
     * Starts a payment transaction and returns the transaction ID
     *
     * @param Payment_PagamentoCerto_Order $order Order information
     *
     * @return string Transaction ID
     */
    public function startTransaction(Payment_PagamentoCerto_Order $order)
    {
        $this->order = $order;

        $soap = $this->getSoapClient($this->sellerWebserviceUrl);

        $soapParams                = new stdClass();
        $soapParams->xml           = $this->getXml();
        $soapParams->chaveVendedor = $this->sellerApiKey;
        $soapParams->urlRetorno    = $this->storePostbackUrl;

        $result = $soap->IniciaTransacao($soapParams);

        $xmlResult = new SimpleXmlElement($result->IniciaTransacaoResult);

        if ($xmlResult->Transacao->CodRetorno != 0) {
            throw new Payment_PagamentoCerto_SoapException(
                $xmlResult->Transacao->MensagemRetorno,
                intval($xmlResult->Transacao->CodRetorno)
            );
        } else {
            $this->transactionId = $xmlResult->Transacao->IdTransacao;
            return $this->transactionId;
        }

    }

    /**
     * Returns information about a transaction
     *
     * @param string $transactionId Transaction ID
     *
     * @return Payment_PagamentoCerto_Transaction
     */
    public function getTransactionInfo($transactionId)
    {
        if (empty($transactionId)) {
            if (empty($this->transactionId)) {
                throw new Payment_PagamentoCerto_NoTransactionId(
                    'transaction id not specified'
                );
            } else {
                $transactionId = $this->transactionId;
            }

        }

        $soapClient = $this->getSoapClient($this->sellerWebserviceUrl);

        $soapParams                = new stdClass();
        $soapParams->chaveVendedor = $this->sellerApiKey;
        $soapParams->idTransacao   = $transactionId;

        $result = $soapClient->ConsultaTransacao($soapParams);

        $xmlResult = new SimpleXmlElement($result->ConsultaTransacaoResult);

        return new Payment_PagamentoCerto_Transaction($xmlResult);

    }

    /**
     * Redirects the user to the payment gateway
     *
     * @param string $transactionId Transaction ID
     *
     * @return void
     */
    public function redirectToPaymentGateway($transactionId)
    {
        if (!empty($this->transactionId)) {
            header(
                'Location: ' . $this->paymentGatewayUrl .
                '?tdi=' . $this->transactionId
            );
            exit;
        }
    }

    /**
     * Returns an XML containing the order information
     *
     * @return string
     */
    protected function getXml()
    {
        $xmlBuilder = new Payment_PagamentoCerto_XmlBuilder($this->order);
        return $xmlBuilder->getXml();

    }

    /**
     * Returns an instance of a SoapClient
     *
     * @param string $url Webservice URL (WSDL mode)
     *
     * @return SoapClient
     */
    protected function getSoapClient($url)
    {
        $soapOptions = array(
            'trace' => true,
            'exceptions' => true,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 1000
        );

        // WSDL mode
        $soapClient = new SoapClient($url, $soapOptions);

        return $soapClient;

    }

}

?>