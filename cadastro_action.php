<?php
require_once __DIR__ . '/includes/db.php';

$matricula = isset($_POST['matricula']) ? (int)$_POST['matricula'] : 0;
$nome = trim($_POST['nome'] ?? '');
$tipo = $_POST['tipo'] ?? 'ALUNO';
$id_escola = isset($_POST['id_escola']) ? (int)$_POST['id_escola'] : 0;
$id_turma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;
$birth_date = $_POST['birth_date'] ?? '';
$avatar = $_POST['avatar'] ?? '/assets/avatarAzul.png';
$senha = $_POST['senha'] ?? '';

if ($matricula <= 0 || $nome === '' || !in_array($tipo, ['ALUNO','PROF'], true) || $id_escola <= 0 || $id_turma <= 0 || $birth_date === '' || $senha === '') {
  header('Location: /cadastro.php?err=Preencha+tudo+corretamente');
  exit;
}

$stmt = $mysqli->prepare('SELECT MATRICULA FROM USERS WHERE MATRICULA = ? LIMIT 1');
$stmt->bind_param('i', $matricula);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
  header('Location: /cadastro.php?err=Esta+matr%C3%ADcula+j%C3%A1+est%C3%A1+cadastrada');
  exit;
}

$hash = hash('sha256', $senha);
$active = 0;

$ins = $mysqli->prepare('INSERT INTO USERS (MATRICULA, TIPO, NOME, PASSWORD_HASH, ACTIVE, AVATAR_URL, BIRTH_DATE, ID_ESCOLA, ID_TURMA) VALUES (?,?,?,?,?,?,?,?,?)');
$ins->bind_param('isssissii', $matricula, $tipo, $nome, $hash, $active, $avatar, $birth_date, $id_escola, $id_turma);
if (!$ins->execute()) {
  header('Location: /cadastro.php?err=Erro+ao+cadastrar:+'.urlencode($ins->error));
  exit;
}


$g = $mysqli->prepare('INSERT INTO GAMIFICACAO (MATRICULA, TOTAL_XP, LEVEL, TITLE) VALUES (?,0,1,\'INICIANTE\')');
$g->bind_param('i', $matricula);
$g->execute();

header('Location: login.php');
