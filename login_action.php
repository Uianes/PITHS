<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
$senha = $_POST['senha'] ?? '';
$passwordHash = hash('sha256', $senha);
 
if ($matricula <= 0 || $senha === '') {
  header('Location: login.php?err=Informe+matr%C3%ADcula+e+senha');
  exit;
}

$stmt = $mysqli->prepare('SELECT u.MATRICULA, u.TIPO, u.NOME, u.PASSWORD_HASH, u.ACTIVE, u.ID_TURMA, u.ID_ESCOLA,
  t.NOME_TURMA, e.NOME_ESCOLA
  FROM USERS u
  LEFT JOIN TURMA t ON t.ID_TURMA = u.ID_TURMA
  LEFT JOIN ESCOLA e ON e.ID_ESCOLA = u.ID_ESCOLA
  WHERE u.MATRICULA = ? LIMIT 1');
$stmt->bind_param('i', $matricula);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
  header('Location: login.php?err=Matr%C3%ADcula+ou+senha+inv%C3%A1lida');
  exit;
}

$user = $res->fetch_assoc();

if (!hash_equals($user['PASSWORD_HASH'], $passwordHash)) {
  header('Location: login.php?err=Matr%C3%ADcula+ou+senha+inv%C3%A1lida');
  exit;
}

if ((int)$user['ACTIVE'] !== 1) {
  $_SESSION = [];
  header('Location: aguardando_validacao.php');
  exit;
}

$_SESSION['matricula'] = (int)$user['MATRICULA'];
$_SESSION['tipo'] = $user['TIPO'];
$_SESSION['nome'] = $user['NOME'];
$_SESSION['id_turma'] = (int)$user['ID_TURMA'];
$_SESSION['id_escola'] = (int)$user['ID_ESCOLA'];
$_SESSION['turma_nome'] = $user['NOME_TURMA'] ?? '';
$_SESSION['escola_nome'] = $user['NOME_ESCOLA'] ?? '';

if ($user['TIPO'] === 'ADM') { header('Location: admin/index.php'); exit; }
if ($user['TIPO'] === 'PROF') { header('Location: professor/index.php'); exit; }
header('Location: aluno/index.php');
