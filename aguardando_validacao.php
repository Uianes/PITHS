<?php
require_once __DIR__ . '/includes/layout.php';
piths_head('Aguardando validação - PITHS');
piths_navbar();
?>
<div class="container py-5">
  <div class="piths-glass p-4 text-center">
    <h2 class="brand mb-3">Usuário aguardando validação</h2>
    <p class="lead mb-4">Seu cadastro foi enviado. Assim que um administrador validar, você poderá entrar no PITHS 😊</p>
    <a class="btn btn-warning btn-fun" href="index.php">Voltar ao início</a>
  </div>
</div>
<?php piths_footer(); ?>
