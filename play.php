<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";
require_once "lib/Mobile_Detect.php";

$mobile_detect = new Mobile_Detect;

start_time();

$rs_b = secure_variable("rs_b");
$rs_w = secure_variable("rs_w");
$view = secure_variable("view");
if ($view == "mobile" || ($view == "" && $mobile_detect->isMobile())) $show_mobile = 1;

if ($rs_b == '') $rs_b = 0;
if ($rs_w == '') $rs_w = 0;

$title = "$site_name: Play";

login();

load_rules();

// Load ruleset
$r = mysqli_query($ml,
  "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    WHERE rs_id='$rs_b'");
echo mysqli_error($ml);
$rs0 = mysqli_fetch_assoc($r);
// Load ruleset
$r = mysqli_query($ml,
  "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    WHERE rs_id='$rs_w'");
echo mysqli_error($ml);
$rs1 = mysqli_fetch_assoc($r);

if ($show_mobile) {
  $rule_height = 100;
  $rule_width = 600;
  $board_width = 600;
  ?>
  <title><?=$title ?></title>
  <link rel="manifest" href="manifest.json">
  <link rel="icon" href="icons/king.ico">

  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="application-name" content="FlexiChess">
  <meta name="apple-mobile-web-app-title" content="FlexiChess">
  <meta name="msapplication-starturl" content="/">
  <meta name="viewport" content="width=700,shrink-to-fit=no">
  <style>
    body {
      overscroll-behavior-y: contain;
      position: fixed;
      overflow: hidden;
      touch-action: none;
    }
  </style>
  <script language='JavaScript' type='text/javascript' src='plugin/jquery.min.js'></script>
  <script>
    document.body.addEventListener('touchmove',function(event){
      event.preventDefault();
    });
  </script>
  <?
}
else {
  include "template/menu.php";
  echo "<p>";
  echo "<div class=container>";
  $rule_height = 200;
  $rule_width = 400;
  $board_width = 600;
}

echo "<link rel='stylesheet' href='chessboardjs/css/chessboard-0.3.0.min.css'>\n";
echo "<link rel='stylesheet' href='css/play.css'>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.js'></script>\n";
echo "<script src='js/lib.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='plugin/notify.min.js'></script>";
echo "<table>";
echo "<tr>";
echo "<td valign='top'>";
echo "<canvas id='rating_indicator' width=5 height={$board_width}></canvas>";
echo "<td valign='top'>";
echo "<div id='board' style='width: {$board_width}px'></div>\n";
if ($show_mobile) {
  echo "<br>";
}
else {
  echo "<td valign='top'>";
}
echo "<button onclick=\"Undo();\">Undo</button>\n";
echo "<button onclick=\"RandomMove();\">Random</button>\n";
echo "<button onclick=\"ShowHint();\">Hint</button>\n";
if ($show_mobile) {
  echo "<form style='display:inline;' role=search method=get action='' target=_blank>";
  echo "<input type='hidden' name='rs_b' value='$rs_b'>";
  echo "<input type='hidden' name='rs_w' value='$rs_w'>";
  echo "<input type='hidden' name='view' value='desktop'>";
  echo "<input type=submit value='Desktop view'></form>\n";
  echo "<form style='display:inline;' role=search method=get action='rulesets.php' target=_blank>";
  echo "<input type=submit value='Exit'></form>\n";
  echo " <span id=status></span>";
}
else {
  echo "<form style='display:inline;' role=search method=get action='' target=_blank>";
  echo "<input type='hidden' name='rs_b' value='$rs_b'>";
  echo "<input type='hidden' name='rs_w' value='$rs_w'>";
  echo "<input type='hidden' name='view' value='mobile'>";
  echo "<input type=submit value='Mobile view'></form>\n";
  echo "<br><span id=status></span>";
}

echo "<div style='width: 400px' id=bcaptures></div>";
echo "<div style='padding: 2px; line-height: 1; width: {$rule_width}px; height: {$rule_height}px; overflow-y: scroll; background-color: black; border:1px solid black' id=brules></div>";
echo "<div style='padding: 2px; line-height: 1; width: {$rule_width}px; height: {$rule_height}px; overflow-y: scroll; border:1px solid black' id=wrules></div>";
echo "<div style='width: 400px; ' id=wcaptures></div>";
echo "<div style='line-height: 1; width: {$rule_width}px; height: 50px; overflow-y: scroll; border:1px solid black' id=pgn></div>";
echo "</table>";
?>

