<?php
require_once "lib/clib.php";
require_once "lib/lib.php";
require_once "lib/config.php";
require_once "lib/auth.php";

$title = "$site_name: Privacy Policy";

login();

include "template/menu.php";

echo "<div class=container>";

require_once "template/privacy.php";
include "template/footer.php";

?>