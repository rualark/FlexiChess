<?php
require_once "lib/config.php";
require_once "lib/lib.php";
require_once "lib/auth.php";

$title = "$site_name: Games log";

$u_id = secure_variable("u_id");

login();

include "template/menu.php";

echo "<div class=container>";
echo "<br><h2 align=center>Games log</h2>";

echo "<table class='table'>"; // table-striped table-hover
echo "<thead>";
echo "<tr>";
echo "<th scope=col style='text-align: center;'>Started</th>";
echo "<th scope=col style='text-align: center;'>Last move</th>";
echo "<th scope=col style='text-align: center;'>User</th>";
echo "<th scope=col style='text-align: center;'>Black rule set</th>";
echo "<th scope=col style='text-align: center;'>White rule set</th>";
echo "</tr>\n";
echo "</thead>";
echo "<tbody>";
$r = mysqli_query($ml, "SELECT 
    games.g_id, games.time_started, games.time_changed, rsb.rs_name AS rsb_name, rsw.rs_name AS rsw_name, users.u_name 
    FROM games
    LEFT JOIN users USING (u_id)
    LEFT JOIN rulesets AS rsb ON (games.rs_b=rsb.rs_id) 
    LEFT JOIN rulesets AS rsw ON (games.rs_w=rsw.rs_id) 
    ORDER BY games.time_started DESC
    LIMIT 200");
echo mysqli_error($ml);
$n = mysqli_num_rows($r);
for ($i=0; $i<$n; ++$i) {
  $w = mysqli_fetch_assoc($r);
  echo "<tr>";
  echo "<td align='center'><a href='game.php?g_id=$w[g_id]'>$w[time_started]</td>";
  echo "<td align='center'>$w[time_changed]</td>";
  echo "<td align='center'>$w[u_name]</td>";
  echo "<td align='center'>$w[rsb_name]</td>";
  echo "<td align='center'>$w[rsw_name]</td>";
}
echo "</table>";

include "template/footer.php";
?>