<script language='JavaScript' type='text/javascript' src='js/vars.js'></script>
<script language='JavaScript' type='text/javascript' src='js/stockfish_engine.js'></script>
<script>

<?php

load_rules();

$rpos = array();
$rpar = array();

foreach ($rla as $rid => $rl) {
  echo "rname[$rid] = \"$rl[Rname]\";\n";
  echo "rdesc[$rid] = \"$rl[Rdesc]\";\n";
  $rpar['b'][$rid][0] = $rl['Par0'];
  $rpar['w'][$rid][0] = $rl['Par0'];
  $rpar['b'][$rid][1] = $rl['Par1'];
  $rpar['w'][$rid][1] = $rl['Par1'];
  $rpar['b'][$rid][2] = $rl['Par2'];
  $rpar['w'][$rid][2] = $rl['Par2'];
}

if ($rs_b) apply_ruleset('b', $rs_b);
if ($rs_w) apply_ruleset('w', $rs_w);

if ($rs_b == 0 && $rs_w == 0 && $uid == 1) {
  //$rpos['b'][153] = 100;
}

foreach ($rla as $rid => $rl) {
  if ($rpos['b'][$rid]) {
    send_js_var("rpos['b'][$rid]", $rpos['b'][$rid]);
    send_js_var("rpar['b'][$rid][0]", $rpar['b'][$rid][0]);
    send_js_var("rpar['b'][$rid][1]", $rpar['b'][$rid][1]);
    send_js_var("rpar['b'][$rid][2]", $rpar['b'][$rid][2]);
  }
  if ($rpos['w'][$rid]) {
    send_js_var("rpos['w'][$rid]", $rpos['w'][$rid]);
    send_js_var("rpar['w'][$rid][0]", $rpar['w'][$rid][0]);
    send_js_var("rpar['w'][$rid][1]", $rpar['w'][$rid][1]);
    send_js_var("rpar['w'][$rid][2]", $rpar['w'][$rid][2]);
  }
}

echo "rs_b = $rs_b;\n";
echo "rs_w = $rs_w;\n";
?>

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

function HighlightSquares(square) {
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
}

let onMouseoverSquare = function(square, piece) {
  HighlightSquares(square);
};

let onMouseoutSquare = function(square, piece) {
  removeGreySquares();
};

// do not pick up pieces if the game is over
// only pick up pieces for the side to move
let onDragStart = function(source, piece, position, orientation) {
  HighlightSquares(source);
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
  let move = MakeMove({
    from: source,
    to: target,
    promotion: 'q' // NOTE: always promote to a queen for example simplicity
  });

  // illegal move
  if (move === null) return 'snapback';

  // Send move
  $.ajax({
    type: 'POST',
    url: 'store.php',
    data: {
      act: 'save_move',
      g_id: game_id,
      u_id: <?=$uid?>,
      fen: game.fen(),
      pgn: game.pgn()
    },
    dataType: 'html',
    success: function(data) {
      game_id = data;
      //$.notify("Saved game " + game_id + " state change", "success");
    },
    error: function (error) {
      //$.notify(error, "error");
    }
  });

  updateStatus();
};

// update the board position after the piece snap
// for castling, en passant, pawn promotion
let onSnapEnd = function() {
  board.position(game.fen());
};

function eval_pos() {
  if (!evaler) return;
  eval_best_move.length = game.history().length;
  evaler.send("position fen " + game.fen());
  evaler.send("go depth " + eval_depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      eval_best_move[game.history().length] = matches[1];
      eval_ponder[game.history().length] = matches[2];
    }
  }, function stream(str)
  {
    let matches = str.match(/depth (\d+) .*score (cp|mate) ([-\d]+) .*pv (.+)/),
      score,
      type,
      depth,
      pv,
      data;

    if (matches) {
      depth = Number(matches[1]);
      type = matches[2];
      score = Number(matches[3]);
      pv = matches[4].split(" ");

      if (type === "mate") {
        score = 100000 * score;
      }
      /// Convert the relative score to an absolute score.
      if (game.turn() === "b") {
        score *= -1;
      }

      ShowRating(score);
    }
  });
}

