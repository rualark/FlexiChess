<?php
require_once "lib/config.php";
require_once "lib/lib.php";

$act = secure_variable("act");
$u_id = secure_variable("u_id");
$g_id = secure_variable("g_id");
$rs_b = secure_variable("rs_b");
$rs_w = secure_variable("rs_w");
$pgn = secure_variable("pgn");
$fen = secure_variable("fen");

if ($act == "save_move") {
  if ($g_id == 0) {
    // Start game
    $q = "INSERT INTO games (u_id,rs_b,rs_w,time_started,time_changed,fen,pgn,ip,ip_forwarded) 
      VALUES ('$u_id', '$rs_b', '$rs_w', NOW(), NOW(), \"$fen\", \"$pgn\", '$_SERVER[REMOTE_ADDR]', '$_SERVER[HTTP_X_FORWARDED_FOR]')";
    mysqli_query($ml,$q);
    echo mysqli_error($ml);
    echo mysqli_insert_id($ml);
  }
  else {
    mysqli_query($ml,
      "UPDATE games SET time_changed=NOW(), fen='$fen', pgn='$pgn'
      WHERE g_id='$g_id' 
    ");
    echo mysqli_error($ml);
    echo $g_id;
  }
}
?>