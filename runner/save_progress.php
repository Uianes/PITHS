<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

function out($ok, $extra=[]){
  echo json_encode(array_merge(["ok"=>$ok], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

function sec_to_time($sec){
  $sec = (int)$sec; if($sec < 0) $sec = 0;
  $h = floor($sec/3600);
  $m = floor(($sec%3600)/60);
  $s = $sec%60;
  return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

$id_atividade = (int)($_POST['id_atividade'] ?? 0);
$matricula    = (int)($_POST['matricula'] ?? 0);
$status       = (string)($_POST['status'] ?? 'INCOMPLETO');

$tent_inc     = (int)($_POST['tentativas_inc'] ?? 0);
$tempo_seg    = isset($_POST['tempo_segundos']) ? (int)$_POST['tempo_segundos'] : null;
$pontos       = (int)($_POST['pontos'] ?? 0);

if ($id_atividade <= 0 || $matricula <= 0) out(false, ["error"=>"Parâmetros inválidos."]);
if (!in_array($status, ['COMPLETO','INCOMPLETO','REFAZER'], true)) $status = 'INCOMPLETO';
if ($tent_inc < 0) $tent_inc = 0;
if ($pontos < 0) $pontos = 0;

$tempo = ($status === 'COMPLETO' && $tempo_seg !== null) ? sec_to_time($tempo_seg) : null;

// Verifica se já existe registro
$sel = $mysqli->prepare("SELECT TENTATIVAS FROM ATIVIDADE_STATUS WHERE ID_ATIVIDADE=? AND MATRICULA=? LIMIT 1");
$sel->bind_param("ii", $id_atividade, $matricula);
$sel->execute();
$res = $sel->get_result();

if ($row = $res->fetch_assoc()) {
  $tent = (int)$row['TENTATIVAS'] + $tent_inc;

  if ($status === 'COMPLETO' && $tempo !== null) {
    $u = $mysqli->prepare("UPDATE ATIVIDADE_STATUS SET STATUS=?, TEMPO=?, TENTATIVAS=? WHERE ID_ATIVIDADE=? AND MATRICULA=?");
    $u->bind_param("ssiii", $status, $tempo, $tent, $id_atividade, $matricula);
    $u->execute();
  } else {
    $u = $mysqli->prepare("UPDATE ATIVIDADE_STATUS SET STATUS=?, TENTATIVAS=? WHERE ID_ATIVIDADE=? AND MATRICULA=?");
    $u->bind_param("siii", $status, $tent, $id_atividade, $matricula);
    $u->execute();
  }
} else {
  $tent = $tent_inc;
  $tempoIns = ($status === 'COMPLETO' && $tempo !== null) ? $tempo : "00:00:00";

  $i = $mysqli->prepare("INSERT INTO ATIVIDADE_STATUS (ID_ATIVIDADE, MATRICULA, TEMPO, STATUS, TENTATIVAS) VALUES (?,?,?,?,?)");
  $i->bind_param("iissi", $id_atividade, $matricula, $tempoIns, $status, $tent);
  $i->execute();
}

// Soma XP quando conclui
if ($status === 'COMPLETO' && $pontos > 0) {
  $g = $mysqli->prepare("SELECT MATRICULA FROM GAMIFICACAO WHERE MATRICULA=? LIMIT 1");
  $g->bind_param("i", $matricula);
  $g->execute();
  $gr = $g->get_result()->fetch_assoc();

  if (!$gr) {
    $ins = $mysqli->prepare("INSERT INTO GAMIFICACAO (MATRICULA, TOTAL_XP, LEVEL, TITLE) VALUES (?,0,1,'INICIANTE')");
    $ins->bind_param("i", $matricula);
    $ins->execute();
  }

  $up = $mysqli->prepare("UPDATE GAMIFICACAO SET TOTAL_XP = TOTAL_XP + ? WHERE MATRICULA=?");
  $up->bind_param("ii", $pontos, $matricula);
  $up->execute();
}

out(true, ["status"=>$status, "tentativas"=>$tent, "tempo"=>$tempo, "pontos"=>$pontos]);
