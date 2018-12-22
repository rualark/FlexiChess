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
echo "<th scope=col style='text-align: center;'>Created</th>";
echo "<th scope=col style='text-align: center;'>Name</th>";
echo "<th scope=col style='text-align: center;'>Difficulty</th>";
echo "</tr>\n";
echo "</thead>";
echo "<tbody>";
$r = mysqli_query($ml, "SELECT * FROM rulesets 
    ORDER BY time_created DESC
    LIMIT 200");
echo mysqli_error($ml);
$n = mysqli_num_rows($r);
for ($i=0; $i<$n; ++$i) {
  $w = mysqli_fetch_assoc($r);
  echo "<tr>";
  echo "<td align='center'><img height=20 src=img/play6.png></td>";
  echo "<td align='center'>$w[time_created]</td>";
  echo "<td align='center'>$w[rs_name]</td>";
  echo "<td align='center'>$w[rs_difficulty]</td>";
}
echo "</table>";

include "template/footer.php";
?>