function stockfish_go(color) {
  if (!engine[game.turn()]) return;
  engine[game.turn()].state = 'Running';
  engine[game.turn()].send("position fen " + game.fen());
  engine[game.turn()].mpv = [];
  engine[game.turn()].send("go depth " + engine[game.turn()].depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      engine[game.turn()].best_move[game.history().length] = matches[1];
      engine[game.turn()].ponder[game.history().length] = matches[2];
      console.log("updating status");
      updateStatus();
      console.log("switching to wait");
      engine[game.turn()].state = 'Wait';
    }
  }, function stream(str)
  {
    let matches = str.match(/depth (\d+) .*score (cp|mate) ([-\d]+) .*pv (.+)/),
      score,
      type,
      depth,
      pv,
      data;
    if (!matches) return;
    depth = Number(matches[1]);
    // Wrong depth
    if (depth != engine[game.turn()].depth) return;
    type = matches[2];
    score = Number(matches[3]);
    pv = matches[4].split(" ");
    if (type === "mate") {
      score = 100000 * score;
    }
    engine[game.turn()].mpv[pv[0]] = score;
  });
}

function MakeMove(move) {
  let result = game.move(move);
  return result;
}

function CapturesCount(color) {
  let cnt = 0;
  let hist = game.history({ verbose: true });
  for (let i=0; i<hist.length; ++i) {
    if (hist[i].color === color && hist[i].captured != null) ++cnt;
  }
  return cnt;
}

function CapturesValue(color) {
  let val = 0;
  let hist = game.history({ verbose: true });
  for (let i=0; i<hist.length; ++i) {
    if (hist[i].color === color && hist[i].captured != null)
      val += pvalue[hist[i].captured]
  }
  return val;
}

