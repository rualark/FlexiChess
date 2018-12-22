<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";

$title = "$site_name: Rule set";
$act = secure_variable("act");
$rs_id = secure_variable("rs_id");
$rs_name = secure_variable("rs_name");

login();

load_rules();

if ($act == "new" || $act == "save") {
  if (!$uid) {
    die ("<script language=javascript>location.replace('index.php');</script>");
  }
}

if ($act == "save") {
  $rcount = 0;
  $rcount100 = 0;
  $diff = 0;
  foreach ($rla as $rid => $rl) {
    if ($_POST["pos$rid"] > 0) ++$rcount;
    if ($_POST["pos$rid"] == 100) ++$rcount100;
    $diff += $_POST["pos$rid"];
  }
  if (!$rs_id) {
    $r = mysqli_query($ml,
      "INSERT INTO rulesets 
    (u_id,time_created,time_changed,rs_name,rs_difficulty,rs_rcount,rs_rcount100) 
    VALUES ('$uid', NOW(), NOW(), '$rs_name', '$diff', '$rcount', '$rcount100')");
    echo mysqli_error($ml);
    $rs_id = mysqli_insert_id($ml);
  }
  else {
    // Load ruleset
    $r = mysqli_query($ml,
      "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    WHERE rs_id='$rs_id'");
    echo mysqli_error($ml);
    $rs = mysqli_fetch_assoc($r);
    // Cannot change ruleset of other user (only admin)
    if ($rs['u_id'] != $uid && !$ua['u_admin']) {
      die ("<script language=javascript>location.replace('index.php');</script>");
    }
    $r = mysqli_query($ml,
      "UPDATE rulesets SET time_changed=NOW(), rs_name='$rs_name', rs_difficulty='$diff', rs_rcount='$rcount', rs_rcount100='$rcount100'
      WHERE rs_id='$rs_id'");
    echo mysqli_error($ml);
  }
  foreach ($rla as $rid => $rl) {
    $r = mysqli_query($ml,
      "REPLACE INTO rs_rules 
    (rs_id,r_id,r_poss,r_par0,r_par1,r_par2) 
    VALUES ('$rs_id', '$rid', '".$_POST["pos$rid"]."', '".$_POST["xx$rid"]."', '".$_POST["yy$rid"]."', '".$_POST["zz$rid"]."')");
    echo mysqli_error($ml);
  }
}

include "template/menu.php";

echo "<div class=container>";
$readonly = "";
if ($act == "new") {
  echo "<br><h2 align=center>New rule set</h2>";
}
else {
  // Load ruleset
  $r = mysqli_query($ml,
    "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    WHERE rs_id='$rs_id'");
  echo mysqli_error($ml);
  $rs = mysqli_fetch_assoc($r);
  // Load rules
  $r = mysqli_query($ml,
    "SELECT * FROM rs_rules
      WHERE rs_id='$rs_id'");
  echo mysqli_error($ml);
  $n = mysqli_num_rows($r);
  for ($i=0; $i<$n; ++$i) {
    $w = mysqli_fetch_assoc($r);
    $rid = $w['r_id'];
    $rw[$rid] = $w;
    $rpos[$rid] = $w['r_poss'];
    $rpar0[$rid] = $w['r_par0'];
    $rpar1[$rid] = $w['r_par1'];
    $rpar2[$rid] = $w['r_par2'];
  }
  echo "<br><h2 align=center>Rule set $rs_id: $rs[rs_name]</h2>";
  echo "<p align='center'>";
  echo "<a data-toggle=tooltip data-placement=top title='Play white against this rule set' href='play.php?rs_id0=$rs_id'><img src=img/play_brown.png></a> ";
  echo "<a data-toggle=tooltip data-placement=top title='Play this rule set for both players' href='play.php?rs_id0=$rs_id&rs_id1=$rs_id'><img src=img/play_cyan.png></a>";
  echo "</p>";
  // Cannot change ruleset of other user (only admin)
  if ($rs['u_id'] != $uid && !$ua['u_admin']) {
    $readonly = "readonly";
  }
}


echo "<form class='form-inline' action='ruleset.php' method='post'>";
echo "<input type=hidden name=rs_id value='$rs_id'>";
echo "<input type=hidden name=act value=save>";

echo "<table cellpadding='3'>";
foreach ($rla as $rid => $rl) {
  // Load defaults if new ruleset
  if (!isset($rpos[$rid])) {
    $rpos[$rid] = 0;
    $rpar0[$rid] = $rl['Par0'];
    $rpar1[$rid] = $rl['Par1'];
    $rpar2[$rid] = $rl['Par2'];
  }
  echo "<tr>";
  echo "<td>";
  $st = $rl['Rname'];
  $st = str_replace("XX", "</span> <input $readonly type='number' min=0 style='width: 100px' class='form-control' id='xx$rid' name='xx$rid' value='$rpar0[$rid]'> <span>", $st);
  $st = str_replace("YY", "</span> <input $readonly type='number' min=0 style='width: 100px' class='form-control' id='yy$rid' name='yy$rid' value='$rpar1[$rid]'> <span>", $st);
  $st = str_replace("ZZ", "</span> <input $readonly type='number' min=0 style='width: 100px' class='form-control' id='zz$rid' name='zz$rid' value='$rpar2[$rid]'> <span>", $st);
  echo "<input type='checkbox' class='form-check-input' onchange=\"if (this.checked) {document.getElementById('pos$rid').value = '100';} else {document.getElementById('pos$rid').value = '0';}\" ";
  if ($rpos[$rid] == 100) echo "checked";
  echo "> ";
  echo "<input $readonly type='number' min=0 max=100 style='width: 100px' class='form-control' id='pos$rid' name='pos$rid' value='$rpos[$rid]'> % ";
  echo "<span data-toggle=tooltip data-placement=top title=\"$rl[Rdesc]\">";
  echo "$st\n";
  echo "</span>";
  echo "</fieldset>\n";
  echo "</div>\n";
}

echo "<tr>";
echo "<td>";
echo "<b>Rule set name:</b> <input $readonly placeholder='Enter descriptive rule set name' pattern='.{10,}' required title='10 characters minimum' class='form-control' style='width: 800px' type=text id=rs_name name=rs_name value='$rs[rs_name]'>";
if ($rs_id) {
  echo "<tr>";
  echo "<td>";
  echo "Rule set was created by $rs[u_name] at $rs[time_created]";
  if ($rs['time_changed'] != $rs['time_created']) echo " and changed at $rs[time_changed]";
}
echo "<tr>";
echo "<td>";
if ($readonly == "readonly") {
  echo "<span data-toggle=tooltip data-placement=top title='Cannot change this rule set, because it belongs to a different user'><a class=\"btn btn-secondary disabled mb-2\" href='#' role=\"button\" >Save rule set</a></span>";
}
else {
  echo "<button type='submit' class='btn btn-primary mb-2'>Save rule set</button>";
}
echo "</table>";
echo "</form>";

include "template/footer.php";
?>