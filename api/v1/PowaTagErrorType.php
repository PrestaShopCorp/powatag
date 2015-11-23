<?php
/**
* 2007-2015 PrestaShop.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*
*  @version  Release: $Revision: 7776 $
*
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License      = array('code' => 'OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

abstract class PowaTagErrorType
{
    public static $BAD_REQUEST = array('code' => '400101', 'response' => 400, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400);

/** E-com Connectivity Error */
    // AIM is unable to connect the the e-commerce/PSP server
    public static $FAIL_LOGIN_TO_ECOM = array('code' => '200101', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Fail to login to eCom Server
    // The user name or password is incorrect
    public static $FAIL_CONNECT_TO_ECOM = array('code' => '200102', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Fail to connect to eCom Server
    public static $UNKNOWN_ECOM_ERROR = array('code' => '200103', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Unknow eCom error
    /** Others */
    public static $SLOW_CONNECTION = array('code' => '200104', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Slow Connection
    // Wrong PSP certificate
    public static $CONNECTION_TIMEOUT = array('code' => '200105', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Connection Timeout

/** Merchant Error */
    // Shop ID is Wrong
    public static $MERCHANT_NOT_FOUND = array('code' => '200201', 'response' => 200, 'legCode' => 'CONFIG_ERROR', 'legResponse' => 500); // Merchant Not Found
    // The Merchant Account is no longer using this E-com Platform or The Account is Mal-functioning etc
    public static $MERCHANT_DISABLED = array('code' => '200201', 'response' => 200, 'legCode' => 'CONFIG_ERROR', 'legResponse' => 500); // Merchant is Disabled
    /** SKU Error */
    public static $SKU_NOT_FOUND = array('code' => '200210', 'response' => 200, 'legCode' => 'SKU_NOT_FOUND', 'legResponse' => 404); // SKU not found
    // The SKU is no longer selling in this shop
    public static $SKU_DISABLED = array('code' => '200211', 'response' => 200, 'legCode' => 'SKU_NOT_FOUND', 'legResponse' => 404); // SKU is Disabled
    // Cannot find the price of the product
    public static $PRICE_NOT_FOUND = array('code' => '200212', 'response' => 200, 'legCode' => 'SKU_NOT_FOUND', 'legResponse' => 404); // Price Not Found
    // Other unexpected error related to product details
    public static $OTHER_PRODUCT_ERROR = array('code' => '200213', 'response' => 200, 'legCode' => 'SKU_NOT_FOUND', 'legResponse' => 404); // Other Product Error
    /** Shipping Error */
    public static $MERCHANT_WRONG_COUNTRY = array('code' => '200220', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Does not Ship to This Country
    public static $MERCHANT_WRONG_STATE = array('code' => '200221', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Does not Ship to This State
    public static $MERCHANT_WRONG_CITY = array('code' => '200222', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Does not Ship to This City
    public static $MERCHANT_WRONG_STREET = array('code' => '200223', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Does not Ship to This Street
    public static $MERCHANT_WRONG_ZIP_CODE = array('code' => '200224', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Does not Ship to This Zip Code/Post Code
    public static $MERCHANT_WRONG_ADDRESS = array('code' => '200225', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchants Does not Ship to this Address
    public static $MERCHANT_SHIPS_TO_ITS_COUNTRY = array('code' => '200226', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Only Ships to its Country of Registration
    // Rare Case but Does Exist; I've seen Korean and Chinese Websites Shipping Only to Korean and Chinese
    public static $MERCHANT_SHIPS_TO_USER_COUNTRY = array('code' => '200227', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant only Ships to User Country of Registration
    public static $ADDRESS_REQUIRED = array('code' => '200228', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Address is Required to Calculate Shipping
    // Merchant does not send to this address; City is Missing; Country is Missing etc
    public static $ADDRESS_FORMAT_ERROR = array('code' => '200229', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Address Format Error
    public static $OTHER_ADDRESS_ERROR = array('code' => '200230', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Other Address Error
    /** Quantity Error */
    public static $QUANTITY_FORMAT_ERROR = array('code' => '200240', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // Quantity Format Error
    public static $QUANTITY_ZERO_ERROR = array('code' => '200241', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // Quantity Cannot be Less Than Zero
    public static $OTHER_QUANTITY_ERROR = array('code' => '200242', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // Other Quantity Error
    /** Stock Error */
    public static $SKU_OUT_OF_STOCK = array('code' => '200243', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // SKU Out of Stock
    public static $INSUFFICIENT_STOCK = array('code' => '200244', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // Insufficient Stock
    public static $OTHER_STOCK_ERROR = array('code' => '200245', 'response' => 200, 'legCode' => 'NOT_IN_STOCK', 'legResponse' => 400); // Other Stock Error
    /** Others */
    public static $INTERNAL_ERROR = array('code' => '200290', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // An Internal Error Has Occurred
    public static $INVALID_DATA = array('code' => '200291', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Data
    public static $INVALID_REQUEST = array('code' => '200292', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Request
    public static $INVALID_LOYALTY_ID = array('code' => '200293', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Loyalty Id

    /** Transaction Error */
    public static $MERCHANT_NOT_FOUND_TRANSACTION = array('code' => '200301', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Not Found
    public static $WRONG_MID = array('code' => '200302', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong/Incorrect MID
    public static $WRONG_PPS_KEY = array('code' => '200303', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong PPS Encryption Key
    public static $WRONG_CERTIFICATE = array('code' => '200304', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong Certificate
    public static $UNABLE_TO_REACH_ISSUER = array('code' => '200305', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Unable to Reach Issuer
    public static $UNABLE_TO_REACH_SWITCH = array('code' => '200306', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Unable to Reach Switch
    public static $UNKNOWN_GATEWAY_ERROR = array('code' => '200307', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Unknown Gateway Error
    public static $GATEWAY_TIMEOUT = array('code' => '200308', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Gateway Timeout
    public static $TRANSACTION_TYPE_NOT_SUPPORTED = array('code' => '200309', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // This Transaction Type is Not Supported
    public static $PAYMENT_METHOD_NOT_SUPPORTED = array('code' => '200310', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // This Payment Method is Not Supported
    public static $CURRENCY_NOT_SUPPORTED = array('code' => '200311', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // This Currency is Not Supported
    public static $CURRENCY_PAIR_NOT_SUPPORTED = array('code' => '200312', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Cross Border currency pair is not supported
    public static $FOREIGN_CARDS_NOT_SUPPORTED = array('code' => '200313', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Foreign Cards/Cross Border are Not Supported
    public static $MERCHANT_ONLY_BILLING_ADDRESS = array('code' => '200314', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Merchant Only Ships to Billing Address
    public static $INVALID_TRANSACTION = array('code' => '200315', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Transaction
    public static $INVALID_AMOUNT = array('code' => '200316', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Amount
    public static $INVALID_CURRENCY_CODE = array('code' => '200317', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Currency Code
    public static $FIELD_MISSING_AMOUNT = array('code' => '200318', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing; Amount
    public static $FIELD_MISSING_CURRENCY = array('code' => '200319', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing; Currency
    public static $FIELD_MISSING_ADDRESS = array('code' => '200320', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing; Shipping Address
    public static $FIELD_MISSING_CITY = array('code' => '200321', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing; Shipping City
    public static $FIELD_MISSING_COUNTRY = array('code' => '200322', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing; Shipping Country
    public static $FAILED_TO_PLACE_ORDER = array('code' => '200323', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Failed to Place Order
    public static $DUPLICATED_TRANSACTION = array('code' => '200324', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Duplicated Transaction
    public static $INVALID_BILLING_ADDRESS = array('code' => '200325', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Billing Address
    public static $INVALID_BILLING_CITY = array('code' => '200326', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Billing City
    public static $INVALID_BILLING_POSTCODE = array('code' => '200327', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Billing Postcode
    public static $INVALID_BILLING_COUNTRY = array('code' => '200328', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Billing Country
    public static $NO_CHECKING_ACCOUNT = array('code' => '200400', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // No Checking Account
    public static $INVALID_CARD_NUMBER = array('code' => '200401', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Card Number
    public static $WRONG_HOLDER_NAME = array('code' => '200402', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong Card Holder Name
    public static $WRONG_EXPIRY_DATE = array('code' => '200403', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong Expiry Date
    public static $WRONG_CVV = array('code' => '200404', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Wrong CVV
    public static $INVALID_CARD = array('code' => '200405', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Card
    public static $FAILED_AVS = array('code' => '200406', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Failed AVS
    public static $AVS_NOT_SUPPORTED_CARD_TYPE = array('code' => '200407', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid AVS/AVS Not Supported For This Card Type
    public static $AVS_NOT_SUPPORTED_ISSUER = array('code' => '200408', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid AVS/AVS Not Supported For This Issuer
    public static $AVS_NOT_SUPPORTED = array('code' => '200409', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // AVS Not Supported
    public static $FIELD_MISSING_HOLDER_NAME = array('code' => '200410', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing: Card Holder Name
    public static $FIELD_MISSING_EXPIRY_DATE = array('code' => '200411', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing: Expiry Date
    public static $FIELD_MISSING_CVV = array('code' => '200412', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing: CVV
    public static $FIELD_MISSING_CARD_DATA = array('code' => '200413', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Field Missing: Card Data
    public static $CARD_DATA_MISSING_FIELDS = array('code' => '200414', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Card Data missing fields
    public static $INSUFFICIENT_FUND = array('code' => '200415', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Insufficient Fund
    public static $EXPIRED_CARD = array('code' => '200416', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Expired Card
    public static $RESTRICTED_CARD = array('code' => '200417', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Restricted Card
    public static $LOST_CARD = array('code' => '200418', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Lost Card
    public static $STOLEN_CARD = array('code' => '200419', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Stolen Card
    public static $TEMPORARY_HOLD = array('code' => '200420', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Temporary Hold
    public static $CALL_ISSUER = array('code' => '200421', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Call Issuer
    /** Payment Error */
    public static $PAYMENT_DECLINED = array('code' => '200500', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Payment Declined
    public static $DECLINED_SUSPICIOUS_CARD = array('code' => '200501', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined, Suspicious Card
    public static $DECLINED_LOST_CARD = array('code' => '200502', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined, Lost Card
    public static $AUTHORIZED_SUSPICIOUS_CARD = array('code' => '200503', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Authorized: Suspicious Card
    public static $AUTHORIZED_SUSPICIOUS_TRANSACTION = array('code' => '200504', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Authorized: Suspicious Transaction
    public static $DECLINED_CALL_ISSUER = array('code' => '200505', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined by Issuer; Call Issuer
    public static $DECLINED_UNEXPECTED_ERROR = array('code' => '200506', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined by Issuer; Unexpected Error
    public static $DECLINED_RETRY = array('code' => '200507', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined by Issuer; Retry Transaction
    public static $ISSUER_REQUEST_RETRY = array('code' => '200508', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Issuer Request to Retry Transaction
    public static $DECLINED_BY_GATEWAY = array('code' => '200509', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Declined by Gateway
    public static $SYSTEM_ERROR_RETRY = array('code' => '200510', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // System Error, Please Retry the Transaction
    public static $SYSTEM_ERROR_DO_NOT_RETRY = array('code' => '200511', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // System Error, Please Do Not Retry the Transaction
    /** Capture Error */
    public static $AUTHORIZATION_EXPIRED = array('code' => '200600', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // The authorization to be Settled has Expired
    public static $MAX_NUMBER_OF_CREDITS_REACHED = array('code' => '200601', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Maximum Number of Credits Allowed for Settlement Reached
    public static $BATCH_SUBMISSION_FAILED = array('code' => '200602', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Batch Submission Failed
    public static $BATCH_ALREADY_SUBMITTED = array('code' => '200603', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Batch Already Submitted
    public static $BATCH_FORMAT_ERROR = array('code' => '200604', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Batch Format Error
    public static $OTHER_CAPTURE_ERROR = array('code' => '200605', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Other Capture Error
    /** Refund Error */
    public static $REFUND_DECLINED = array('code' => '200606', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Refund Declined
    public static $TRANSACTION_FORMAT_ERROR = array('code' => '200607', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Transaction Format Error
    public static $REFUND_FORMAT_ERROR = array('code' => '200608', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Refund Format Error
    public static $VOID_FORMAT_ERROR = array('code' => '200609', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Void Format Error
    public static $OTHER_REFUND_ERROR = array('code' => '200610', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Other Refund Error
    public static $OTHER_VOID_ERROR = array('code' => '200611', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Other Void Error
    /** Others */
    public static $EXCEEDED_MAXIMUM_ATTEMPTS = array('code' => '200900', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Exceeded Maximum Attempts
    public static $INVALID_TRANSACTION_DATA = array('code' => '200901', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid Data
    public static $NO_RESPONSE = array('code' => '200902', 'response' => 200, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // No Response

/** HTTP 400 Bad Request */
    // json not in proper format
    public static $MALFORMED_REQUEST = array('code' => '400101', 'response' => 400, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Malformed Request
    /** Use {@link CustomFormattedException} for handle missing field */
    public static $MISSING_FIELD = array('code' => '400102', 'response' => 400, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Missing Required field {0}
    /** Use {@link CustomFormattedException} for handle invalid field */
    public static $INVALID_FIELD = array('code' => '400103', 'response' => 400, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Invalid field {0}
    /** Use {@link CustomFormattedException} for handle format error.
     * e.g. new String[] {"CVV"', 'response' => 200); // (3 digit)"} */
    public static $FORMAT_ERROR = array('code' => '400404', 'response' => 400, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // {0} format error, {0} should be in {1}

/** HTTP 403 Unauthorize request */
    // user pass to AIM not valid
    public static $CREDENTIAL_ERROR = array('code' => '401101', 'response' => 403, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Account Name/Password Error
    public static $ACCESS_DENIED = array('code' => '401102', 'response' => 403, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // User Doesn't Have the Permission to Access this AIM Resource

/** HTTP 500 Internal Error */
    // AIM Internal Error
    public static $UNEXPECTED_ERROR = array('code' => '500101', 'response' => 500, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Unexpected Error
    public static $COMPONENT_NOT_FOUND = array('code' => '500102', 'response' => 500, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Component Not Found
    public static $CONFIG_ERROR = array('code' => '500103', 'response' => 500, 'legCode' => 'BAD_REQUEST', 'legResponse' => 400); // Configuration Error
}
