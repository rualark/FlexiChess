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

function apply_ruleset($pid, $rs_id) {
  GLOBAL $ml, $prw, $rdb, $rpos, $rpar;
  // Load rules
  $r = mysqli_query($ml,
    "SELECT * FROM rs_rules
      WHERE rs_id='$rs_id'");
  echo mysqli_error($ml);
  $n = mysqli_num_rows($r);
  for ($i=0; $i<$n; ++$i) {
    $w = mysqli_fetch_assoc($r);
    $rid = $w['r_id'];
    $prw[$pid][$rid] = $w;
    for ($x=0; $x<count($rdb->result); ++$x) {
      $rl = $rdb->result[$x];
      if ($rl['Rid'] != $rid) continue;
      $rpos[$pid][$rid] = $w['r_poss'];
      $rpar[$pid][$rid][0] = $rl['Par0'];
      $rpar[$pid][$rid][1] = $rl['Par1'];
      $rpar[$pid][$rid][2] = $rl['Par2'];
    }
  }
}

function send_js_var($pname, $value) {
  if ($value != "") {
    echo "$pname = \"$value\";\n";
  }
}
?>