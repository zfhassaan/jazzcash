<?php

namespace Zfhassaan\JazzCash;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use zfhassaan\jazzcash\Gateway\RefundPayment;

class JazzCash
{

    protected string $api_mode;
    protected string $apiUrl;
    protected string $merchant_id;
    protected string $return_url;
    protected string $password;
    protected string $timezone;

    //    Post Fields
    private int $amount;
    private string $billreference;
    private string $productdescription;
    private string $mpin;
    // Refund URL
    protected string $refundURL;

    /**
     * Constructor for JazzCash Payment Gateway
     * @return void
     */
    public function __construct()
    {
        $this->initConfig();
    }


    /**
     * Initialize Config Values
     * @return void
     */
    public function initConfig(): void
    {
        $this->api_mode = config('jazzcash.mode');
        $this->api_mode === 'sandbox' ? $this->setApiUrl(config('jazzcash.sandbox_api_url')) : $this->setApiUrl(config('jazzcash.api_url'));
        $this->merchant_id = config('jazzcash.merchant_id');
        $this->return_url = config('jazzcash.return_url');
        $this->password = config('jazzcash.password');
        $this->timezone = date_default_timezone_set('Asia/Karachi');
        $this->api_mode === 'sandbox' ? $this->setRefundApiUrl(config('jazzcash.refund_sandbox_url')) : $this->setRefundApiUrl(config('jazzcash.refund_production_url'));
        $this->mpin = config('jazzcash.mpin');
    }

    /**
     * Send Request
     * @return Response|Application|ResponseFactory
     */
    public function sendRequest(): Response|Application|ResponseFactory
    {
        $data['amount'] = $this->getAmount() * 100;  //Last two digits will be considered as Decimal
        $data['billRef'] = $this->getBillRefernce();
        $data['description'] = $this->getProductDescription();
        $data['isRegisteredCustomer'] = "No";
        $data['Language'] = "EN";
        $data['TxnCurrency'] = 'PKR';
        $data['TxnDateTime'] = date('YmdHis');
        $data['TxnExpiryDateTime'] = date('YmdHis', strtotime('+1 Days'));
        $data['TxnRefNumber'] = "TR" . date('YmdHis') . mt_rand(10, 100); // You can customize it (only Max 20 Alpha-Numeric characters)
        $data['TxnType'] = '';
        $data['Version'] = '2.0';
        $data['SubMerchantID'] = '';
        $data['BankID'] = '';
        $data['ProductID'] = '';
        $data['ppmpf_1'] = '';
        $data['ppmpf_2'] = '';
        $data['ppmpf_3'] = '';
        $data['ppmpf_4'] = '';
        $data['ppmpf_5'] = '';
        $data['securehash'] = $this->HashArray($data);
        return response($this->renderPage($data));
    }

    /**
     * Send Refund Request...
     *
     */
    public function RequestRefund($request): JsonResponse
    {
        $data['pp_Amount'] = $request['amount'];
        $data['pp_MerchantID'] = $this->merchant_id;
        $data['pp_MerchantMPIN'] = $this->mpin;
        $data['pp_Password'] = $this->password;
        $data['pp_TxnCurrency'] = 'PKR';
        $data['pp_TxnRefNo'] = $request['txnref'];
        $data['pp_SecureHash'] = $this->RefundHashArray($data);
//        $data['pp_SecureHash'] = "2C595361C2DA0E502D18BFBAA92CF4740330215E5E8AD0CF4489A64E7400B117";
        $url = $this->getRefundApiUrl();
        $refund = new RefundPayment();
        $result = $refund->sendRequest($url, $data);
        return response()->json($result);
    }

    /**
     * Refund Hash Array.
     */
    public function RefundHashArray($data)
    {
        $result = [];
        foreach($data as $key => $value)
        {
            $result[] = $value;
        }
        $resultString = '';
        foreach($result as $key => $value)
        {
            $resultString .= $value.'&';
        }
        $SortedArray = substr($resultString,0,-1);
        $key = implode('', $result); // Convert the $result array into a string
        return hash_hmac('sha256', $SortedArray, $key);
    }
    /**
     * Create Hash Array
     */
    public function HashArray($data): string
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = $value;
        }
        $resultString = implode('', $result);

        $SortedArray = '';

        for ($i = 0; $i < strlen($resultString); $i++) {
            if ($resultString[$i] != 'undefined' and $resultString[$i] != null and $resultString[$i] != "") {
                $SortedArray .= "&" . $resultString[$i];
            }
        }

        $key = implode('', $result); // Convert the $result array into a string
        return hash_hmac('sha256', $SortedArray, $key);
    }

    /**
     * Generate the HTML to render in Mobile Application to send Request:
     *
     */
    public function renderPage($data): string
    {
        $jazzcashForm[] = '<div id="header"><form id="jc-params" action="' . $this->apiUrl . '" method="post" id="jazzcash-checkout">';

        foreach ($data as $key => $value) {
            $jazzcashForm[] = '<input type="text" name="' . ($key) . '" id="' . ($key) . '" value="' . ($value) . '" />';
        }
        $jazzcashForm[] = '<input type="submit" class="button paydast-submit" name="" value="Submit" />';
        $jazzcashForm[] = '<script> window.addEventListener("load", function() {    document.getElementById("myForm").submit();  });</script></form></div>';

        return implode('', $jazzcashForm);
    }

    /**
     * Set Amount for Orders
     *
     */
    public function setAmount($amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get the amount for Order
     * @return int;
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set Bill Reference for Jazz Cash Order
     *
     */
    public function setBillReference($billref): static
    {
        $this->billreference = $billref;
        return $this;
    }

    /**
     * Get the Bill Reference Number for Jazz Cash Order
     *
     */
    public function getBillRefernce()
    {
        return $this->billreference;
    }

    /**
     * Set Product Description for Jazz Cash Order
     *
     */
    public function setProductDescription($description): static
    {

        $this->productdescription = $description;
        return $this;
    }

    /**
     * Get the Product Description for Jazz Cash Order
     *
     */
    public function getProductDescription()
    {
        return $this->billreference;
    }

    /**
     * Set the value of apiUrl
     *
     * @param $apiUrl
     * @return  self
     */
    public function setApiUrl($apiUrl): static
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getApiUrl(): mixed
    {
        return $this->apiUrl;
    }

    /**
     * Set the value for Refund API Url
     *
     * @param $apiUrl
     * @return  self
     */
    public function setRefundApiUrl($apiUrl): static
    {
        $this->refundURL = $apiUrl;
        return $this;
    }

    /**
     * Get the Refund API Url
     * @return mixed
     */
    public function getRefundApiUrl(): mixed
    {
        return $this->refundURL;
    }

    /**
     * Set the value Mobile Pin
     *
     */
    public function SetMpin($mpin)
    {
        $this->mpin = $mpin;
        return $this;
    }

    /**
     * Get the MPIN
     * @return mixed
     */
    public function GetMpin(): mixed
    {
        return $this->mpin;
    }
}
