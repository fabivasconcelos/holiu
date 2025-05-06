<?php
require_once __DIR__ . '/../../src/Models/Product.php';
require_once __DIR__ . '/../../src/Models/Payment.php';
require_once __DIR__ . '/../../src/Services/MercadoPagoService.php';

$config = require __DIR__ . '/../../config.php';
$mercadoPago = new MercadoPagoService($config);

// ✅ Lê e decodifica o JSON POST do Mercado Pago
$input = json_decode(file_get_contents('php://input'), true);

// ✅ Pega o ID do pagamento do payload
$payment_id = $input['data']['id'] ?? null;

if (!$payment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'payment_id ausente.']);
    exit;
}

// ✅ Consulta os dados reais do pagamento via API
$data = $mercadoPago->findPaymentById($payment_id);

// $data = [
//     'id' => $payment_id,
//     'status' => 'approved',
//     'external_reference' => 'Deborah|Martins|deborah1993martins@gmail.com|1023916'
// ];

if (!$data || !isset($data['external_reference'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Pagamento não encontrado ou referência ausente.']);
    exit;
}

// ✅ Extrai dados do external_reference
list($name, $lastName, $email, $product_id) = explode('|', $data['external_reference'] ?? '|');

$productModel = new Product();

// Busca o product daquele código
$product = $productModel->findBuyPodiaId($product_id);

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Produto não encontrado.']);
    return;
}

$paymentModel = new Payment();

// ✅ Verifica se já existe compra registrada por esse pagamento_id
if ($paymentModel->exists($payment_id)) {
    $paymentModel->updateSatus($data['status'], $payment_id);
} else {
    $paymentModel->create($name, $lastName, $email, $product["slug"], $data['status'], $payment_id);
}

// ✅ Se aprovado, notifica o Zapier
if ($data['status'] === 'approved') {
    file_get_contents($config['zapier_webhook'] . "?email=" . urlencode($email). "&name=" . urlencode($name). "&last_name=" . urlencode($lastName) . "&product_id=" . urlencode($product_id) . "&payment_id=" . urlencode($payment_id));
}

http_response_code(200);
echo json_encode(['success' => true]);
