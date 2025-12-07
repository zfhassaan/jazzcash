<?php

declare(strict_types=1);

namespace zfhassaan\JazzCash;

use zfhassaan\JazzCash\Constants\JazzCashConstants;
use zfhassaan\jazzcash\Payment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use InvalidArgumentException;

/**
 * JazzCash Payment Gateway Main Class
 *
 * Handles hosted checkout payment processing for JazzCash.
 */
class JazzCash extends Payment
{
    /**
     * Send payment request to JazzCash
     *
     * @return Response|Application|ResponseFactory
     * @throws InvalidArgumentException If payment data is invalid
     */
    public function sendRequest(): Response|Application|ResponseFactory
    {
        // Validate payment data before proceeding
        $this->validatePaymentData();

        $data = $this->buildPaymentData();
        $data['pp_SecureHash'] = $this->HashArray($data);

        return response($this->renderPage($data));
    }

    /**
     * Build payment data array for JazzCash API
     *
     * @return array<string, mixed> Payment data array
     */
    protected function buildPaymentData(): array
    {
        $data = [];
        $data['pp_Version'] = JazzCashConstants::VERSION;
        $data['pp_TxnType'] = '';
        $data['pp_Language'] = JazzCashConstants::LANGUAGE;
        $data['pp_MerchantID'] = $this->merchant_id;
        $data['pp_SubMerchantID'] = '';
        $data['pp_Password'] = $this->password;
        $data['pp_TxnRefNo'] = $this->generateTransactionReference();
        $data['pp_Amount'] = (int)($this->getAmount() * 100); // Last two digits will be considered as Decimal
        $data['pp_TxnCurrency'] = JazzCashConstants::CURRENCY;
        $data['pp_TxnDateTime'] = $this->getTransactionDateTime();
        $data['pp_BillReference'] = $this->getBillReference();
        $data['pp_Description'] = trim($this->getProductDescription(), "'");
        $data['pp_IsRegisteredCustomer'] = JazzCashConstants::IS_REGISTERED_CUSTOMER;
        $data['pp_BankID'] = '';
        $data['pp_ProductID'] = '';
        $data['pp_TxnExpiryDateTime'] = $this->getTransactionExpiryDateTime();
        $data['pp_ReturnURL'] = $this->return_url;
        $data['ppmpf_1'] = '';
        $data['ppmpf_2'] = '';
        $data['ppmpf_3'] = '';
        $data['ppmpf_4'] = '';
        $data['ppmpf_5'] = '';

        return $data;
    }

    /**
     * Generate unique transaction reference
     *
     * @return string Transaction reference (max 20 alphanumeric characters)
     */
    protected function generateTransactionReference(): string
    {
        return "TR" . date('YmdHis') . mt_rand(10, 100);
    }

    /**
     * Get transaction date time in JazzCash format
     *
     * @return string Transaction date time (YmdHis format)
     */
    protected function getTransactionDateTime(): string
    {
        return date('YmdHis');
    }

    /**
     * Get transaction expiry date time
     *
     * @return string Transaction expiry date time (YmdHis format)
     */
    protected function getTransactionExpiryDateTime(): string
    {
        return date('YmdHis', strtotime('+' . JazzCashConstants::DEFAULT_EXPIRY_DAYS . ' Days'));
    }

    /**
     * Generate the HTML form to render for payment request
     *
     * @param array<string, mixed> $data Payment data array
     * @return string HTML form string
     */
    public function renderPage(array $data): string
    {
        $jazzcashForm = [];
        $apiUrlEscaped = htmlspecialchars($this->apiUrl, ENT_QUOTES, 'UTF-8');
        $jazzcashForm[] = '<div id="header"><form id="jc-params" action="' . $apiUrlEscaped . '" method="post">';

        foreach ($data as $key => $value) {
            $keyEscaped = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            $valueEscaped = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $jazzcashForm[] = '<input type="hidden" name="' . $keyEscaped . '" id="' . $keyEscaped . '" value="' . $valueEscaped . '" />';
        }

        $jazzcashForm[] = '<input style="display:none;" type="submit" class="button paydast-submit" name="" value="Submit" />';
        $jazzcashForm[] = '<script> window.addEventListener("DOMContentLoaded", function() {    document.getElementById("jc-params").submit();  });</script></form></div>';

        return implode('', $jazzcashForm);
    }
}
