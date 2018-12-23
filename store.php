<?php
require_once "lib/config.php";
require_once "lib/lib.php";

$act = secure_variable("act");
$u_id = secure_variable("u_id");
$g_id = secure_variable("g_id");
$rs_id0 = secure_variable("rs_id0");
$rs_id1 = secure_variable("rs_id1");
$pgn = secure_variable("pgn");
$fen = secure_variable("fen");

if ($act == "save_move") {
  if ($g_id == 0) {
    // Start game
    $q = "INSERT INTO games (u_id,rs_id0,rs_id1,time_started,time_changed,fen,pgn) 
      VALUES ('$u_id', '$rs_id0', '$rs_id1', NOW(), NOW(), \"$fen\", \"$pgn\")";
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