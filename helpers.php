<?php

function sessionVariablesSet($sid, $array)
{
    foreach ($array as $entry) {
        if (!isset($_SESSION[$sid][$entry])) {
            return false;
        }
    }

    return true;
}

function postVariablesSet($array)
{
    foreach ($array as $entry) {
        if (!isset($_POST[$entry])) {
            return false;
        }
    }

    return true;
}

// Returns true if we're on a page with step > 1
function isBigStep() {
  global $step;
  if (isset($step) && $step > 1) {
    return true;
  }
  return false;
}

function isSearch() {
  global $currentPage;
  if (isset($currentPage) && ($currentPage == 'ebs' || $currentPage == 'xps')) {
    return true;
  }
  return false;
}

function isHome() {
  global $currentPage;
  if (isset($currentPage) && $currentPage == 'home') {
    return true;
  }
  return false;
}

function isDocs() {
  global $currentPage;
  if (isset($currentPage) && $currentPage == 'docs') {
    return true;
  }
  return false;
}

// For debugging: Pretty print variables
function var_dump_pre($mixed = null) {
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
  return null;
}
