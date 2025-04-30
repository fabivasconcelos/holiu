
<?php
require_once __DIR__ . '/../Models/Produto.php';
require_once __DIR__ . '/../Helpers/mercado_pago.php';

class PaymentController {
    public function validateSlug($slug) {
        header('Content-Type: application/json');
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetro slug obrigatório.']);
            return;
        }

        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorSlug($slug);
        if (!$produto) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado.']);
            return;
        }

        echo json_encode(['message' => 'Produto válido.']);
    }

    public function create($slug) {
        header('Content-Type: application/json');
        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorSlug($slug);

        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $nome = trim($data['nome'] ?? '');
        $sobrenome = trim($data['sobrenome'] ?? '');
        $email = trim($data['email'] ?? '');

        if (!$produto) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado.']);
            return;
        }

        if (!$nome || !$sobrenome || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos ou incompletos.']);
            return;
        }

        if ($produtoModel->jaComprou($email, $slug)) {
            http_response_code(409);
            echo json_encode(['error' => 'Você já comprou este curso.']);
            return;
        }

        $link = gerarLinkPagamento($produto['nome'], $produto['preco'], $email, $produto['podia_id']);

        if (!$link) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao gerar link de pagamento.']);
            return;
        }

        echo json_encode(['checkout_url' => $link]);
    }

    public function confirm($slug, $email) {
        header('Content-Type: application/json');
        $produtoModel = new Produto();
        if ($produtoModel->jaComprou($email, $slug)) {
            echo json_encode(['status' => 'approved']);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
    }
}
