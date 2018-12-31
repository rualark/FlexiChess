<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";

$title = "$site_name: Import PGN";

$act = secure_variable("act");
$fen = secure_variable("fen");
$pgn = secure_variable("pgn");

login();

if ($act == "import") {
  $q = "INSERT INTO games (u_id,time_started,time_changed,fen,pgn,ip,ip_forwarded) 
      VALUES ('$uid', NOW(), NOW(), \"$fen\", \"$pgn\", '$_SERVER[REMOTE_ADDR]', '$_SERVER[HTTP_X_FORWARDED_FOR]')";
  mysqli_query($ml,$q);
  echo mysqli_error($ml);
  $g_id = mysqli_insert_id($ml);
  die ("<script language=javascript>location.replace('game.php?g_id=$g_id');</script>");
}

include "template/menu.php";
echo "<div class=container>";
echo "<br>";

load_rules();

echo "<link rel='stylesheet' href='chessboardjs/css/chessboard-0.3.0.min.css'>\n";
echo "<link rel='stylesheet' href='css/play.css'>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.js'></script>\n";
echo "<script src='js/lib.js'></script>\n";
echo "<script src='js/simple-chess-ai.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='plugin/notify.min.js'></script>";

echo "<div id='board' style='width: 400px; margin:0 auto'></div>";

echo "<form action='importpgn.php' method='get'>";
echo "<input type=hidden name=act value='import'>";
echo "<input type=hidden id=input_fen name=fen value=''>";

echo "<div class='form-group'>";
echo "<label for='pgn'><b>Copy PGN here:</b></label>";
echo "<textarea onchange='ImportPGN();' class='form-control' rows=10 id='pgn' name='pgn'></textarea>";
echo "</select>\n";
echo "</div>\n";

echo "<button id='submit_btn' type='submit' onclick='send_fen()' class='btn btn-primary mb-2'>Import PGN</button>";
echo "</form>";

include "template/footer.php";
?>

<script>

  game = new Chess();
  let board = ChessBoard('board', {
  });

  document.getElementById('submit_btn').disabled = true;

  function send_fen() {
    document.getElementById('input_fen').value =
      board.fen() + ' ' +
      document.getElementById('sel_move_color').value + ' KQkq - 0 1';
  }

  function ImportPGN() {
    if (!game.load_pgn(document.getElementById('pgn').value)) {
      alert('You have an error in this PGN');
      return;
    }
    board.position(game.fen());
    document.getElementById('input_fen').value = game.fen();
    document.getElementById('submit_btn').disabled = false;
  }
</script>
