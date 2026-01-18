<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();
require_once __DIR__ . '/includes/layout.php';
piths_head('PITHS');
piths_navbar();
?>
<div class="container py-5">
  <div class="piths-glass p-4 p-md-5 text-center">
    <h1 class="display-4 brand mb-3">PITHS</h1>
    <img src="assets/piths.png" alt="Mascote PITHS" class="img-fluid" style="max-width: 420px;">
    <div class="mt-4">
      <a href="login.php" class="btn btn-warning btn-lg btn-fun px-5">Entrar</a>
    </div>
  </div>
</div>
<?php piths_footer(); ?>
