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
    protected string $hash_key;
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
        $this->hash_key = config('jazzcash.hash_key');
    }

    /**
     * Send Request
     * @return Response|Application|ResponseFactory
     */
    public function sendRequest(): Response|Application|ResponseFactory
    {
        $data['pp_Version'] = '2.0';
        $data['pp_TxnType'] = '';
        $data['pp_Language'] = "EN";
        $data['pp_MerchantID'] = $this->merchant_id;
        $data['pp_SubMerchantID'] = '';
        $data['pp_Password'] = $this->password;
        $data['pp_TxnRefNo'] = "TR" . date('YmdHis') . mt_rand(10, 100); // You can customize it (only Max 20 Alpha-Numeric characters)
        $data['pp_Amount'] = $this->getAmount() * 100;  //Last two digits will be considered as Decimal
        $data['pp_TxnCurrency'] = 'PKR';
        $data['pp_TxnDateTime'] = date('YmdHis');
        $data['pp_BillReference'] = $this->getBillRefernce();
        $data['pp_Description'] = trim($this->getProductDescription(),"'");
        $data['pp_IsRegisteredCustomer'] = "No";
        $data['pp_BankID'] = '';
        $data['pp_ProductID'] = '';
        $data['pp_TxnExpiryDateTime'] = date('YmdHis', strtotime('+1 Days'));
        $data['pp_ReturnURL'] = $this->return_url;
        $data['ppmpf_1'] = '';
        $data['ppmpf_2'] = '';
        $data['ppmpf_3'] = '';
        $data['ppmpf_4'] = '';
        $data['ppmpf_5'] = '';
        $data['pp_SecureHash'] = $this->HashArray($data);
        return response($this->renderPage($data));
    }

    /**
     * Create Hash Array
     */
    public function HashArray($data): string
    {
        $HashArray = [
            $data['pp_Amount'],
            $data['pp_BankID'],
            $data['pp_BillReference'],
            $data['pp_Description'],
            $data['pp_IsRegisteredCustomer'],
            $data['pp_Language'],
            $data['pp_MerchantID'],
            $data['pp_Password'],
            $data['pp_ProductID'],
            $data['pp_ReturnURL'],
            $data['pp_TxnCurrency'],
            $data['pp_TxnDateTime'],
            $data['pp_TxnExpiryDateTime'],
            $data['pp_TxnRefNo'],
            $data['pp_TxnType'], $data['pp_Version'],
            $data['ppmpf_1'], $data["ppmpf_2"], $data['ppmpf_3'], $data['ppmpf_4'], $data['ppmpf_5']];
        $SortedArray = $this->hash_key;
        for ($i = 0; $i < count($HashArray); $i++) {
            if ($HashArray[$i] != 'undefined' and $HashArray[$i] != null and $HashArray[$i] != "") {
                $SortedArray .= "&" . $HashArray[$i];
            }
        }

        return hash_hmac('sha256', $SortedArray, $this->hash_key);
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
        return $this->productdescription;
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
