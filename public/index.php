<?php
require_once __DIR__ . '/../src/Controllers/PaymentController.php';

if (isset($_GET['validate-slug'])) {
    $controller = new PaymentController();
    $controller->validateSlug($_GET['slug'] ?? '');
    exit;
}

if (isset($_GET['create'])) {
    $controller = new PaymentController();
    $controller->create($_GET['slug'] ?? '');
    exit;
}

if (isset($_GET['confirm'])) {
    $controller = new PaymentController();
    $controller->confirm($_GET['slug'] ?? '', $_GET['email'] ?? '');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Holi U - Pagamento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { margin: 0; font-family: 'Inter', sans-serif; }
    header, footer { padding: 20px; color: white; text-align: center; }
    header { background-color: #0A4C3E; }
    header nav span { margin: 0 10px; text-transform: uppercase; font-weight: 500; }
    footer { background-color: #4DB6AC; }
    footer input, footer button { padding: 8px; margin: 5px; border: none; border-radius: 4px; }
    footer button, .btn-primary { background-color: #0A4C3E; color: white; }
    .container { padding: 40px; text-align: center; }
    input { padding: 10px; width: 300px; margin: 10px; border-radius: 4px; border: 1px solid #ccc; }
    .hidden { display: none; }
    .loading { margin: 40px auto; border: 5px solid #f3f3f3; border-radius: 50%; border-top: 5px solid #0A4C3E; width: 60px; height: 60px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    iframe { width: 100%; height: 600px; border: none; margin-top: 30px; }
    .btn-primary { padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 20px; }
  </style>
</head>
<body>

<header>
  <h1>🌿 Holi U</h1>
  <nav>
    <span>MENTORIA</span> | <span>QUEM SOMOS</span> | <span>WORKSHOPS</span> |
    <span>CONTEÚDO EXCLUSIVO</span> | <span>MINHAS AULAS</span> |
    <span>PERFIL</span> | <span>COMECE AGORA</span>
  </nav>
</header>

<div class="container" id="content">
  <div id="initial-error" class="hidden">
    <p style="color: red;">Pagamento temporariamente indisponível.</p>
  </div>
  <div id="loader" class="loading"></div>

  <form id="form" class="hidden">
    <input type="text" name="nome" placeholder="Nome" required><br>
    <input type="text" name="sobrenome" placeholder="Sobrenome" required><br>
    <input type="email" name="email" placeholder="E-mail" required><br>
    <button id="btn-pagar" class="btn-primary" type="submit">Pagar</button>
  </form>

  <div id="mensagem"></div>
  <div id="iframe-container"></div>
  <div id="confirmar-pagamento" class="hidden">
    <button id="btn-confirmar" class="btn-primary">Já fez seu pagamento?</button>
  </div>
</div>

<footer>
  <p><strong>Fale conosco</strong></p>
  <input type="text" placeholder="Email"><button>INSCREVER-SE</button><br>
  <p><a href="#">Facebook</a> | <a href="#">Instagram</a> | <a href="#">TikTok</a></p>
  <p><a href="#">Termos de serviço</a> | <a href="#">Política de Privacidade</a></p>
  <p><small>Criado com ❤️ pela Holi U</small></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
const slug = params.get('slug');
const loader = document.getElementById('loader');
const initialError = document.getElementById('initial-error');
const form = document.getElementById('form');
const msg = document.getElementById('mensagem');
const iframeContainer = document.getElementById('iframe-container');
const btnPagar = document.getElementById('btn-pagar');
const confirmarDiv = document.getElementById('confirmar-pagamento');

if (!slug) {
  loader.classList.add('hidden');
  initialError.classList.remove('hidden');
} else {
  axios.get(`?validate-slug=1&slug=${slug}`)
    .then(() => {
      loader.classList.add('hidden');
      form.classList.remove('hidden');
    })
    .catch(err => {
      loader.classList.add('hidden');
      msg.innerHTML = `<p style="color:red">${err.response?.data?.error || 'Produto inválido.'}</p>`;
    });

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const nome = form.nome.value.trim();
    const sobrenome = form.sobrenome.value.trim();
    const email = form.email.value.trim();

    if (!nome || !sobrenome || !email || !email.includes('@')) {
      msg.innerHTML = "<p style='color:red'>Preencha todos os campos com um e-mail válido.</p>";
      return;
    }

    btnPagar.innerText = 'Processando...';
    btnPagar.disabled = true;

    try {
      const res = await axios.post(`?create=1&slug=${slug}`, { nome, sobrenome, email });
      form.classList.add('hidden');
      iframeContainer.innerHTML = `<iframe src="${res.data.checkout_url}"></iframe>`;
      setTimeout(() => confirmarDiv.classList.remove('hidden'), 180000);
    } catch (err) {
      const erro = err.response?.data?.error || 'Erro ao processar.';
      if (erro.includes('já comprou')) {
        msg.innerHTML = `<p>${erro}</p><a href="https://area.membros.com" class="btn-primary">Ir para a área de membros</a>`;
        form.classList.add('hidden');
      } else {
        msg.innerHTML = `<p style="color:red">${erro}</p>`;
      }
    } finally {
      btnPagar.innerText = 'Pagar';
      btnPagar.disabled = false;
    }
  });

  document.getElementById('btn-confirmar').addEventListener('click', async function () {
    const email = form.email.value;
    msg.innerHTML = 'Verificando pagamento...';
    try {
      const res = await axios.get(`?confirm=1&slug=${slug}&email=${email}`);
      if (res.data.status === 'approved') {
        msg.innerHTML = '<p style="color:green">Seu pagamento foi processado com sucesso! Verifique seu e-mail para acessar o curso.</p>';
        confirmarDiv.classList.add('hidden');
      } else {
        msg.innerHTML = '<p style="color:red">Pagamento não identificado, por favor, realize o pagamento do PIX gerado acima.</p>';
        confirmarDiv.classList.add('hidden');
        setTimeout(() => {
          msg.innerHTML = '';
          confirmarDiv.classList.remove('hidden');
        }, 180000);
      }
    } catch (err) {
      msg.innerHTML = '<p style="color:red">Erro ao verificar pagamento.</p>';
    }
  });
}
</script>
</body>
</html>