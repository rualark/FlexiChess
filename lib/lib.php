<?php
function start_time() {
  GLOBAL $starttime, $starttime2;
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  if ($starttime2 == 0) $starttime2 = $mtime;
  $starttime = $mtime;
}

function secure_variable($st) {
  GLOBAL $ml;
  if (isset($_GET[$st])) return mysqli_real_escape_string($ml, $_GET[$st]);
  return "";
}

function secure_variable_post($st) {
  GLOBAL $ml;
  if (isset($_POST[$st])) return mysqli_real_escape_string($ml, $_POST[$st]);
  return "";
}

function insert_analytics_hit($table, $hitserver, $hitscript, $hitquery, $u_id) {
  GLOBAL $analytics_ip, $ml;
  if (isset($_SERVER["HTTP_X_REMOTE_ADDR"])) $analytics_ip =  $_SERVER["HTTP_X_REMOTE_ADDR"];
  else $analytics_ip = $_SERVER['REMOTE_ADDR'];
  $q = "INSERT INTO $table VALUES(NOW(), '$analytics_ip', '$hitserver', '$hitscript', '$hitquery', '$u_id')";
  mysqli_query($ml, $q);
  echo mysqli_error($ml);
}

function share_link($url, $title, $desc, $img, $services='facebook,vkontakte,gplus', $style='') {
  echo "
    <script type='text/javascript' src='//yastatic.net/es5-shims/0.0.2/es5-shims.min.js' charset='utf-8'></script>
    <script type='text/javascript' src='//yastatic.net/share2/share.js' charset='utf-8'></script>
    <div style='display: inline-block; $style' class='ya-share2' 
    data-services='$services'
    data-counter='' 
    data-description='$desc' 
    data-title='$title' 
    data-url='$url'
    data-image='$img'></div>
  ";
}

function share_header($url, $title, $desc, $img) {
  echo "<meta property='og:url' content='$url' />";
  echo "<meta property='og:image' content='$img' />";
  echo "<meta property='og:title' content='$title' />";
  echo "<meta property='og:description' content='$desc' />";
}

function show_chatovod($name) {
  echo "<div id=\"420065961799316593\" align=\"left\" style=\"width: 100%; overflow-y: hidden;\" class=\"wcustomhtml\"><script type=\"text/javascript\">";
  echo "var chatovodOnLoad = chatovodOnLoad || [];";
  echo "chatovodOnLoad.push(function() {";
  echo "    chatovod.addChatButton({host: \"$name.chatovod.com\", align: \"bottomRight\",";
  echo "        width: 490, height: 600, defaultLanguage: \"en\"});";
  echo "});";
  echo "(function() {";
  echo "    var po = document.createElement('script');";
  echo "    po.type = 'text/javascript'; po.charset = \"UTF-8\"; po.async = true;";
  echo "    po.src = (document.location.protocol=='https:'?'https:':'http:') + '//st1.chatovod.com/api/js/v1.js?2';";
  echo "    var s = document.getElementsByTagName('script')[0];";
  echo "    s.parentNode.insertBefore(po, s);";
  echo "})();";
  echo "</script></div>";
}

function stop_time($st="", $show=1) {
  GLOBAL $starttime, $starttime2, $view_child;
  // Show run time
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  $totaltime = round($endtime - $starttime, 3);
  $totaltime2 = round($endtime - $starttime2, 3);
  if ($show>0) {
    echo "<p>The script ran ".$totaltime." seconds ";
    if ($totaltime2>$totaltime) echo "($totaltime2 total) ";
    echo "$st. ";
  }
  // Set all subsequent views to child
  $view_child=1;
  // Restart timer
  start_time();
}

function make_color($r, $g=-1, $b=-1) {
  if (is_array($r) && sizeof($r) == 3)
    list($r, $g, $b) = $r;

  $r = intval($r);
  $g = intval($g);
  $b = intval($b);

  $r = dechex($r<0?0:($r>255?255:$r));
  $g = dechex($g<0?0:($g>255?255:$g));
  $b = dechex($b<0?0:($b>255?255:$b));

  $color = (strlen($r) < 2?'0':'').$r;
  $color .= (strlen($g) < 2?'0':'').$g;
  $color .= (strlen($b) < 2?'0':'').$b;
  return '#'.$color;
}

?>