function Undo() {
  if (engine[game.turn()] && engine[game.turn()].state !== 'Wait') {
    engine[game.turn()].send("stop");
    window.setTimeout(Undo, 100);
    return;
  }
  console.log("Starting undo");
  let result = game.undo();
  board.position(game.fen());
  updateStatus();
  return result;
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
    rdis[rid] = [];
    for (let i=0; i<posMoves.length; ++i) {
      if (posMoves[i].disabled === 1) {
        ract[rid] = 2;
        posMoves[i].disabled = 2;
        rdis[rid].push(posMoves[i]);
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

function DisablePawns(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (CapturesValue(game.them()) >= rpar[game.turn()][rid][1]) return;
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
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
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
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking with stronger
    if (tpiece && pvalue[move.piece] > pvalue[tpiece.type] &&
      move.piece !== 'k') continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeUnprotectedOrStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable if taking unprotected
    if (tpiece && !move.chess.attackedCnt(game.them(), move.to)) continue;
    // Do not disable if taking a stronger
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeProtectedIfStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking with stronger
    if (tpiece && pvalue[move.piece] > pvalue[tpiece.type] && move.piece !== 'k'
        && move.chess.attackedCnt(game.them(), move.to)) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTake(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking
    if (tpiece) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeWithPawn(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking
    if (tpiece && move.piece === 'p') continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeWeakest(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let min_pvalue = 10000;
  // Get minimum pvalue
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece && pvalue[tpiece.type] < min_pvalue) min_pvalue = pvalue[tpiece.type];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking weakest
    if (tpiece && pvalue[tpiece.type] === min_pvalue) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeStrongest(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let max_pvalue = 0;
  // Get minimum pvalue
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece && pvalue[tpiece.type] > max_pvalue) max_pvalue = pvalue[tpiece.type];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking strongest
    if (tpiece && pvalue[tpiece.type] === max_pvalue) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCanTakeOnlyWeakest(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let min_pvalue = 10000;
  // Get minimum pvalue
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece && pvalue[tpiece.type] < min_pvalue) min_pvalue = pvalue[tpiece.type];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking weakest
    if (tpiece && pvalue[tpiece.type] !== min_pvalue)
        DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeWithStrongest(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let max_pvalue = 0;
  // Get maximum pvalue
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece && pvalue[move.piece] > max_pvalue) max_pvalue = pvalue[move.piece];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking with strongest
    if (tpiece && pvalue[move.piece] === max_pvalue) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMustTakeWithWeakest(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let min_pvalue = 1000;
  // Get maximum pvalue
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece && pvalue[move.piece] < min_pvalue) min_pvalue = pvalue[move.piece];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable only if taking with weakest
    if (tpiece && pvalue[move.piece] === min_pvalue) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking a stronger
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureStrongerWithCaptured(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking a stronger by captured
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type] &&
      last_cap === move.piece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureCapturer(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking capturer
    if (tpiece && hist[hist.length - 1].to === move.to)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureAfterYourCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureStrongerAfterYourCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCaptureOnlyAfterCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece && !last_cap)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureCapturerStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking capturer
    if (tpiece && hist[hist.length - 1].to === move.to &&
      pvalue[move.piece] < pvalue[tpiece.type])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureWithCaptured(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking by captured
    if (tpiece &&
      last_cap === move.piece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantCaptureCapturerType(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (!last_cap) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking by captured
    if (tpiece &&
      last_cap === tpiece.type)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMultiCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  // Check if all previous moves were captures
  for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
    if (hist[hist.length - 2 - i * 2].captured == null) return;
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking again
    if (tpiece) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMultiCaptureStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  // Check if all previous moves were captures
  for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
    if (hist[hist.length - 2 - i * 2].captured == null) return;
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking again
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type]) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMultiCaptureType(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking again
    if (tpiece) {
      // Check if all previous moves were captures with same type
      let found = 1;
      for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
        if (hist[hist.length - 2 - i * 2].captured == null ||
          hist[hist.length - 2 - i * 2].piece !== move.piece) {
          found = 0;
          break;
        }
      }
      if (found) DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableCantMultiMoveType(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Check if all previous moves were moves with same type
    let found = 1;
    for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
      if (hist[hist.length - 2 - i * 2].piece !== move.piece) {
        found = 0;
        break;
      }
    }
    if (found) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMultiCaptureSame(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  // Check if all previous moves were captures
  for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
    if (hist[hist.length - 2 - i * 2].captured == null) return;
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking again
    if (tpiece) {
      let found = 1;
      // Check first previous move
      if (move.from !== hist[hist.length - 2].to) continue;
      // Check if all previous moves form chain
      for (let i=1; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
        if (hist[hist.length - 2 - i * 2].to !== hist[hist.length - i * 2].from) {
          found = 0;
          break;
        }
      }
      if (found) DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableCantMultiMoveSame(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    let found = 1;
    // Check first previous move
    if (move.from !== hist[hist.length - 2].to) continue;
    // Check if all previous moves form chain
    for (let i=1; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
      if (hist[hist.length - 2 - i * 2].to !== hist[hist.length - i * 2].from) {
        found = 0;
        break;
      }
    }
    if (found) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMultiCaptureTypeStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < (rpar[game.turn()][rid][1] - 1) * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking again
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type]) {
      // Check if all previous moves were captures with same type
      let found = 1;
      for (let i=0; i<rpar[game.turn()][rid][1] - 1 ; ++i) {
        if (hist[hist.length - 2 - i * 2].captured == null ||
          hist[hist.length - 2 - i * 2].piece !== move.piece) {
          found = 0;
          break;
        }
      }
      if (found) DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableCantCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMoveIfAttacked(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    // Skip not attacked squares that start moves
    if (game.attackedCnt(game.them(), move.from) <= rpar[game.turn()][rid][1]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCanMoveOnlyAttacked(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    // Skip attacked squares that start moves
    if (game.attackedCnt(game.them(), move.from) > rpar[game.turn()][rid][1]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCanMoveOnlyAttackedNoCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Skip attacked squares that start moves
    if (game.attackedCnt(game.them(), move.from) > rpar[game.turn()][rid][1] && !tpiece) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoCaptureFromCheck(rid) {
  if (!ract[rid]) return;
  if (!game.in_check()) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableKingNoCaptureFromCheck(rid) {
  if (!ract[rid]) return;
  if (!game.in_check()) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking by king
    if (tpiece && move.piece === 'k')
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoFirstCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (CapturesCount(game.them()) > 0) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
    if (tpiece)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoCaptureUnprotected(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i = 0; i < posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if capturing unprotected (except king)
    if (tpiece && move.piece !== 'k' &&
      !move.chess.attackedCnt(game.them(), move.to))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoCaptureProtected(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i = 0; i < posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if capturing protected (except king)
    if (tpiece && move.piece !== 'k' &&
      move.chess.attackedCnt(game.them(), move.to))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoCaptureProtectedStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i = 0; i < posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if capturing protected stronger (except king)
    if (tpiece && move.piece !== 'k' && pvalue[move.piece] < pvalue[tpiece.type] &&
      move.chess.attackedCnt(game.them(), move.to))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoveIntoAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (game.attacked(game.them(), move.to)) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMoveIntoAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (game.attackedCnt(game.them(), move.to) <= rpar[game.turn()][rid][1]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCantMoveIntoAttackButCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece ||
      game.attackedCnt(game.them(), move.to) <= rpar[game.turn()][rid][1]) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableRemoveAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let acnt1 = game.attackedCnt(game.them(), move.from);
    let acnt2 = posMoves[i].chess.attackedCnt(game.them(), move.to);
    if (acnt1 > rpar[game.turn()][rid][1] && acnt2 < acnt1) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableRemoveAttackNoCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    let acnt1 = game.attackedCnt(game.them(), move.from);
    let acnt2 = posMoves[i].chess.attackedCnt(game.them(), move.to);
    if (acnt1 > rpar[game.turn()][rid][1] && acnt2 < acnt1
        && !tpiece) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableRandomPieceTypes(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  // Choose pieces
  let pal = [];
  for (let i=0; i<rpar[game.turn()][rid][1]; ++i) {
    let rnd = Math.floor(Math.random() * 5);
    if (rnd === 5) rnd = 4;
    if (rnd === 0) pal['p'] = 1;
    if (rnd === 1) pal['b'] = 1;
    if (rnd === 2) pal['n'] = 1;
    if (rnd === 3) pal['r'] = 1;
    if (rnd === 4) pal['q'] = 1;
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    // Allow king and enabled
    if (move.piece === 'k' || pal[move.piece] === 1) continue;
    DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableRandomMoves(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let dis_cnt = posMoves.length * rpar[game.turn()][rid][1] / 100;
  for (let i=0; i<dis_cnt; ++i) {
    let randomIndex = Math.floor(Math.random() * posMoves.length);
    DisableMove(randomIndex);
  }
  ValidateRule(rid);
}

function DisableRandomPieces(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let froms = [];
  // Get unique movable pieces
  for (let i=0; i<posMoves.length; ++i) {
    froms[posMoves[i].from] = 1;
  }
  // Scan unique movable pieces
  for (let key in froms) {
    if (Math.random() * 100 > rpar[game.turn()][rid][1]) continue;
    // Disable all moves for chosen piece
    for (let i=0; i<posMoves.length; ++i) {
      if (posMoves[i].from === key)
        DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableNeedMovesEachPieceType(rid) {
  if (!ract[rid]) return;
  if (hist.length < rpar[game.turn()][rid][1] * 2) return;
  // Check moves for each piece type
  let ptneed = [];
  for (let pt_id in ptypes) {
    let found = 0;
    for (let i=0; i<rpar[game.turn()][rid][1]; ++i) {
      console.log(i, hist[hist.length - 2 - i * 2].piece, pt_id, ptypes[pt_id], ptneed.length);
      if (hist[hist.length - 2 - i * 2].piece === ptypes[pt_id]) {
        found = 1;
        break;
      }
    }
    if (found) continue;
    ptneed.push(ptypes[pt_id]);
  }
  console.log(ptneed, ptneed.length);
  // No needed piece types
  if (!ptneed.length) return;
  // Disable all moves except needed
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let found = 0;
    for (let pt_id in ptneed) {
      console.log(move.piece, ptneed[pt_id]);
      if (move.piece === ptneed[pt_id]) found = 1;
    }
    if (!found) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNeedKingMoves(rid) {
  if (!ract[rid]) return;
  if (hist.length < rpar[game.turn()][rid][1] * 2) return;
  let ptype = 'k';
  let found = 0;
  for (let i=0; i<rpar[game.turn()][rid][1]; ++i) {
    if (hist[hist.length - 2 - i * 2].piece === ptype) {
      found = 1;
      break;
    }
  }
  if (found) return;
  // Disable all moves except needed
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.piece !== ptype) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNeedPawnMoves(rid) {
  if (!ract[rid]) return;
  if (hist.length < rpar[game.turn()][rid][1] * 2) return;
  let ptype = 'p';
  let found = 0;
  for (let i=0; i<rpar[game.turn()][rid][1]; ++i) {
    if (hist[hist.length - 2 - i * 2].piece === ptype) {
      found = 1;
      break;
    }
  }
  if (found) return;
  // Disable all moves except needed
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.piece !== ptype) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNeedQueenMoves(rid) {
  if (!ract[rid]) return;
  if (hist.length < rpar[game.turn()][rid][1] * 2) return;
  let ptype = 'q';
  let found = 0;
  for (let i=0; i<rpar[game.turn()][rid][1]; ++i) {
    if (hist[hist.length - 2 - i * 2].piece === ptype) {
      found = 1;
      break;
    }
  }
  if (found) return;
  // Disable all moves except needed
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.piece !== ptype) DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCreateAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.them()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCreateAttackNotAttacked(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.them()) ||
      move.chess.attackedCnt(game.them(), move.to))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCreateAttackOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.them()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCreateAttackByProtected(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.them()) ||
      move.chess.attackedCnt(game.turn(), move.to) <= rpar[game.turn()][rid][1])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableCreateAttackByProtectedOrCaptureStronger(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Do not disable if taking stronger
    if (tpiece && pvalue[move.piece] < pvalue[tpiece.type]) continue;
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.them()) ||
      move.chess.attackedCnt(game.turn(), move.to) <= rpar[game.turn()][rid][1])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableStockfish(rid) {
  if (!ract[rid]) return;
  let from = engine[game.turn()].best_move[game.history().length].substr(0, 2);
  let to = engine[game.turn()].best_move[game.history().length].substr(2, 2);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.from !== from || move.to !== to)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableStockfishAvailable(rid) {
  if (!ract[rid]) return;
  let best_score = -100000;
  let best_move = '';
  console.log(engine[game.turn()].mpv);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    let score = engine[game.turn()].mpv[move.from + move.to];
    if (typeof score === 'undefined') {
      console.log("Move " + move.from + move.to + " not found");
      score = -100000000 - Math.random() * 100000;
    }
    if (score > best_score) {
      best_score = score;
      best_move = move.from + move.to;
    }
  }
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (best_move !== move.from + move.to)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoves() {
  // First run checks that force moves
  // Then run checks that disable moves
  DisableStockfish(156);
  DisableNoCaptureFromCheck(108);
  DisableMustTakeIfStronger(102);
  DisableCantMoveIfAttacked(104);
  DisableCanMoveOnlyAttacked(105);
  DisableCanMoveOnlyAttackedNoCapture(112);
  DisablePawns(103);
  DisableCantCaptureStronger(106);
  DisablePawnsDoubleMove(107);
  DisableMoveIntoAttack(109);
  DisableCantMoveIntoAttack(124);
  DisableRemoveAttack(110);
  DisableRemoveAttackNoCapture(111);
  DisableMustTake(113);
  DisableMustTakeWeakest(114);
  DisableMustTakeWithStrongest(115);
  DisableMustTakeProtectedIfStronger(116);
  DisableCantCapture(117);
  DisableNoFirstCapture(118);
  DisableNoCaptureUnprotected(119);
  DisableNoCaptureProtectedStronger(120);
  DisableCanTakeOnlyWeakest(121);
  DisableKingNoCaptureFromCheck(122);
  DisableRandomPieceTypes(123);
  DisableRandomMoves(125);
  DisableRandomPieces(126);
  DisableCantCaptureWithCaptured(127);
  DisableCantCaptureStrongerWithCaptured(128);
  DisableCantMultiCapture(129);
  DisableCantMultiCaptureType(130);
  DisableCantMultiCaptureStronger(131);
  DisableCantMultiCaptureTypeStronger(132);
  DisableCantMultiCaptureSame(133);
  DisableCantMultiMoveSame(134);
  DisableCantMultiMoveType(135);
  DisableCantCaptureCapturer(136);
  DisableCantCaptureCapturerStronger(137);
  DisableCantCaptureAfterYourCapture(138);
  DisableCantCaptureStrongerAfterYourCapture(139);
  DisableCaptureOnlyAfterCapture(140);
  DisableNeedMovesEachPieceType(141);
  DisableNeedKingMoves(142);
  DisableNeedPawnMoves(143);
  DisableNeedQueenMoves(144);
  DisableCantMoveIntoAttackButCapture(145);
  DisableCreateAttack(146);
  DisableCreateAttack(147);
  DisableCreateAttackByProtected(148);
  DisableCreateAttackByProtectedOrCaptureStronger(149);
  DisableMustTakeWithPawn(150);
  DisableCreateAttackNotAttacked(154);
  DisableMustTakeUnprotectedOrStronger(151);
  DisableMustTakeStrongest(152);
  DisableMustTakeWithWeakest(153);
  DisableStockfishAvailable(160);
}

function ChooseRules() {
  ract = [];
  rpos[game.turn()].forEach(function(item, i, arr) {
    if (item === 0) return;
    if (Math.random()*100 <= item) ract[i] = 1;
  });
}

function ShowRules() {
  let rst3 = '';
  let rst2 = '';
  let rst1 = '';
  let rst0 = '';
  let hst = '';
  let bst = '';
  let wst = '';
  let rst = '';
  rpos[game.turn()].forEach(function(pos, rid, arr) {
    if (pos === 0) return;
    let st = rname[rid];
    st = st.replace(/XX/g, rpar[game.turn()][rid][0]);
    st = st.replace(/YY/g, rpar[game.turn()][rid][1]);
    st = st.replace(/ZZ/g, rpar[game.turn()][rid][2]);
    let st2 = rdesc[rid];
    st2 = st2.replace(/XX/g, rpar[game.turn()][rid][0]);
    st2 = st2.replace(/YY/g, rpar[game.turn()][rid][1]);
    st2 = st2.replace(/ZZ/g, rpar[game.turn()][rid][2]);
    if (ract[rid] === 3) st2 += "\nThis rule tried to limit moves, but it disabled all possible moves";
    else if (ract[rid] === 2) {
      let rdis_st = "";
      for (let i=0; i<rdis[rid].length; ++i) {
        if (rdis_st !== '') rdis_st += ', ';
        rdis_st += rdis[rid][i].from + '-' + rdis[rid][i].to;
      }
      st2 += "\nThis rule limits moves: " + rdis_st;
    }
    else if (ract[rid] === 1) st2 += "\nThis rule does not currently limit moves";
    else st2 += "\nThis rule is not active due to low possibility";
    fst = '<span title="' + st2 + '">- ' + st + '</span><br>';
    if (ract[rid] === 3) rst3 += fst;
    else if (ract[rid] === 2) rst2 += fst;
    else if (ract[rid] === 1) rst1 += fst;
    else rst0 += fst;
  });
  hst +=
    "<font color=red>" + rst2 + "</font>" +
    "<font color=orange>" + rst1 + "</font>" +
    "<font color=#7777ff>" + rst3 + "</font>" +
    "<font color=green>" + rst0 + "</font>";
  if (game.turn() === 'b') bst = hst;
  else wst = hst;
  rst = "<font color=white>Black rule set: ";
  if (rs_b) {
    rst += "<a target=_blank href=ruleset.php?rs_id=<?=$rs_b?>&act=view><font color=white><?=$rs0['rs_name']?></font></a>";
  }
  else {
    rst += "None";
  }
  rst += "</font><br>";
  bst = rst + bst;
  rst = "White rule set: ";
  if (rs_w) {
    rst += "<a target=_blank href=ruleset.php?rs_id=<?=$rs_w?>&act=view><font color=black><?=$rs1['rs_name']?></font></a>";
  }
  else {
    rst += "None";
  }
  rst += "</font><br>";
  wst = rst + wst;
  if (game.history().length === 0) {
    brulesEl.html(bst);
    wrulesEl.html(wst);
  }
  else {
    if (game.turn() === 'b') brulesEl.html(bst);
    else wrulesEl.html(wst);
  }
}

let updateStatus = function() {
  if (engine[game.turn()]) {
    if (engine[game.turn()].state === 'Wait') {
      stockfish_go();
      return;
    }
  }
  eval_pos();
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
    hist = game.history({verbose: true});
    rdis = [];
    tnum = hist.length;
    if (hist.length > 1) {
      prelast_cap = hist[hist.length - 2].captured;
    }
    else prelast_cap = '';
    if (hist.length > 0) {
      last_cap = hist[hist.length - 1].captured;
    }
    else last_cap = '';
    for (let i=0; i<posMoves.length; ++i) {
      posMoves[i].disabled = 0;
      posMoves[i].chess = new Chess(game.fen());
      posMoves[i].chess.move(posMoves[i]);
    }
    ChooseRules();
    DisableMoves();
    RemoveDisabledMoves();
    ShowRules();
    HighlightPosMoves();
    window.setTimeout(function() {AutoMove(game.turn())}, 500);
  }

  statusEl.html(status);
  fenEl.html(game.fen());
  let mypgn = game.pgn();
  mypgn = mypgn.replace(/ ([0-9])/g,"<br>$1");
  //pgnEl.attr('data-content', mypgn);
  //pgnEl.popover('show');
  pgnEl.html(mypgn);
  let pgnSel = $('#pgn');
  pgnSel.scrollTop(pgnSel[0].scrollHeight);
  pgnEl.scrollTop = pgnEl.scrollHeight - pgnEl.clientHeight;
  bcapturesEl.html("<b>Black captures balance: " + (CapturesValue('b') - CapturesValue('w')));
  wcapturesEl.html("<b>White captures balance: " + (CapturesValue('w') - CapturesValue('b')));
};

function RemoveDisabledMoves() {
  posMoves2 = [];
  for (let i=0; i<posMoves.length; ++i) {
    if (posMoves[i].disabled === 0) {
      posMoves2.push(posMoves[i]);
    }
  }
}

function AutoMove(color) {
  if (countObjectsByKey(posMoves2, 'disabled', 0) === 1 ||
    (ract[101] && game.history().length > rpar[color][101][0] * 2)) {
    RandomMove();
  }
}

function RandomMove() {
  let randomIndex = Math.floor(Math.random() * posMoves2.length);
  MakeMove(posMoves2[randomIndex]);
  board.position(game.fen());
  removeGreySquares();
  updateStatus();
}

// Highlight possible moves
function HighlightPosMoves() {
  boardEl.find('.highlight-red').removeClass('highlight-red');
  boardEl.find('.highlight-green').removeClass('highlight-green');
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

function ShowRating(rating) {
  let canvas = document.getElementById("rating_indicator");
  let ctx = canvas.getContext("2d");
  let pos = rating * canvas.height / 3000 + canvas.height / 2;
  if (pos > canvas.height - 1) pos = canvas.height - 1;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillRect(0, 0, 5, canvas.height - pos);
  ctx.beginPath();
  ctx.moveTo(0, canvas.height / 2);
  ctx.lineTo(5, canvas.height / 2);
  ctx.lineWidth = 4;
  ctx.strokeStyle = '#ff0000';
  ctx.stroke();
  canvas.title = rating / 100;
}

function ShowHint() {
  boardEl.find('.highlight-red').removeClass('highlight-red');
  boardEl.find('.highlight-green').removeClass('highlight-green');
  let from = eval_best_move[game.history().length].substr(0, 2);
  let to = eval_best_move[game.history().length].substr(2, 2);
  boardEl.find('.square-' + from).addClass('highlight-green');
  boardEl.find('.square-' + to).addClass('highlight-green');
  window.setTimeout(HighlightPosMoves, 1500);
}

function init_evaler() {
  evaler = load_engine();
  evaler.send("uci");
  evaler.set_level(20);
  //evaler.send("setoption name MultiPV value 200");
}

function init_engine(color) {
  let level;
  let depth;
  if (rpos[color][156]) depth = rpar[color][156][1];
  if (rpos[color][157]) depth = rpar[color][157][1];
  if (rpos[color][158]) depth = rpar[color][158][1];
  if (rpos[color][159]) depth = rpar[color][159][1];
  if (rpos[color][160]) depth = rpar[color][160][1];
  if (!depth) return;
  engine[color] = load_engine();
  engine[color].send("uci");
  engine[color].depth = depth;
  if (rpos[color][156]) {
    level = rpar[color][156][2];
  }
  else {
    level = 20;
  }
  engine[color].set_level(level);
  if (rpos[color][157] || rpos[color][159] || rpos[color][160]) {
    engine[color].send("setoption name MultiPV value 200");
  }
}

//init_evaler();
init_engine('b');
init_engine('w');
updateStatus();
</script>

<?php
//stop_time();
if ($view != "mobile") {
  require_once "template/footer.php";
}
?>