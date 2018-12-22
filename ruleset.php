<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";

$title = "$site_name: Rule set";
$act = secure_variable("act");
$rs_id = secure_variable("rs_id");

login();

load_rules();

if ($act == "save") {
  for ($i=0; $i<count($rdb->result); ++$i) {
    $rl = $rdb->result[$i];
    $rid = $rl['Rid'];
  }
}

include "template/menu.php";

echo "<div class=container>";
if ($act == "new") {
  echo "<br><h2 align=center>New rule set</h2>";
}
else {
  echo "<br><h2 align=center>Rule set: $rs[rs_name]</h2>";
}


echo "<form class='form-inline' action='ruleset.php' method='post'>";
echo "<input type=hidden name=rs_id value='$rs_id'>";
echo "<input type=hidden name=act value=save>";

echo "<table cellpadding='3'>";
for ($i=0; $i<count($rdb->result); ++$i) {
  $rl = $rdb->result[$i];
  $rid = $rl['Rid'];
  $rpos[$rid] = 0;
  $rpar0[$rid] = $rl['Par0'];
  $rpar1[$rid] = $rl['Par1'];
  $rpar2[$rid] = $rl['Par2'];
  echo "<tr>";
  echo "<td>";
  $st = $rl['Rname'];
  $st = str_replace("XX", "</span> <input type='number' min=0 style='width: 100px' class='form-control' id='xx$rid' value='$rpar0[$rid]'> <span>", $st);
  $st = str_replace("YY", "</span> <input type='number' min=0 style='width: 100px' class='form-control' id='yy$rid' value='$rpar1[$rid]'> <span>", $st);
  $st = str_replace("ZZ", "</span> <input type='number' min=0 style='width: 100px' class='form-control' id='zz$rid' value='$rpar2[$rid]'> <span>", $st);
  echo "<input type='checkbox' class='form-check-input' onchange=\"if (this.checked) {document.getElementById('pos$rid').value = '100';} else {document.getElementById('pos$rid').value = '0';}\"> ";
  echo "<input type='number' min=0 max=100 style='width: 100px' class='form-control' id='pos$rid' value='$rpos[$rid]'> % ";
  echo "<span data-toggle=tooltip data-placement=top title=\"$rl[Rdesc]\">";
  echo "$st\n";
  echo "</span>";
  echo "</fieldset>\n";
  echo "</div>\n";
}

echo "<tr>";
echo "<td>";
echo "<b>Rule set name:</b> <input placeholder='Enter descriptive rule set name' pattern='.{10,}' required title='10 characters minimum' class='form-control' style='width: 800px' type=text id=name>";
echo "<tr>";
echo "<td>";
echo "<button type='submit' class='btn btn-primary mb-2'>Submit</button>";
echo "</table>";
echo "</form>";

include "template/footer.php";
?>