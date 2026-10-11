<?php
// Core: session, PDO connection, security helpers.
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
$CFG = require __DIR__ . '/../config/config.php';
try {
  $pdo = new PDO("mysql:host={$CFG['db_host']};dbname={$CFG['db_name']};charset=utf8mb4", $CFG['db_user'], $CFG['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) { error_log($e->getMessage()); http_response_code(500); exit('Database connection failed. Check config/config.php and that MySQL is running.'); }

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function csrf_token(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_ok(){ return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']); }
function user(){ return $_SESSION['user'] ?? null; }
function role(){ return $_SESSION['user']['role'] ?? ''; }
function require_login(array $roles = []){
  if (!user()) { header('Location: login.php'); exit; }
  if ($roles && !in_array(role(), $roles, true)) { http_response_code(403); exit('403 - You are not authorized to view this page.'); }
}
function json_out($d, $code = 200){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($d); exit; }
function letter_grade($pct){ return $pct>=90?'A':($pct>=80?'B':($pct>=70?'C':($pct>=60?'D':'F'))); }
// student row linked to the logged-in student user
function my_student_id($pdo){ $s=$pdo->prepare('SELECT id FROM students WHERE user_id=?'); $s->execute([user()['id']]); return $s->fetchColumn() ?: 0; }
