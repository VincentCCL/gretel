<?php

session_start();
if (!isset($_GET['sid'])) {
  exit;
}

define('SID', $_GET['sid']);
$xpath = $_SESSION[SID]]'xpath'];
session_write_close();

header('Content-type:text/plain; charset=utf-8');

echo "$xpath\n";
