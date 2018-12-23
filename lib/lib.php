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
  else return secure_variable_post($st);
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
  echo "<meta property='og:image:width' content='600' />";
  echo "<meta property='og:image:height' content='315' />";
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

function is_mobile() {
  $useragent=$_SERVER['HTTP_USER_AGENT'];
  return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
}
?>
