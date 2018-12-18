<?php
require_once "lib/lib.php";
require_once "lib/CsvDb.php";

start_time();

echo "<link rel=icon href='icons/chess.ico'>";

echo "<pre>";
$rdb = new CsvDb;
$fname = "rules/rules.csv";
echo $rdb->Open($fname);
//print_r($rdb->header);
echo $rdb->Select();
//print_r($rdb->result);
for ($i=0; $i<count($rdb->result); ++$i) {
  echo $rdb->result[$i]['Rname'] . "<br>";
}
?>
