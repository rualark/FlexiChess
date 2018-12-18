<?php
require_once "lib/lib.php";
require_once "lib/CsvDb.php";

start_time();

echo "<link rel=icon href='icons/king.ico'>";
echo "<title>Play FlexiChess</title>\n";
echo "<link rel='stylesheet' href='chessboardjs/css/chessboard-0.3.0.min.css'>\n";
echo "<link rel='stylesheet' href='css/play.css'>\n";
echo "<script src='https://code.jquery.com/jquery-1.12.4.min.js'></script>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.min.js'></script>\n";
echo "<div id='board' style='width: 400px'></div>\n";
echo "<p><span id=status></span></p>";
//echo "<p>FEN: <span id=fen></span></p>";
echo "<p>PGN: <span id=pgn></span></p>";
echo "<p><span id=debug></span></p>";

echo "<script>\n";
echo "let rname = []; // Rule names\n";
echo "let rdesc = []; // Rule descriptions\n";
echo "let rpos = []; // Rule possibility for each player\n";
echo "rpos[0] = [];\n";
echo "rpos[1] = [];\n";
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
let board,
  game = new Chess(),
  boardEl = $('#board'),
  statusEl = $('#status'),
  debugEl = $('#debug'),
  fenEl = $('#fen'),
  pgnEl = $('#pgn'),
  squareClass = 'square-55d63',
  squareToHighlight,
  colorToHighlight;

// Possible moves
let posMoves;

let removeGreySquares = function() {
  $('#board .square-55d63').css('background', '');
};

let greySquare = function(square) {
  let squareEl = $('#board .square-' + square);

  let background = '#a9a9a9';
  if (squareEl.hasClass('black-3c85d') === true) {
    background = '#696969';
  }

  squareEl.css('background', background);
};

let onMouseoverSquare = function(square, piece) {
  // exit if there are no moves available for this square
  if (posMoves.length === 0) return;

  // highlight the possible squares for this piece
  for (let i = 0; i < posMoves.length; i++) {
    if (posMoves[i].from == square) {
      greySquare(posMoves[i].to);
      greySquare(square);
    }
  }
};

let onMouseoutSquare = function(square, piece) {
  removeGreySquares();
};

// do not pick up pieces if the game is over
// only pick up pieces for the side to move
let onDragStart = function(source, piece, position, orientation) {
  if (game.game_over() === true ||
      (game.turn() === 'w' && piece.search(/^b/) !== -1) ||
      (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
    return false;
  }
};

let onDrop = function(source, target) {
  removeGreySquares();
  // Allow only pawns
  let piece = game.get(source);
  //if (piece.type !== game.PAWN && piece.color === game.BLACK) return 'snapback';

  let found = 0;
  for (let i=0; i<posMoves.length; ++i) {
    if (posMoves[i].from === source  && posMoves[i].to === target) {
      found = 1;
      break;
    }
  }
  // illegal move
  if (!found) return 'snapback';

  // see if the move is legal
  let move = game.move({
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
let onSnapEnd = function() {
  board.position(game.fen());
};

let updateStatus = function() {
  let status = '';

  let moveColor = 'White';
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
    // Cycle through moves
    boardEl.find('.highlight-red').removeClass('highlight-red');
    let possibleMoves = game.moves({
      verbose: true
    });
    posMoves = [];
    for (let i=0; i<possibleMoves.length; ++i) {
      let move = possibleMoves[i];
      if (move.piece === 'p') {
        boardEl.find('.square-' + move.from).addClass('highlight-red');
        posMoves.push(move);
      }
    }
  }

  statusEl.html(status);
  fenEl.html(game.fen());
  pgnEl.html(game.pgn());
};

let cfg = {
  draggable: true,
  position: 'start',
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd
};
board = ChessBoard('board', cfg);

updateStatus();
</script>

<?php
stop_time();
?>
