<?php
function generatePaymentLink($product_id, $product_name, $product_price, $buyer_name, $buyer_last_name, $buyer_email)
{
    $config = require __DIR__ . '/../../config.php';

    $notificationUrl = $config['notification_url'];
    $preferenceUrl = $config['mercado_pago_preference_url'];

    $data = [
        'items' => [[
            'title' => $product_name,
            'quantity' => 1,
            'unit_price' => (float)$product_price,
            'currency_id' => 'BRL'
        ]],
        'payer' => ['email' => $buyer_email],
        "auto_return" => "approved",
        "back_urls" => [
            "success" => $notificationUrl,
            "pending" => $notificationUrl,
            "failure" => $notificationUrl
        ],
        'notification_url' => $notificationUrl,
        'external_reference' => "$buyer_name|$buyer_last_name|$buyer_email|$product_id"
    ];

    $ch = curl_init($preferenceUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $config['mercado_pago_token']
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);

    return json_decode($response, true)['init_point'] ?? null;
}
