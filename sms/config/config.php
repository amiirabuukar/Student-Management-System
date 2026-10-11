<?php
// Database settings. Override via environment variables in production (never commit real credentials).
return [
  'db_host' => getenv('SMS_DB_HOST') ?: '127.0.0.1',
  'db_name' => getenv('SMS_DB_NAME') ?: 'student_management_system',
  'db_user' => getenv('SMS_DB_USER') ?: 'root',   // XAMPP default
  'db_pass' => getenv('SMS_DB_PASS') ?: '',       // XAMPP default (empty)
  'per_page' => 8,
];
