<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpoint;
    protected $returnUrl;
    protected $notifyUrl;
    protected $verifySsl;

    public function __construct()
    {
        $this->partnerCode = config('services.momo.partner_code');
        $this->accessKey = config('services.momo.access_key');
        $this->secretKey = config('services.momo.secret_key');
        $this->endpoint = config('services.momo.endpoint');
        $this->returnUrl = config('services.momo.return_url');
        $this->notifyUrl = config('services.momo.notify_url');
        // Convert to boolean since env strings might be 'false'
        $this->verifySsl = filter_var(config('services.momo.verify_ssl'), FILTER_VALIDATE_BOOLEAN);
    }

    public function createPayment($order, $transactionId = null)
    {
        $requestId = $transactionId ?? (time() . "_" . $order->id);
        $orderId = $order->id . "_" . time(); // MoMo requires unique orderId per request
        $amount = (string) (int) $order->total_amount;
        $orderInfo = "Thanh toan don hang #" . $order->id;
        $requestType = "payWithCC"; // Dùng thanh toán qua thẻ quốc tế (Visa, Master, JCB...)
        $extraData = "";

        // Tạo rawHash
        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $this->notifyUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $this->partnerCode .
            "&redirectUrl=" . $this->returnUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "Aurelia Store",
            'storeId' => "AureliaStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $this->returnUrl,
            'ipnUrl' => $this->notifyUrl,
            'lang' => 'vi',
            'requestType' => $requestType,
            'autoCapture' => true,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        try {
            $client = Http::asJson();
            
            if (!$this->verifySsl) {
                $client = $client->withoutVerifying();
            }

            $response = $client->post($this->endpoint, $data);
            $result = $response->json();
            
            if (isset($result['payUrl'])) {
                return [
                    'success' => true,
                    'payUrl' => $result['payUrl'],
                    'transaction_id' => $requestId
                ];
            }

            Log::error('MoMo Create Payment Error: ', $result);
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Lỗi không xác định từ MoMo'
            ];

        } catch (\Exception $e) {
            Log::error('MoMo Create Payment Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function verifySignature($data)
    {
        $accessKey = $this->accessKey;
        $amount = $data['amount'];
        $extraData = $data['extraData'];
        $message = $data['message'];
        $orderId = $data['orderId'];
        $orderInfo = $data['orderInfo'];
        $orderType = $data['orderType'];
        $partnerCode = $data['partnerCode'];
        $payType = $data['payType'];
        $requestId = $data['requestId'];
        $responseTime = $data['responseTime'];
        $resultCode = $data['resultCode'];
        $transId = $data['transId'];
        $momoSignature = $data['signature'];

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&message=" . $message .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&orderType=" . $orderType .
            "&partnerCode=" . $partnerCode .
            "&payType=" . $payType .
            "&requestId=" . $requestId .
            "&responseTime=" . $responseTime .
            "&resultCode=" . $resultCode .
            "&transId=" . $transId;

        $partnerSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return hash_equals($momoSignature, $partnerSignature);
    }
}
