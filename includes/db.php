<?php
$config = [
  'db_host' => '127.0.0.1',
  'db_user' => 'root',
  'db_pass' => '',
  'db_name' => 'piths',
];


$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die('Falha ao conectar no banco: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
