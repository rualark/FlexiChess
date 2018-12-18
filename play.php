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
echo "<script src='chessboardjs/js/chess.js'></script>\n";
echo "<div id='board' style='width: 700px'></div>\n";
echo "<p><span id=status></span></p>";
//echo "<p>FEN: <span id=fen></span></p>";
echo "<p>PGN: <span id=pgn></span></p>";
echo "<p><span id=brules></span></p>";
echo "<p><span id=wrules></span></p>";
?>

<script>
let rname = []; // Rule names
let rdesc = []; // Rule descriptions
let rpos = []; // Rule possibility for each player
rpos[0] = [];
rpos[1] = [];
let rpar = []; // Rule parameters for each player
rpar[0] = [];
rpar[1] = [];

<?php
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

echo "rpos[0][101] = 0;\n";
echo "rpos[0][102] = 100;\n";
echo "rpos[0][103] = 100;\n";
echo "rpos[0][104] = 100;\n";
echo "rpos[0][105] = 100;\n";
echo "rpos[0][106] = 100;\n";
echo "rpos[0][107] = 100;\n";

echo "rpar[0][103] = 6;\n";
?>

let color_to_pid = [];
color_to_pid['b'] = 0;
color_to_pid['w'] = 1;

// Piece values
let pvalue = [];
pvalue['p'] = 1;
pvalue['b'] = 3;
pvalue['n'] = 3;
pvalue['r'] = 5;
pvalue['q'] = 9;
pvalue['k'] = 100;

let board,
  game = new Chess(),
  boardEl = $('#board'),
  statusEl = $('#status'),
  brulesEl = $('#brules'),
  wrulesEl = $('#wrules'),
  fenEl = $('#fen'),
  pgnEl = $('#pgn'),
  // Player id
  pid;

// Possible moves
let posMoves, posMoves2, ract;

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
    if (posMoves[i].disabled) continue;
    if (posMoves[i].from === square) {
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
    if (posMoves[i].from === source  && posMoves[i].to === target && posMoves[i].disabled === 0) {
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

function findObjectByKey(array, key, value) {
  for (let i = 0; i < array.length; i++) {
    if (array[i][key] === value) {
      return array[i];
    }
  }
  return null;
}

function countObjectsByKey(array, key, value) {
  let count = 0;
  for (let i = 0; i < array.length; i++) {
    if (array[i][key] === value) {
      ++count;
    }
  }
  return count;
}

function RevertRule() {
  for (let i=0; i<posMoves.length; ++i) {
    if (posMoves[i].disabled === 1) posMoves[i].disabled = 0;
  }
}

function ValidateRule(rid) {
  // Revert rules that give no possible moves
  if (findObjectByKey(posMoves, 'disabled', 0) === null) {
    RevertRule();
    ract[rid] = 3;
  }
  // Apply other rules
  else {
    for (let i=0; i<posMoves.length; ++i) {
      if (posMoves[i].disabled === 1) {
        ract[rid] = 2;
        posMoves[i].disabled = 2;
      }
    }
  }
  //console.log(rid, JSON.stringify(posMoves));
}

// Disable move only if it is not disabled (to avoid setting disabled=2 to disabled=1
function DisableMove(i) {
  if (posMoves[i].disabled === 0) {
    posMoves[i].disabled = 1;
  }
}

function DisablePawnsFirst(rid) {
  if (!ract[rid]) return;
  if (game.history().length > rpar[pid][103] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.piece !== 'p') {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisablePawnsDoubleMove(rid) {
  if (!ract[rid]) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.flags === 'b') {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableMustTakeIfStronger(rid) {
  if (!ract[rid]) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking with stronger
    if (tpiece && pvalue[move.piece] > pvalue[tpiece.type]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureStronger(rid) {
  if (!ract[rid]) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking a stronger
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMoveIfAttacked(rid) {
  if (!ract[rid]) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    // Skip not attacked squares that start moves
    if (!game.attacked(game.them(), move.from)) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCanMoveOnlyAttacked(rid) {
  if (!ract[rid]) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    // Skip attacked squares that start moves
    if (game.attacked(game.them(), move.from)) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoves() {
  // First run checks that force moves
  DisableMustTakeIfStronger(102);
  DisableCanMoveOnlyAttacked(105);
  // Now run checks that disable moves
  DisableCantMoveIfAttacked(104);
  DisablePawnsFirst(103);
  DisableCantCaptureStronger(106);
  DisablePawnsDoubleMove(107);
}

function ChooseRules() {
  ract = [];
  rpos[pid].forEach(function(item, i, arr) {
    if (item === 0) return;
    if (Math.random()*100 <= item) ract[i] = 1;
  });
}

function ShowRules() {
  let rst3 = '';
  let rst2 = '';
  let rst1 = '';
  let rst0 = '';
  rpos[pid].forEach(function(pos, rid, arr) {
    if (pos === 0) return;
    if (ract[rid] === 3) rst3 += rname[rid] + '<br>';
    else if (ract[rid] === 2) rst2 += rname[rid] + '<br>';
    else if (ract[rid] === 1) rst1 += rname[rid] + '<br>';
    else rst0 += rname[rid] + '<br>';
  });
  hst =
    "<font color=red>" + rst2 + "</font>" +
    "<font color=orange>" + rst1 + "</font>" +
    "<font color=blue>" + rst3 + "</font>" +
    "<font color=green>" + rst0 + "</font>";
  if (game.turn() === 'b') brulesEl.html(hst);
  else wrulesEl.html(hst);
}

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
    // Get maximum moves
    posMoves = game.moves({
      verbose: true
    });
    pid = color_to_pid[game.turn()];
    for (let i=0; i<posMoves.length; ++i) posMoves[i].disabled = 0;
    ChooseRules();
    DisableMoves();
    RemoveDisabledMoves();
    ShowRules();
    HighlightPosMoves();
    window.setTimeout(AutoMove, 500);
    //AutoMove();
  }

  statusEl.html(status);
  fenEl.html(game.fen());
  pgnEl.html(game.pgn());
};

function RemoveDisabledMoves() {
  posMoves2 = [];
  for (let i=0; i<posMoves.length; ++i) {
    if (posMoves[i].disabled === 0) {
      posMoves2.push(posMoves[i]);
    }
  }
}

function AutoMove() {
  if (countObjectsByKey(posMoves2, 'disabled', 0) === 1 || ract[101]) {
    let randomIndex = Math.floor(Math.random() * posMoves2.length);
    game.move(posMoves2[randomIndex]);
    board.position(game.fen());
    removeGreySquares();
    updateStatus();
  }
}

// Highlight possible moves
function HighlightPosMoves() {
  boardEl.find('.highlight-red').removeClass('highlight-red');
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    boardEl.find('.square-' + move.from).addClass('highlight-red');
  }
}

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
