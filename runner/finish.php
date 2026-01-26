<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$tipo = $_SESSION['tipo'] ?? '';
$matricula = (int)($_SESSION['matricula'] ?? 0);
$points = isset($_POST['points']) ? (int)$_POST['points'] : 0;
$idAtividade = isset($_POST['id_atividade']) ? (int)$_POST['id_atividade'] : 0;
$tempoSec = isset($_POST['tempo']) ? (int)$_POST['tempo'] : 0;
if ($points < 0) $points = 0;
if ($tempoSec < 0) $tempoSec = 0;

if ($tipo === 'ALUNO' && $matricula > 0) {
  $mysqli->begin_transaction();

  $alreadyComplete = false;
  if ($idAtividade > 0) {
    $chkStatus = $mysqli->prepare("SELECT STATUS FROM ATIVIDADE_STATUS WHERE ID_ATIVIDADE=? AND MATRICULA=?");
    $chkStatus->bind_param('ii', $idAtividade, $matricula);
    $chkStatus->execute();
    $rowStatus = $chkStatus->get_result()->fetch_assoc();
    if ($rowStatus && $rowStatus['STATUS'] === 'COMPLETO') {
      $alreadyComplete = true;
    }
  }

  if ($alreadyComplete) {
    $points = 5;
  }

  $check = $mysqli->prepare('SELECT TOTAL_XP, LEVEL, TITLE FROM GAMIFICACAO WHERE MATRICULA=?');
  $check->bind_param('i', $matricula);
  $check->execute();
  $res = $check->get_result();

  if ($row = $res->fetch_assoc()) {
    $total = (int)$row['TOTAL_XP'] + $points;
    $upd = $mysqli->prepare('UPDATE GAMIFICACAO SET TOTAL_XP=? WHERE MATRICULA=?');
    $upd->bind_param('ii', $total, $matricula);
    $upd->execute();
  } else {
    $ins = $mysqli->prepare('INSERT INTO GAMIFICACAO (MATRICULA, TOTAL_XP, LEVEL, TITLE) VALUES (?, ?, 1, "INICIANTE")');
    $ins->bind_param('ii', $matricula, $points);
    $ins->execute();
  }

  if ($idAtividade > 0) {
    $tempoStr = gmdate('H:i:s', $tempoSec);
    $sel = $mysqli->prepare('SELECT TENTATIVAS FROM ATIVIDADE_STATUS WHERE ID_ATIVIDADE=? AND MATRICULA=?');
    $sel->bind_param('ii', $idAtividade, $matricula);
    $sel->execute();
    $selRes = $sel->get_result();
    if ($row = $selRes->fetch_assoc()) {
      $tent = (int)($row['TENTATIVAS'] ?? 0);
      $tent += 1;
      $up = $mysqli->prepare("UPDATE ATIVIDADE_STATUS SET TEMPO=?, STATUS='COMPLETO', TENTATIVAS=? WHERE ID_ATIVIDADE=? AND MATRICULA=?");
      $up->bind_param('siii', $tempoStr, $tent, $idAtividade, $matricula);
      $up->execute();
    } else {
      $tent = 1;
      $insStatus = $mysqli->prepare("INSERT INTO ATIVIDADE_STATUS (ID_ATIVIDADE, MATRICULA, TEMPO, STATUS, TENTATIVAS) VALUES (?, ?, ?, 'COMPLETO', ?)");
      $insStatus->bind_param('iisi', $idAtividade, $matricula, $tempoStr, $tent);
      $insStatus->execute();
    }
  }

  $mysqli->commit();
}

$redirect = '/aluno/index.php';
if ($tipo === 'PROF') $redirect = '/professor/index.php';
if ($tipo === 'ADM') $redirect = '/admin/index.php';

echo json_encode(['ok' => true, 'redirect' => $redirect]);
