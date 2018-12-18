<?php
require_once "lib/lib.php";
require_once "lib/CsvDb.php";

start_time();

echo "<link rel=icon href='icons/king.ico'>";
echo "<title>Play FlexiChess</title>\n";
echo "<script src='https://code.jquery.com/jquery-1.12.4.min.js'></script>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.min.js'></script>\n";
echo "<div id='board' style='width: 400px'></div>\n";

echo "<script>\n";
echo "MAX_RULES = 200;\n";
echo "rname = new Array(MAX_RULES); // Rule names\n";
echo "rdesc = new Array(MAX_RULES); // Rule descriptions\n";
echo "rpos = new Array(2); // Rule possibility for each player\n";
echo "rpos[0] = new Array(MAX_RULES);\n";
echo "rpos[1] = new Array(MAX_RULES);\n";
$rdb = new CsvDb;
$fname = "rules/rules.csv";
echo $rdb->Open($fname);
//print_r($rdb->header);
echo $rdb->Select();
//print_r($rdb->result);
for ($i=0; $i<count($rdb->result); ++$i) {
  echo "rname[" . $rdb->result[$i]['Rid'] . "] = \"" . $rdb->result[$i]['Rname'] . "\";\n";
  echo "rdesc[" . $rdb->result[$i]['Rid'] . "] = \"" . $rdb->result[$i]['Rdesc'] . "\";\n";
}

echo "rpos[0][101] = 100;\n";
echo "rpos[0][102] = 50;\n";
echo "rpos[0][103] = 50;\n";
echo "</script>";
?>

<script>
var board,
  game = new Chess(),
  statusEl = $('#status'),
  fenEl = $('#fen'),
  pgnEl = $('#pgn');

// do not pick up pieces if the game is over
// only pick up pieces for the side to move
var onDragStart = function(source, piece, position, orientation) {
  if (game.game_over() === true ||
      (game.turn() === 'w' && piece.search(/^b/) !== -1) ||
      (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
    return false;
  }
};

var onDrop = function(source, target) {
  // Allow only pawns
  var piece = game.get(source);
  if (piece.type != game.PAWN && piece.color == game.BLACK) return 'snapback';

  // see if the move is legal
  var move = game.move({
    from: source,
    to: target,
    promotion: 'q' // NOTE: always promote to a queen for example simplicity
  });

  // illegal move
  if (move === null) return 'snapback';

  updateStatus();
};

// update the board position after the piece snap 
// for castling, en passant, pawn promotion
var onSnapEnd = function() {
  board.position(game.fen());
};

var updateStatus = function() {
  var status = '';

  var moveColor = 'White';
  if (game.turn() === 'b') {
    moveColor = 'Black';
  }

  // checkmate?
  if (game.in_checkmate() === true) {
    status = 'Game over, ' + moveColor + ' is in checkmate.';
  }

  // draw?
  else if (game.in_draw() === true) {
    status = 'Game over, drawn position';
  }

  // game still on
  else {
    status = moveColor + ' to move';

    // check?
    if (game.in_check() === true) {
      status += ', ' + moveColor + ' is in check';
    }
  }

  statusEl.html(status);
  fenEl.html(game.fen());
  pgnEl.html(game.pgn());
};

var cfg = {
  draggable: true,
  position: 'start',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onSnapEnd: onSnapEnd
};
board = ChessBoard('board', cfg);

updateStatus();
</script>

<?php
stop_time();
?>
