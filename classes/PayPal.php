<?php
/**
 * PayPal payment processing class
 */
class PayPal
{
    private $clientId;
    private $secret;
    private $baseUrl;

    public function __construct()
    {
        $this->clientId = PAYPAL_CLIENT_ID;
        $this->secret = PAYPAL_SECRET;
        $this->baseUrl = PAYPAL_MODE === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get access token from PayPal
     */
    private function getAccessToken()
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_USERPWD => $this->clientId . ':' . $this->secret,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('PayPal Auth Error: ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Create a PayPal order for deposit payment
     */
    public function createOrder($orderId, $amount, $description)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'ORDER_' . $orderId,
                    'description' => $description,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => SITE_NAME,
                'landing_page' => 'NO_PREFERENCE',
                'user_action' => 'PAY_NOW',
                'return_url' => SITE_URL . '/payment-success.php?order_id=' . $orderId,
                'cancel_url' => SITE_URL . '/my-orders.php'
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($orderData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['id'])) {
            return [
                'success' => true,
                'paypal_order_id' => $data['id'],
                'approval_url' => $this->getApprovalUrl($data['links'])
            ];
        }

        error_log('PayPal Create Order Error: ' . $response);
        return ['success' => false, 'error' => 'Failed to create PayPal order'];
    }

    /**
     * Capture a PayPal order (complete the payment)
     */
    public function captureOrder($paypalOrderId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders/' . $paypalOrderId . '/capture',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '{}',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 201 && isset($data['status']) && $data['status'] === 'COMPLETED') {
            $capture = $data['purchase_units'][0]['payments']['captures'][0] ?? null;
            return [
                'success' => true,
                'transaction_id' => $capture['id'] ?? null,
                'amount' => $capture['amount']['value'] ?? null,
                'status' => $data['status']
            ];
        }

        error_log('PayPal Capture Error: ' . $response);
        return ['success' => false, 'error' => 'Failed to capture payment'];
    }

    /**
     * Get order details from PayPal
     */
    public function getOrder($paypalOrderId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/v2/checkout/orders/' . $paypalOrderId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Extract approval URL from links array
     */
    private function getApprovalUrl($links)
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }

    /**
     * Calculate deposit amount
     */
    public static function calculateDeposit($totalPrice)
    {
        return round($totalPrice * (DEPOSIT_PERCENTAGE / 100), 2);
    }
}
