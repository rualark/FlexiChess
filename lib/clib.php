<?php

require_once "CsvDb.php";
$rla = array();

function load_rules() {
  GLOBAL $rla;
  $rdb = new CsvDb;
  $fname = "rules/rules.csv";
  echo $rdb->Open($fname);
  echo $rdb->Select();
  $rla = $rdb->result;
  for ($i=0; $i<count($rdb->result); ++$i) {
    $rl = $rdb->result[$i];
    $rid = $rl['Rid'];
    $rla[$rid] = $rdb->result[$i];
  }
}

function apply_ruleset($pid, $rs_id) {
  GLOBAL $ml, $prw, $rpos, $rpar;
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
    $rpos[$pid][$rid] = $w['r_poss'];
    $rpar[$pid][$rid][0] = $w['r_par0'];
    $rpar[$pid][$rid][1] = $w['r_par1'];
    $rpar[$pid][$rid][2] = $w['r_par2'];
  }
}

function send_js_var($pname, $value) {
  if ($value != "") {
    echo "$pname = \"$value\";\n";
  }
}
?>