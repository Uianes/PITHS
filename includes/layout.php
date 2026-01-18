<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

function piths_head(string $title = 'PITHS'): void {
  $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $projectRoot = realpath(__DIR__ . '/..');
  $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
  $basePath = $projectRoot ? rtrim(str_replace($docRoot, '', $projectRoot), '/') : '';
  $assetUrl = $basePath . '/assets/fundo.png';
  echo "<!doctype html>\n<html lang=\"pt-br\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n<title>{$t}</title>\n";
  echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH\" crossorigin=\"anonymous\">\n";
  echo "<style>\n";
  echo "body{min-height:100vh;background:#000 url('{$assetUrl}') no-repeat center center fixed;background-size:cover;}\n";
  echo ".piths-glass{background:rgba(255,255,255,.86);backdrop-filter:blur(3px);border-radius:18px;box-shadow:0 10px 35px rgba(0,0,0,.25);}\n";
  echo ".btn-fun{border-radius:999px;font-weight:700;}\n";
  echo ".navbar{background:rgba(255,255,255,.92)!important;}\n";
  echo ".brand{font-weight:900;letter-spacing:.08em;}\n";
  echo ".profile-avatar{width:80px;height:80px;border-radius:50%;object-fit:cover;box-shadow:0 6px 18px rgba(0,0,0,.2);}\n";
  echo ".sidebar-meta{font-size:.9rem;color:#444;}\n";
  echo "</style>\n</head>\n<body>\n";
}

function piths_navbar(): void {
  $logged = !empty($_SESSION['matricula']);
  $tipo = $_SESSION['tipo'] ?? '';
  $nome = $_SESSION['nome'] ?? '';
  $matricula = $_SESSION['matricula'] ?? '';
  $idTurma = $_SESSION['id_turma'] ?? '';
  $idEscola = $_SESSION['id_escola'] ?? '';
  $turmaNome = $_SESSION['turma_nome'] ?? '';
  $escolaNome = $_SESSION['escola_nome'] ?? '';
  $projectRoot = realpath(__DIR__ . '/..');
  $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
  $basePath = $projectRoot ? rtrim(str_replace($docRoot, '', $projectRoot), '/') : '';
  $defaultAvatar = $basePath . '/assets/avatarAzul.png';
  $avatarUrl = $_SESSION['avatar_url'] ?? $defaultAvatar;
  $nomeEsc = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
  $avatarEsc = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');

  echo "<nav class=\"navbar navbar-expand-lg\">\n<div class=\"container\">\n<a class=\"navbar-brand brand\" href=\"index.php\">PITHS</a>\n<button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#nav\" aria-controls=\"nav\" aria-expanded=\"false\" aria-label=\"Menu\"><span class=\"navbar-toggler-icon\"></span></button>\n<div class=\"collapse navbar-collapse\" id=\"nav\">\n<ul class=\"navbar-nav me-auto mb-2 mb-lg-0\">";

  if ($logged) {
    if ($tipo === 'ADM') echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"../admin/index.php\">Painel Admin</a></li>";
    if ($tipo === 'PROF') echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"../professor/index.php\">Painel Professor</a></li>";
    if ($tipo === 'ALUNO') echo "<li class=\"nav-item\"><a class=\"nav-link\" href=\"../aluno/index.php\">Minhas atividades</a></li>";
  }

  echo "</ul>\n<div class=\"d-flex align-items-center gap-2\">";

  if ($logged) {
    echo "<span class=\"small\">👋 Olá, <strong>{$nomeEsc}</strong></span>";
    echo "<button class=\"btn btn-outline-dark btn-sm btn-fun\" type=\"button\" data-bs-toggle=\"offcanvas\" data-bs-target=\"#profileSidebar\" aria-controls=\"profileSidebar\" aria-label=\"Abrir perfil\">☰</button>";
  } else {
    echo "<a class=\"btn btn-warning btn-sm btn-fun\" href=\"login.php\">Login</a>";
    echo "<a class=\"btn btn-primary btn-sm btn-fun\" href=\"cadastro.php\">Cadastro</a>";
  }

  echo "</div>\n</div>\n</div>\n</nav>\n";

  if ($logged) {
    echo "<div class=\"offcanvas offcanvas-end\" tabindex=\"-1\" id=\"profileSidebar\" aria-labelledby=\"profileSidebarLabel\">\n";
    echo "<div class=\"offcanvas-header\">\n<h5 class=\"offcanvas-title\" id=\"profileSidebarLabel\">Perfil</h5>\n";
    echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"offcanvas\" aria-label=\"Fechar\"></button>\n</div>\n";
    echo "<div class=\"offcanvas-body\">\n<div class=\"text-center mb-3\">\n<img class=\"profile-avatar\" src=\"{$avatarEsc}\" alt=\"Avatar\">\n";
    echo "<div class=\"mt-2 fw-bold\">{$nomeEsc}</div>\n<div class=\"sidebar-meta\">{$tipo}</div>\n</div>\n";
    echo "<div class=\"sidebar-meta\">\n<div>Matrícula: <strong>" . htmlspecialchars((string)$matricula, ENT_QUOTES, 'UTF-8') . "</strong></div>\n";
    echo "<div>Escola: <strong>" . htmlspecialchars($escolaNome ?: ("#" . (string)$idEscola), ENT_QUOTES, 'UTF-8') . "</strong></div>\n";
    echo "<div>Turma: <strong>" . htmlspecialchars($turmaNome ?: ("#" . (string)$idTurma), ENT_QUOTES, 'UTF-8') . "</strong></div>\n</div>\n";
    echo "<div class=\"mt-3\">\n<a class=\"btn btn-outline-dark btn-sm btn-fun w-100\" href=\"../logout.php\">Sair</a>\n</div>\n";
    echo "</div>\n</div>\n";
  }
}

function piths_footer(): void {
  echo "<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\" integrity=\"sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz\" crossorigin=\"anonymous\"></script>\n</body>\n</html>";
}
