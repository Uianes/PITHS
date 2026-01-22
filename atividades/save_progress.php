<?php
// save_progress.php
header('Content-Type: application/json; charset=utf-8');

$aluno_id     = isset($_POST['aluno_id']) ? (int)$_POST['aluno_id'] : 0;
$atividade_id = isset($_POST['atividade_id']) ? (int)$_POST['atividade_id'] : 0;
$status       = isset($_POST['status']) ? (int)$_POST['status'] : 0;

$tempo_segundos = isset($_POST['tempo_segundos']) ? (int)$_POST['tempo_segundos'] : null;
$pontos         = isset($_POST['pontos']) ? (int)$_POST['pontos'] : null;

$acertos        = isset($_POST['acertos']) ? (int)$_POST['acertos'] : null;
$total          = isset($_POST['total']) ? (int)$_POST['total'] : null;
$pontos_parciais= isset($_POST['pontos_parciais']) ? (int)$_POST['pontos_parciais'] : null;

// TODO: aqui você conecta no MySQL e faz UPSERT na atividade_status
// Exemplo conceitual:
// - se status=1: marca em andamento e salva acertos/parciais
// - se status=2: marca concluído, salva tempo e pontos finais

echo json_encode([
  "ok" => true,
  "aluno_id" => $aluno_id,
  "atividade_id" => $atividade_id,
  "status" => $status,
  "tempo_segundos" => $tempo_segundos,
  "pontos" => $pontos,
  "acertos" => $acertos,
  "total" => $total,
  "pontos_parciais" => $pontos_parciais
], JSON_UNESCAPED_UNICODE);