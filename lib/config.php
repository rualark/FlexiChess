<?php
$site_name = "FlexiChess";
$domain_main = "artportal.su";
$url_main = "http://$domain_main/flexichess";
$url_share = "http://$domain_main/flexichess";
$mail_method = "sendmail";
$mail_params = array('sendmail_path' => 'c:\openserver\modules\sendmail\sendmail.exe -t');
$os = "win";

$ml = mysqli_connect("localhost", "flexichess", "yifan");
if (!$ml) {
  die("<font color=red>Cannot connect to database. Please reload page</font>");
}
echo mysqli_connect_error();
mysqli_select_db($ml, "flexichess");
mysqli_query($ml, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
echo mysqli_error($ml);
