<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();
require_once __DIR__ . '/includes/layout.php';
piths_head('Login - PITHS');
piths_navbar();
$err = $_GET['err'] ?? '';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="piths-glass p-4">
        <div class="text-center mb-3">
          <img src="assets/piths.png" alt="Mascote PITHS" class="img-fluid" style="max-width: 220px;">
          <h2 class="mt-3 brand">Login</h2>
        </div>

        <?php if ($err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="login_action.php" autocomplete="off">
          <div class="mb-3">
            <label class="form-label" for="matricula">Matrícula</label>
            <input class="form-control form-control-lg" type="number" id="matricula" name="matricula" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="senha">Senha</label>
            <input class="form-control form-control-lg" type="password" id="senha" name="senha" required>
          </div>
          <button class="btn btn-primary btn-lg btn-fun w-100" type="submit">Entrar</button>
        </form>

        <div class="text-center mt-3">
          <a href="cadastro.php" class="btn btn-link">Ainda não tenho cadastro</a>
        </div>

      </div>
    </div>
  </div>
</div>
<?php piths_footer(); ?>
