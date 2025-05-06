<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Controllers/PaymentController.php';

$controller = new PaymentController();

if (isset($_GET['validate-slug'])) {
  $controller->validateSlug($_GET['slug'] ?? '');
  exit;
}

if (isset($_GET['validate-buyer'])) {
  $controller->validateBuyer($_GET['slug'] ?? '', $_GET['email'] ?? '');
  exit;
}

if (isset($_GET['validate-payment'])) {
  $controller->validatePayment($_GET['slug'] ?? '', $_GET['email'] ?? '');
  exit;
}

if (isset($_GET['create'])) {
  $controller->create($_GET['slug'] ?? '');
  exit;
}

if (isset($_GET['update'])) {
  $controller->update();
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Holi U - Pagamento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <image src="/logo_inline.png" />
        </image>
      </div>
      <nav class="menu">
        <a href="#">Mentoria</a>
        <a href="#">Quem somos</a>
        <a href="#">Workshops</a>
        <a href="#">Conteúdo exclusivo</a>
        <a href="#">Minhas aulas</a>
        <a href="#">Perfil</a>
        <a href="#">Comece agora</a>
      </nav>
    </div>
  </header>
  <div class="container" id="content">
    <div id="mensagem" class="msg"></div>
    <div id="loader" class="loading"></div>

    <form id="email-form" class="hidden form">
      <h2 class="form-title">Antes de continuar</h2>
      <p class="form-subtitle">Informe seu e-mail para verificarmos sua inscrição</p>
      <input type="email" name="email" placeholder="Digite seu e-mail" required>
      <button type="submit" class="btn">Prosseguir</button>
    </form>

    <form id="payment-form" class="hidden form">
      <h2 class="form-title">Complete seus dados</h2>
      <p class="form-subtitle">Preencha para gerar seu link de pagamento</p>
      <input type="text" name="name" placeholder="Nome" required>
      <input type="text" name="last_name" placeholder="Sobrenome" required>
      <button type="submit" class="btn">Pagar agora</button>
    </form>

    <div id="iframe-container" class="iframe-container hidden"></div>

    <div id="pagamento-confirmado" class="hidden success-box"></div>
  </div>
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-logo">
        <img src="/logo_inline.png" alt="Holi U" class="logo-img">
      </div>

      <div class="footer-menu">
        <span>Mentoria Exclusiva</span>
        <span>Cursos e Workshops</span>
        <span>Mentores na sua casa</span>
        <span>Conteúdo Digital</span>
        <span>Sobre nós</span>
      </div>

      <div class="footer-menu">
        <span>Fernanda Capobianco</span>
        <span>Mentores</span>
        <span>8 Pilares</span>
        <span>Minhas aulas</span>
        <span>Meu Perfil</span>
      </div>

      <div class="footer-social">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-tiktok"></i></a>
      </div>

      <div class="footer-links">
        <a href="#">Termos de serviço</a>
        <a href="#">Política de Privacidade</a>
      </div>

    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script>
    const slug = new URLSearchParams(window.location.search).get('slug');
    const loader = document.getElementById('loader');
    const msg = document.getElementById('mensagem');
    const emailForm = document.getElementById('email-form');
    const paymentForm = document.getElementById('payment-form');
    const iframeContainer = document.getElementById('iframe-container');
    const confirmacao = document.getElementById('pagamento-confirmado');

    if (!slug) {
      loader.classList.add('hidden');
      msg.innerHTML = "<p style='color:red'>Nenhum produto foi informado para pagamento.</p>";
    } else {
      axios.get(`?validate-slug=1&slug=${slug}`).then(() => {
        loader.classList.add('hidden');
        msg.classList.add('hidden');
        emailForm.classList.remove('hidden');
      }).catch(err => {
        loader.classList.add('hidden');
        msg.innerHTML = `<p style='color:red'>Nenhum produto encontrado para o slug ${slug}</p>`;
      });

      emailForm.addEventListener('submit', async e => {
        e.preventDefault();
        const email = emailForm.email.value.trim();
        loader.classList.remove('hidden');
        msg.classList.add('hidden');
        msg.innerHTML = '';
        try {
          const res = await axios.get(`?validate-buyer=1&slug=${slug}&email=${email}`);
          loader.classList.add('hidden');
          if (res.data.success) {
            msg.innerHTML = `<p>Você já adquiriu este produto.</p><a href="${res.data.redirect_url}" class="btn">Ir para a área de membros</a>`;
            msg.classList.remove('hidden');
          } else {
            emailForm.classList.add('hidden');
            paymentForm.classList.remove('hidden');
          }
        } catch {
          loader.classList.add('hidden');
          msg.innerHTML = "<p style='color:red'>Erro ao validar o comprador.</p>";
        }
      });

      paymentForm.addEventListener('submit', async e => {
        e.preventDefault();
        const name = paymentForm.name.value.trim();
        const last_name = paymentForm.last_name.value.trim();
        const email = emailForm.email.value.trim();
        loader.classList.remove('hidden');
        msg.innerHTML = '';
        try {
          const res = await axios.post(`?create=1&slug=${slug}`, {
            name,
            last_name,
            email
          });
          loader.classList.add('hidden');
          paymentForm.classList.add('hidden');
          iframeContainer.innerHTML = `<iframe src="${res.data.checkout_url}"></iframe>`;
          iframeContainer.classList.remove('hidden');
          const interval = setInterval(async () => {
            const res = await axios.get(`?validate-payment=1&slug=${slug}&email=${email}`);
            if (res.data.success) {
              iframeContainer.classList.add('hidden');
              confirmacao.classList.remove('hidden');
              confirmacao.innerHTML = `<p>Seu pagamento foi realizado!<br/> Você receberá instruções para acesso no e-mail ${email}</p><a href="${res.data.redirect_url}" class="btn">Ir para área de membros</a>`;
              clearInterval(interval);
            }
          }, 1000);
        } catch {
          loader.classList.add('hidden');
          msg.innerHTML = "<p style='color:red'>Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente em alguns minutos.</p>";
        }
      });
    }
  </script>
</body>

</html>