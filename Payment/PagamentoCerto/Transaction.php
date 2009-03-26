<?php
/**
 * This file contains all the Payment_PagamentoCerto_Transaction class
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
 * Class representing a payment transaction
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_Transaction
{
    /**#@+
     * Values that represent the API's return codes
     */
    const NOT_FOUND            = 11;
    const NOT_PROCESSED        = 12;
    const IN_PROCESS           = 13;
    const EXPIRED              = 14;
    const PROCESSED            = 15;
    const ERROR                = 16;
    const PAYMENT_EXPIRED      = 17;
    const USER_EXIT            = 18;
    const USER_CANCELLED       = 19;
    const RETRY_LIMIT_EXCEEDED = 20;
    /**#@-*/

    /**
     * Transaction ID
     */
    protected $id;

    /**
     * UNIX timestamp representing the transaction date
     */
    protected $timestamp;

    /**
     * Transaction status code
     */
    protected $status;

    /**
     * Status message of the transaction
     */
    protected $statusMessage;

    /**
     * Array contaning buyer information
     */
    protected $buyerInfo = array();

    /**
     * Buyer type
     */
    protected $buyerType;

    /**
     * Payment type
     */
    protected $paymentType;

    /**
     * Order id
     */
    protected $orderId;

    /**
     * Total amount of the order
     */
    protected $totalAmount;

    /**
     * Class constructor
     *
     * @param SimpleXMLElement $info Transaction information
     *
     * @return void
     */
    public function __construct(SimpleXMLElement $info)
    {
        // Transaction info
        $this->id            = (string) $info->Transacao->IdTransacao;
        $this->status        = (int) (string) $info->Transacao->CodRetorno;
        $this->statusMessage = (string) $info->Transacao->MensagemRetorno;

        // date format is dd/m/aaaa hh:mm:ss
        $date = (string) $info->Transacao->Data;

        // separating into date and hour
        $tmp = explode(' ', $date);

        // splitting the date
        $date = explode('/', $tmp[0]);

        // splitting the hour
        $hour = explode(':', $tmp[1]);

        // creating the timestamp
        $this->timestamp = mktime(
            (int) $hour[0], // hour
            (int) $hour[1], // minutes
            (int) $hour[2], // seconds
            (int) $date[1], // month
            (int) $date[0], // day
            (int) $date[2]  // year
        );

        // Buyer info
        $this->buyerInfo["name"]  = (string) $info->Comprador->Nome;
        $this->buyerInfo["email"] = (string) $info->Comprador->Email;

        // Buyer type
        if ((string) $info->Comprador->TipoPessoa == "Juridica") {

            $this->buyerType = Payment_PagamentoCerto_Order::BUYER_TYPE_COMPANY;

            $this->buyerInfo["cnpj"] = (string) $info->Comprador->Cnpj;

        } else {

            $this->buyerType = Payment_PagamentoCerto_Order::BUYER_TYPE_PERSON;

            $this->buyerInfo["cpf"] = (string) $info->Comprador->Cpf;

        }

        // Payment info
        switch ((string) $info->Pagamento->Modulo) {
        case "CartaoCredito":
            if ((string) $info->Pagamento->Type == "Visa") {
                $this->paymentType =
                    Payment_PagamentoCerto_Order::PAYMENT_METHOD_CC_VISA;
            } else {
                // if there's no credit card type, defaults to invoice
                $this->paymentType =
                    Payment_PagamentoCerto_Order::PAYMENT_METHOD_INVOICE;
            }
            break;
        case "Boleto":
        default:
            $this->paymentType =
                Payment_PagamentoCerto_Order::PAYMENT_METHOD_INVOICE;
            break;
        }

        // Order info
        $this->orderId     = (int) (string) $info->Pedido->Numero;
        $this->totalAmount = intval((string) $info->Pedido->ValorTotal) / 100;
    }

    /**
     * Returns the transaction ID
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->id;
    }

    /**
     * Returns the order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Returns buyer information
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
     * Returns the payment type
     *
     * @return int
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Returns the date of the transaction in the specified format
     *
     * @param string $format Date format (http://www.php.net/date)
     *
     * @return string
     */
    public function getDate($format = 'd/m/Y H:i:s')
    {
        return date($format, $this->_timestamp);
    }

    /**
     * Returns the total amount of the order
     *
     * @return int|float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Returns the status code of the transaction
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Returns the status message of the transaction
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

}

?>
