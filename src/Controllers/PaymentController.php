<?php
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Helpers/MercadoPagoHelper.php';

class PaymentController
{
    public function validateSlug($slug)
    {
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parâmetro slug obrigatório.']);
            exit;
        }

        $productModel = new Product();
        $product = $productModel->findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Produto não encontrado.']);
            exit;
        }
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Produto válido.']);
        exit;
    }

    public function validateBuyer($slug, $email)
    {
        header('Content-Type: application/json');

        if (!$slug || !$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parâmetros obrigatórios ausentes.']);
            exit;
        }

        $productModel = new Product();
        $product = $productModel->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Produto não encontrado.']);
            return;
        }

        $paymentModel = new Payment();

        http_response_code(200);
        if ($paymentModel->existsForEmailAndSlug($email, $slug)) {
            $config = require __DIR__ . '/../../config.php';

            echo json_encode([
                'success' => true,
                'message' => 'Você já comprou este curso.',
                'redirect_url' => $config["holiu_member_area_url"]
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Compra não identificada para o comprador e produto informados.']);
        exit;
    }

    public function validatePayment($slug, $email)
    {
        header('Content-Type: application/json');

        if (!$slug || !$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parâmetros obrigatórios ausentes.']);
            exit;
        }

        $productModel = new Product();
        $product = $productModel->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Produto não encontrado.']);
            return;
        }

        $paymentModel = new Payment();

        http_response_code(200);
        if ($paymentModel->existsForEmailAndSlug($email, $slug)) {
            $payment = $paymentModel->findByEmailAndSlug($email, $slug);

            echo json_encode([
                'success' => true,
                'message' => 'Pagamento aprovado.',
                'redirect_url' => $payment["podia_register_url"]
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Compra não identificada para o comprador e produto informados.']);
        exit;
    }

    public function update()
    {
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $payment_code = trim($data['payment_code'] ?? '');
        $register_url = trim($data['register_url'] ?? '');
        
        $paymentModel = new Payment();

        $payment = $paymentModel->exists($payment_code);

        if (!$payment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pagamento não encontrado.']);
            exit;;
        }

        $paymentModel->updateRegisterURL($payment_code, $register_url);
        
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => "Pagamento atualizado!"]);
        exit;
    }

    public function create($slug)
    {
        $productModel = new Product();
        $product = $productModel->findBySlug($slug);

        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Produto não encontrado.']);
            exit;;
        }

        if (!$name || !$lastName || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Dados inválidos ou incompletos.']);
            return;
        }

        $paymentModel = new Payment();

        if ($paymentModel->existsForEmailAndSlug($email, $slug)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Você já comprou este curso.']);
            exit;;
        }

        $link = generatePaymentLink($product['podia_id'], $product['name'], $product['price'], $name, $lastName, $email);

        if (!$link) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erro ao gerar link de pagamento.']);
            exit;;
        }

        http_response_code(201);
        echo json_encode(['success' => true, 'checkout_url' => $link]);
        exit;
    }
}
