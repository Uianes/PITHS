<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

function require_login(): void {
  if (empty($_SESSION['matricula'])) {
    header('Location: ../login.php');
    exit;
  }
}

function require_role(string $role): void {
  require_login();
  if (($_SESSION['tipo'] ?? '') !== $role) {
    http_response_code(403);
    echo 'Acesso negado.';
    exit;
  }
}

function redirect_by_role(): void {
  $tipo = $_SESSION['tipo'] ?? '';
  if ($tipo === 'ADM') {
    header('Location: admin/index.php');
    exit;
  }
  if ($tipo === 'PROF') {
    header('Location: professor/index.php');
    exit;
  }
  if ($tipo === 'ALUNO') {
    header('Location: aluno/index.php');
    exit;
  }
}

function redirect_if_logged_in(): void {
  if (!empty($_SESSION['matricula'])) {
    redirect_by_role();
  }
}
