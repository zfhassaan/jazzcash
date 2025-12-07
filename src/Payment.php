<?php

declare(strict_types=1);

namespace zfhassaan\jazzcash;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use RuntimeException;

/**
 * Base Payment class for JazzCash Payment Gateway
 *
 * Handles configuration, hash generation, and payment data management.
 */
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
    private float|int $amount = 0;
    private string $billreference = '';
    private string $productdescription = '';
    private string $mpin = '';
    // Refund URL
    protected string $refundURL = '';

    /**
     * Constructor for JazzCash Payment Gateway
     */
    public function __construct()
    {
        $this->initConfig();
    }


    /**
     * Initialize Config Values
     *
     * @return void
     * @throws RuntimeException If required configuration is missing
     */
    public function initConfig(): void
    {
        $this->api_mode = config('jazzcash.mode', 'sandbox');
        $this->api_mode === 'sandbox' 
            ? $this->setApiUrl(config('jazzcash.sandbox_api_url', '')) 
            : $this->setApiUrl(config('jazzcash.api_url', ''));
        $this->merchant_id = config('jazzcash.merchant_id', '');
        $this->return_url = config('jazzcash.return_url', '');
        $this->password = config('jazzcash.password', '');
        $this->timezone = config('jazzcash.timezone', 'Asia/Karachi');
        $this->mpin = config('jazzcash.mpin', '');
        $this->hash_key = config('jazzcash.hash_key', '');
        
        // Validate configuration (only if not in test mode)
        if (!app()->runningUnitTests()) {
            $this->validateConfig();
        }
    }

    /**
     * Validate that required configuration is present
     *
     * @return void
     * @throws RuntimeException If required configuration is missing
     */
    protected function validateConfig(): void
    {
        $required = [
            'merchant_id' => $this->merchant_id,
            'password' => $this->password,
            'hash_key' => $this->hash_key,
            'return_url' => $this->return_url,
        ];

        foreach ($required as $key => $value) {
            if (empty($value)) {
                throw new RuntimeException("JazzCash configuration missing: {$key}. Please check your .env file.");
            }
        }

        if (empty($this->apiUrl)) {
            throw new RuntimeException("JazzCash API URL is not configured. Please set JAZZCASH_PRODUCTION_URL or JAZZCASH_SANDBOX_URL in your .env file.");
        }
    }

    /**
     * Create Hash Array for secure hash generation
     *
     * @param array<string, mixed> $data Payment data array
     * @return string Generated hash string
     */
    public function HashArray(array $data): string
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
     * @param float|int|string $amount The transaction amount
     * @return static Returns self for method chaining
     * @throws InvalidArgumentException If amount is invalid
     */
    public function setAmount(float|int|string $amount): static
    {
        $amount = (float) $amount;
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get the amount for Order
     *
     * @return float|int The transaction amount
     */
    public function getAmount(): float|int
    {
        return $this->amount;
    }

    /**
     * Set Bill Reference for Jazz Cash Order
     *
     * @param string $billref Bill reference number
     * @return static Returns self for method chaining
     */
    public function setBillReference(string $billref): static
    {
        $this->billreference = $billref;
        return $this;
    }

    /**
     * Get the Bill Reference Number for Jazz Cash Order
     *
     * @return string Bill reference number
     * @deprecated Use getBillReference() instead. This method will be removed in a future version.
     */
    public function getBillRefernce(): string
    {
        return $this->billreference;
    }

    /**
     * Get the Bill Reference Number for Jazz Cash Order
     *
     * @return string Bill reference number
     */
    public function getBillReference(): string
    {
        return $this->getBillRefernce(); // Alias for backward compatibility
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
     * @return string Product description
     */
    public function getProductDescription(): string
    {
        return $this->productdescription;
    }

    /**
     * Set the value of apiUrl
     *
     * @param string $apiUrl API URL
     * @return static Returns self for method chaining
     */
    public function setApiUrl(string $apiUrl): static
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    /**
     * Get the API URL
     *
     * @return string API URL
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Set the value for Refund API Url
     *
     * @param string $apiUrl Refund API URL
     * @return static Returns self for method chaining
     */
    public function setRefundApiUrl(string $apiUrl): static
    {
        $this->refundURL = $apiUrl;
        return $this;
    }

    /**
     * Get the Refund API Url
     *
     * @return string Refund API URL
     */
    public function getRefundApiUrl(): string
    {
        return $this->refundURL;
    }

    /**
     * Validate payment data before sending request
     *
     * @return void
     * @throws InvalidArgumentException If required data is missing or invalid
     */
    protected function validatePaymentData(): void
    {
        if (empty($this->amount) || $this->amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than 0');
        }

        if (empty($this->billreference)) {
            throw new InvalidArgumentException('Bill reference is required');
        }

        if (empty($this->productdescription)) {
            throw new InvalidArgumentException('Product description is required');
        }
    }
}
