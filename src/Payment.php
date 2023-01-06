<?php

namespace Zfhassaan\Jazzcash;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Payment 
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
        $this->mpin = config('jazzcash.mpin');
        $this->hash_key = config('jazzcash.hash_key');
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
            $data['ppmpf_1'],
            $data["ppmpf_2"],
            $data['ppmpf_3'],
            $data['ppmpf_4'],
            $data['ppmpf_5']
        ];

        $SortedArray = $this->hash_key;
        for ($i = 0; $i < count($HashArray); $i++) {
            if ($HashArray[$i] != 'undefined' and $HashArray[$i] != null and $HashArray[$i] != "") {
                $SortedArray .= "&" . $HashArray[$i];
            }
        }
        return hash_hmac('sha256', $SortedArray, $this->hash_key);
    }

    /**
     * Set Amount for Orders
     *
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get the amount for Order
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set Bill Reference for Jazz Cash Order
     *
     */
    public function setBillReference($billref)
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
}
