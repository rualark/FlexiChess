<?php

require_once "CsvDb.php";
$rla = array();

function load_rules() {
  GLOBAL $rla;
  $rdb = new CsvDb;
  $fname = "rules/rules.csv";
  echo $rdb->Open($fname);
  echo $rdb->Select();
  $rid_loaded = array();
  for ($i=0; $i<count($rdb->result); ++$i) {
    $rl = $rdb->result[$i];
    $rid = $rl['Rid'];
    if ($rid_loaded[$rid]) {
      echo "<font color='red'>Error: duplicate rule id $rid: $rid_loaded[$rid]</font><br>";
    }
    ++$rid_loaded[$rid];
    $rla[$rid] = $rdb->result[$i];
  }
}

function apply_ruleset($color, $rs_id) {
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
    $prw[$color][$rid] = $w;
    $rpos[$color][$rid] = $w['r_poss'];
    $rpar[$color][$rid][0] = $w['r_par0'];
    $rpar[$color][$rid][1] = $w['r_par1'];
    $rpar[$color][$rid][2] = $w['r_par2'];
  }
}

function send_js_var($pname, $value) {
  if ($value != "") {
    echo "$pname = \"$value\";\n";
  }
}

function start_collapse_container($cid, $header) {
  echo "<div class='collapse-container$cid' style='width: 100%'>";
  echo "<div class='collapse-header$cid'  align=left><span>$header...</span></div>";
  echo "<div class='collapse-content$cid'>";
  echo "<table>";
}

function end_collapse_container($cid, $header) {
  echo "</table>";
  echo "</div>";
  echo "</div>";
  ?>
  <script>
    $(".collapse-header<?=$cid ?>").click(function () {
      $header = $(this);
      //getting the next element
      $content = $header.next();
      //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
      $content.slideToggle(500, function () {
        //execute this after slideToggle is done
        //change text of header based on visibility of content div
        $header.text(function () {
          //change text based on condition
          return $content.is(":visible") ? "<?=$header ?>" : "<?=$header ?>...";
        });
      });
    });
  </script>
  <style>
    .collapse-container<?=$cid ?> {
      border:1px solid #d3d3d3;
    }
    .collapse-container<?=$cid ?> div {
    }
    .collapse-container<?=$cid ?> .collapse-header<?=$cid ?> {
      background-color:#d3d3d3;
      padding: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    .collapse-container<?=$cid ?> .collapse-content<?=$cid ?> {
      background-color: #f9f9f9;
      display: none;
      padding : 5px;
    }
  </style>
  <?
}

function get_difficulty($rid, $pos, $par0, $par1, $par2) {
  GLOBAL $rla;
  $cdif = $rla[$rid]['Difficulty'];
  $cdif = str_ireplace("x", $par0, $cdif);
  $cdif = str_ireplace("y", $par1, $cdif);
  $cdif = str_ireplace("z", $par2, $cdif);
  $cdif2 = eval('return '.$cdif.';');
  //echo "Evaluated '$cdif' to $cdif2 ";
  if (strpos($rla[$rid]['Rname'], 'XX') !== false) {
    $cdif2 = $cdif2 * min(20, $par0) / 20.0;
  }
  $cdif2 *= $pos / 100.0;
  //echo "($cdif2)";
  //echo "<br>";
  return $cdif2;
}
?>