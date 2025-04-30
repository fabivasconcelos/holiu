
<?php
require_once __DIR__ . '/../Models/Produto.php';
require_once __DIR__ . '/../Helpers/mercado_pago.php';

class PaymentController {
    public function validate($slug, $email) {
        header('Content-Type: application/json');

        if (!$slug || !$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros obrigatórios ausentes.']);
            return;
        }

        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorSlug($slug);

        if (!$produto) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado.']);
            return;
        }

        if ($produtoModel->jaComprou($email, $slug)) {
            echo json_encode([
                'status' => 'already_paid',
                'message' => 'Você já comprou este curso.',
                'redirect_url' => 'https://area.membros.com'
            ]);
            return;
        }

        echo json_encode(['status' => 'ready']);
    }

    public function create($slug) {
        header('Content-Type: application/json');
        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorSlug($slug);

        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $nome = $data['nome'] ?? '';
        $sobrenome = $data['sobrenome'] ?? '';
        $email = $data['email'] ?? '';

        if (!$produto) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado.']);
            return;
        }

        if (!$nome || !$sobrenome || !$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Preencha todos os campos.']);
            return;
        }

        $link = gerarLinkPagamento($produto['nome'], $produto['preco'], $email, $produto['podia_id']);

        if (!$link) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao gerar link.']);
            return;
        }

        http_response_code(201);
        echo json_encode(['checkout_url' => $link]);
    }

    public function confirm($slug, $email) {
        header('Content-Type: application/json');
        $produtoModel = new Produto();
        http_response_code(200);
        if ($produtoModel->jaComprou($email, $slug)) {
            echo json_encode(['status' => 'approved']);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
    }
}
