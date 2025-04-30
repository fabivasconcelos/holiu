<?php
function gerarLinkPagamento($nome, $preco, $email, $produtoId) {
    $config = require __DIR__ . '/../../config.php';

    $notificationUrl = $config['notification_url'];
    $preferenceUrl = $config['mercado_pago_preference_url'];

    $data = [
        'items' => [[
            'title' => $nome,
            'quantity' => 1,
            'unit_price' => (float)$preco,
            'currency_id' => 'BRL'
        ]],
        'payer' => ['email' => $email],
        'notification_url' => $notificationUrl,
        'external_reference' => "$email|$produtoId"
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