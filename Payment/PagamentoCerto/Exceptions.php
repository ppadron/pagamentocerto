<?php
/**
 * This file contains all the exception classes for this package
 *
 * PHP version 5
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */

require_once 'PEAR/Exception.php';

/**
 * Base exception class for this package
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_Exception extends PEAR_Exception
{

}

/**
 * Exception for missing parameters
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_MissingParameterException
    extends Payment_PagamentoCerto_Exception
{

}

/**
 * Exception for invalid parameters
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_InvalidParameterException
    extends Payment_PagamentoCerto_Exception
{

}

/**
 * Exception to be thrown when a product value doesn't match with the existing value
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_ProductValueMismatchException
    extends Payment_PagamentoCerto_Exception
{

}

/**
 * Exception to be thrown when a products are needed but there are no products
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_NoProductsException
    extends Payment_PagamentoCerto_Exception
{

}

/**
 * Generic exception for SOAP errors
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_SoapException
    extends Payment_PagamentoCerto_Exception
{

}

/**
 * Exception to be thrown when specified transaction ID doesn't exist
 *
 * @category Payment
 * @package  PagamentoCerto
 * @author   Pedro Padron <ppadron@w3p.com.br>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.html
 * @link     http://pear.php.net/packages/Payment/PagamentoCerto
 */
class Payment_PagamentoCerto_NoTransactionIdException
    extends Payment_PagamentoCerto_Exception
{

}

?>