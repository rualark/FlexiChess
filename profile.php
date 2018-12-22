<?php
require_once "lib/lib.php";
require_once "lib/config.php";
require_once "lib/auth.php";

$title = "$site_name: Profile";

if (!login()) {
  die ("<script language=javascript>location.replace('index.php');</script>");
}

include "template/menu.php";

echo "<div class=container>";
echo "<br><br><h3>You can change password by signing out and clicking 'Forgot password'</h3><br>";

include "template/footer.php";
?>
