<?php

session_start();
header("Content-Security-Policy: default-src 'self'");

if (!isset($_GET['sid']) || preg_match('/[^a-zA-Z0-9,-]/', $_GET['sid'])) {
  exit;
}

define('SID', $_GET['sid']);
$xpath = $_SESSION[SID]['xpath'];
session_write_close();

header('Content-type:text/plain; charset=utf-8');

echo "$xpath\n";
