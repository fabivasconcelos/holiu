<?php
require_once __DIR__ . '/../src/Models/Produto.php';
require_once __DIR__ . '/../src/Services/MercadoPagoService.php';
require_once __DIR__ . '/../config.php';

$config = require __DIR__ . '/../config.php';
$mercadoPago = new MercadoPagoService($config);

$input = json_decode(file_get_contents('php://input'), true);
$topic = $_GET['topic'] ?? '';
$id = $_GET['id'] ?? '';

if ($topic === 'payment' && $id) {
    $data = $mercadoPago->consultarPagamento($id);

    if ($data && $data['status'] === 'approved') {
        list($email, $product_id) = explode('|', $data['external_reference']);

        $produtoModel = new Produto();
        $produtoModel->registrarCompra($email, $product_id);

        file_get_contents($config['zapier_webhook'] . "?email=" . urlencode($email) . "&product_id=" . urlencode($product_id));
    }
}
