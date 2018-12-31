<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";

$title = "$site_name: New game";

$rs_b = secure_variable("rs_b");
$rs_w = secure_variable("rs_w");
$move_color = secure_variable("move_color");
if ($move_color == "") $move_color = "w";
$fen = secure_variable("fen");
if ($fen == "") $fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";

login();

include "template/menu.php";
echo "<div class=container>";
echo "<br>";
echo "<h5><p align='center'>Edit starting position:</p></h5>";

load_rules();

echo "<form action='play.php' method='get'>";
echo "<input type=hidden id=input_fen name=fen value=''>";

function show_ruleset_select($id, $color, $val){
  GLOBAL $ml;
  echo "<div class='form-group'>";
  echo "<label for='$id'><b>$color rule set</b></label>";
  echo "<select ";
  echo "class=\"form-control custom-select sel_$id\" id='sel_$id' name='$id'>\n";
  echo "<option value=0>None</option>\n";
  // Load rules
  $r = mysqli_query($ml,
    "SELECT * FROM rulesets");
  echo mysqli_error($ml);
  $n = mysqli_num_rows($r);
  for ($i=0; $i<$n; ++$i) {
    $w = mysqli_fetch_assoc($r);
    echo "<option value='$w[rs_id]'";
    if ($w['rs_id'] == $val) echo " selected";
    echo ">$w[rs_name]</option>";
  }
  echo "</select>\n";
  echo "</div>\n";
}

echo "<link rel='stylesheet' href='chessboardjs/css/chessboard-0.3.0.min.css'>\n";
echo "<link rel='stylesheet' href='css/play.css'>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.js'></script>\n";
echo "<script src='js/lib.js'></script>\n";
echo "<script src='js/simple-chess-ai.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='plugin/notify.min.js'></script>";

echo "<div id='board' style='width: 400px; margin:0 auto'></div>";
?>
<script>
  var board = ChessBoard('board', {
  draggable: true,
  dropOffBoard: 'trash',
  sparePieces: true
  });

  board.position('<?=$fen?>');
  $('#startBtn').on('click', board.start);
  $('#clearBtn').on('click', board.clear);

  function send_fen() {
    document.getElementById('input_fen').value =
      board.fen() + ' ' +
      document.getElementById('sel_move_color').value + ' KQkq - 0 1';
  }
</script>

<?php

echo "<div class='form-group'>";
echo "<label for='move_color'><b>Next move:</b></label>";
echo "<select class='form-control custom-select' id='sel_move_color' name='move_color'>\n";
echo "<option value=w";
if ($move_color == "w") echo " selected";
echo ">White to move</option>\n";
echo "<option value=b";
if ($move_color == "b") echo " selected";
echo ">Black to move</option>\n";
echo "</select>";
echo "</div>";

show_ruleset_select("rs_b", "Black", $rs_b);
show_ruleset_select("rs_w", "White", $rs_w);

echo "<button type='submit' onclick='send_fen()' class='btn btn-primary mb-2'>Start game</button>";
echo "</form>";

include "template/footer.php";
?>