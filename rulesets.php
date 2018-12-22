<?php
require_once "lib/config.php";
require_once "lib/lib.php";
require_once "lib/auth.php";

$title = "$site_name: Rule sets";

login();

include "template/menu.php";

echo "<div class=container>";
echo "<br><h2 align=center>Rule sets</h2>";

echo "<table class='table'>"; // table-striped table-hover
echo "<thead>";
echo "<tr>";
echo "<th scope=col style='text-align: center;'>Play</th>";
echo "<th scope=col style='text-align: center;'>Name</th>";
echo "<th scope=col style='text-align: center;'>Difficulty</th>";
echo "<th scope=col style='text-align: center;'>Author</th>";
echo "<th scope=col style='text-align: center;'>Created</th>";
echo "</tr>\n";
echo "</thead>";
echo "<tbody>";
$r = mysqli_query($ml, "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    ORDER BY playcount DESC, time_created DESC
    LIMIT 200");
echo mysqli_error($ml);
$n = mysqli_num_rows($r);
for ($i=0; $i<$n; ++$i) {
  $w = mysqli_fetch_assoc($r);
  echo "<tr>";
  echo "<td align='center'>";
  echo "<a data-toggle=tooltip data-placement=top title='Play white against this rule set' href='play.php?rs_id0=$w[rs_id]'><img src=img/play_brown.png></a> ";
  echo "<a data-toggle=tooltip data-placement=top title='Play this rule set for both players' href='play.php?rs_id0=$w[rs_id]&rs_id1=$w[rs_id]'><img src=img/play_cyan.png></a>";
  echo "</td>";
  echo "<td align='center'><a href='ruleset.php?act=edit&rs_id=$w[rs_id]'>$w[rs_name]</td>";
  echo "<td align='center'>$w[rs_difficulty]</td>";
  echo "<td align='center'>$w[u_name]</td>";
  echo "<td align='center'>$w[time_created]</td>";
}
echo "</table>";

include "template/footer.php";
?>