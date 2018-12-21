<?php

require_once "CsvDb.php";
$rdb = array();

function load_rules() {
  GLOBAL $rdb;
  $rdb = new CsvDb;
  $fname = "rules/rules.csv";
  echo $rdb->Open($fname);
  echo $rdb->Select();
}

?>