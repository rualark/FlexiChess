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
$rsb = mysqli_fetch_assoc($r);
// Load ruleset
$r = mysqli_query($ml,
  "SELECT * FROM rulesets
    LEFT JOIN users USING (u_id) 
    WHERE rs_id='$rs_w'");
echo mysqli_error($ml);
$rsw = mysqli_fetch_assoc($r);

if ($show_mobile) {
  $rule_height = 100;
  $rule_width = 600;
  $pgn_height = 50;
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
  $pgn_height = 100;
  $board_width = 600;
}
$board_width_padded = $board_width - 5;

echo "<link rel='stylesheet' href='chessboardjs/css/chessboard-0.3.0.min.css'>\n";
echo "<link rel='stylesheet' href='css/play.css'>\n";
echo "<script src='chessboardjs/js/chessboard-0.3.0.min.js'></script>\n";
echo "<script src='chessboardjs/js/chess.js'></script>\n";
echo "<script src='js/lib.js'></script>\n";
echo "<script src='js/simple-chess-ai.js'></script>\n";
echo "<script language='JavaScript' type='text/javascript' src='plugin/notify.min.js'></script>";
echo "<table>";
echo "<tr>";
echo "<td valign='top'>";
echo "<canvas id='rating_indicator' width=5 height={$board_width_padded}></canvas>";
echo "<td valign='top'>";
echo "<table><tr><td>";
echo "<div id='board' style='width: {$board_width}px'></div>\n";
echo "<tr><td>";
echo "<canvas style='display: block;' id='progress' width={$board_width_padded} height=4></canvas>";
echo "</table>";
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
echo "<div style='line-height: 1; width: {$rule_width}px; height: {$pgn_height}px; overflow-y: scroll; border:1px solid black' id=pgn></div>";
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
  if (engine_eval.state === 'Running' ||
    engine_ana.state === 'Running' ||
    engine['b'] === 'Running' ||
    engine['w'] === 'Running'
  ) {
    return false;
  }
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

  let chess = new Chess(game.fen());
  let move = {
    from: source,
    to: target,
    promotion: 'q' // NOTE: always promote to a queen for example simplicity
  };
  // see if the move is legal
  if (chess.move(move) === null) return 'snapback';

  MakeMove(move, 0);
};

// update the board position after the piece snap
// for castling, en passant, pawn promotion
let onSnapEnd = function() {
  board.position(game.fen());
};

function eval_pos(turn, color) {
  if (!engine_eval) return;
  engine_eval.state = 'Running';
  eval_turn = turn;
  eval_color = color;
  eval_chess = new Chess(game.fen());
  if (debugging) console.log("Called eval_pos with: ", eval_turn, eval_color);
  eval_best_move.length = eval_turn + 1;
  eval_best_score.length = eval_turn + 1;
  eval_afterbest_score.length = eval_turn;
  engine_eval.send("stop");
  engine_eval.send("position fen " + game.fen());
  engine_eval.send("go depth " + eval_depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      eval_chess.move({from: matches[1].substr(0, 2), to: matches[1].substr(2, 2), promotion: 'q'});
      eval_best_move[eval_turn] = eval_chess.history({ verbose: true })[eval_chess.history().length - 1];
      eval_ponder[eval_turn] = matches[2];
      if (debugging) console.log("Score eval: ", eval_afterbest_score, eval_best_score, eval_best_move, game.history(), eval_turn)
      engine_eval.state = 'Wait';
    }
  }, function stream(str)
  {
    if (debugging) console.log("Eval: " + str);
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

      // Convert the relative score to an absolute score.
      if (eval_color === "b") {
        score *= -1;
      }
      eval_score_st = (score>0 ? "+" : "") + Math.round(score / 10) / 10;
      if (type === "mate") {
        eval_score_st = "mate in " + Math.abs(score);
        score = 100000 * score;
      }

      eval_cur_depth = depth;
      ShowProgress();
      ShowRating(score);
      //if (depth != engine_eval.depth) return;
      eval_cur_depth = depth;
      eval_best_score[eval_turn] = score;
      ShowPgn();
      ShowStatus();
    }
  });
}

function analyse_move(move, turn, color) {
  if (!engine_ana) return;
  engine_ana.state = 'Running';
  if (debugging) console.log("Called analyse_move with: ", move, turn, color);
  ana_turn = turn;
  ana_color = color;
  ana_chess = new Chess(game.fen());
  eval_afterbest_score.length = ana_turn;
  let result = ana_chess.move(move);
  engine_ana.send("stop");
  engine_ana.send("position fen " + ana_chess.fen());
  engine_ana.send("go depth " + eval_depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      engine_ana.state = 'Wait';
    }
  }, function stream(str)
  {
    if (debugging) console.log("Ana: " + str);
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
      if (ana_color === "w") {
        score *= -1;
      }

      //if (depth != engine_ana.depth) return;
      ana_cur_depth = depth;
      ShowProgress();
      ana_cur_depth = depth;
      eval_afterbest_score[ana_turn] = score;
      if (debugging) console.log("Score: ", eval_afterbest_score, eval_best_score, eval_best_move, game.history(), ana_turn);
      ShowPgn();
    }
  });
}

function stockfish_go(color) {
  if (!engine[game.turn()]) return;
  engine[game.turn()].state = 'Running';
  engine[game.turn()].send("position fen " + game.fen());
  engine[game.turn()].mpv = [];
  engine[game.turn()].mpv2 = [];
  engine[game.turn()].send("go depth " + engine[game.turn()].depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      engine[game.turn()].best_move[game.history().length] = matches[1];
      engine[game.turn()].ponder[game.history().length] = matches[2];
      updateStatus();
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
    engine[game.turn()].cur_depth = depth;
    ShowProgress();
    // Wrong depth
    if (depth != engine[game.turn()].depth) return;
    type = matches[2];
    score = Number(matches[3]);
    pv = matches[4].split(" ");
    if (type === "mate") {
      score = 100000 * score;
    }
    engine[game.turn()].mpv[pv[0]] = score;
    engine[game.turn()].mpv2.push({score: score, move: pv[0]});
  });
}

function MakeMove(move, updateBoard) {
  if (engine_eval.state === 'Running' ||
    engine_ana.state === 'Running' ||
    engine['b'] === 'Running' ||
    engine['w'] === 'Running'
  ) {
    if (debugging) console.log("Move " + move.to + " is waiting until stockfish finishes");
    window.setTimeout(function () {
      MakeMove(move, updateBoard)
    }, 100);
    return;
  }
  analyse_move(eval_best_move[game.history().length], game.history().length, game.turn());
  let result = game.move(move);
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
  if (updateBoard) board.position(game.fen());
  removeGreySquares();
  updateStatus();
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

function pValueSum(color) {
  let totalEvaluation = 0;
  for (let i = 0; i <= 119; i++) {
    /* did we run off the end of the board */
    if (i & 0x88) { i += 7; continue; }
    if (typeof game.board[i] !== 'undefined' && game.board[i] != null) {
      let pv = pvalue[game.board[i].type];
      if (game.board[i].color === color) {
        totalEvaluation += pv;
        console.log("Evaluated", game.board[i], pv);
      }
    }
  }
  return totalEvaluation;
}

function Undo() {
  if (engine_eval.state === 'Running' ||
    engine_ana.state === 'Running' ||
    engine['b'] === 'Running' ||
    engine['w'] === 'Running'
  ) {
    if (debugging) console.log("Undo is waiting until stockfish finishes");
    window.setTimeout(Undo, 100);
    return;
  }
  let result = game.undo();
  board.position(game.fen());
  updateStatus();
  return;
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
      if (hist[hist.length - 2 - i * 2].piece === ptypes[pt_id]) {
        found = 1;
        break;
      }
    }
    if (found) continue;
    ptneed.push(ptypes[pt_id]);
  }
  // No needed piece types
  if (!ptneed.length) return;
  // Disable all moves except needed
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let found = 0;
    for (let pt_id in ptneed) {
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

function DisableNotBestStockfish(rid) {
  if (!ract[rid]) return;
  let from = engine[game.turn()].best_move[game.history().length].substr(0, 2);
  let to = engine[game.turn()].best_move[game.history().length].substr(2, 2);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.from === from && move.to === to)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNotBestStockfishNoBlunder(rid) {
  if (!ract[rid]) return;
  if (debugging) console.log(engine[game.turn()].mpv);
  let best_score = engine[game.turn()].mpv2[0].score;
  // At least one move is needed above margin_score
  let margin_score = best_score - rpar[game.turn()][rid][2];
  // This is last move which will be allowed
  let last_score = engine[game.turn()].mpv2[0];
  // Find worst move, which is still not worse than best - rpar
  for (let i=1; i<engine[game.turn()].mpv2.length; ++i) {
    if (engine[game.turn()].mpv2[i].score > margin_score)
      last_score = engine[game.turn()].mpv2[i];
  }
  if (debugging) console.log(last_score);
  // Disable all moves above margin_score
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    let score = engine[game.turn()].mpv[move.from + move.to];
    if (typeof score === 'undefined') {
    }
    else {
      if (score > last_score.score)
        DisableMove(i);
    }
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
  if (debugging) console.log(engine[game.turn()].mpv);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    let score = engine[game.turn()].mpv[move.from + move.to];
    if (typeof score === 'undefined') {
      if (debugging) console.log("Move " + move.from + move.to + " not found");
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

// Simple chess AI
function DisableSCA(rid) {
  if (!ract[rid]) return;
  sca_eval_effect = 1;
  let best_move = minimaxRoot(rpar[game.turn()][rid][1], game, true);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.san !== best_move)
      DisableMove(i);
  }
  ValidateRule(rid);
}

// Simple chess AI with negative position effect
function DisableSCANegativeEffect(rid) {
  if (!ract[rid]) return;
  sca_eval_effect = -1;
  let best_move = minimaxRoot(rpar[game.turn()][rid][1], game, true);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.san !== best_move)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoves() {
  // First run checks that force moves
  // Then run checks that disable moves
  DisableNotBestStockfish(158);
  DisableSCA(161);
  DisableSCANegativeEffect(162);
  DisableStockfish(156);
  DisableNotBestStockfishNoBlunder(159);
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
    rst += "<a target=_blank href=ruleset.php?rs_id=<?=$rs_b?>&act=view><font color=white><?=$rsb['rs_name']?></font></a>";
  }
  else {
    rst += "None";
  }
  rst += "</font><br>";
  bst = rst + bst;
  rst = "White rule set: ";
  if (rs_w) {
    rst += "<a target=_blank href=ruleset.php?rs_id=<?=$rs_w?>&act=view><font color=black><?=$rsw['rs_name']?></font></a>";
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
      stockfish_go(game.turn());
      return;
    }
  }
  eval_pos(game.history().length, game.turn());

  let moveColor = 'White';
  if (game.turn() === 'b') {
    moveColor = 'Black';
  }

  // checkmate?
  if (game.in_checkmate() === true) {
    game_status = 'Game over, ' + moveColor + ' is in checkmate.';
  }

  // draw?
  else if (game.in_draw() === true) {
    game_status = 'Game over, drawn position';
  }

  // game still on
  else {
    game_status = moveColor + ' to move';
    // check?
    if (game.in_check() === true) {
      game_status += ', ' + moveColor + ' is in check';
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

  ShowStatus();
  fenEl.html(game.fen());
  //pgnEl.attr('data-content', mypgn);
  //pgnEl.popover('show');
  ShowPgn();
  bcapturesEl.html("<b>Black material balance: " + (pValueSum('b') - pValueSum('w')));
  wcapturesEl.html("<b>White material balance: " + (pValueSum('w') - pValueSum('b')));
};

function ShowStatus() {
  let st = game_status;
  if (typeof eval_score_st != 'undefined' && eval_score_st != '')
    st += " (" + eval_score_st + ")";
  statusEl.html(st);
}

function GetMoveHtml(i, hist) {
  let st = "";
  let hcolor;
  let comment;
  let move_type = 0;
  let delta = 0;
  let sign = 1;
  if (i % 2 === 1) sign = -1;
  if (typeof eval_afterbest_score[i] !== 'undefined' &&
    typeof eval_best_score[i + 1] !== 'undefined') {
    delta = sign * (eval_afterbest_score[i] - eval_best_score[i + 1]);
    if (delta > 300) move_type = 4;
    else if (delta > 100) move_type = 3;
    else if (delta > 50) move_type = 2;
    else move_type = 1;
  }
  if (move_type === 4) {
    hcolor="#ff5555";
    comment = "Blunder (" + eval_best_score[i + 1] + "). Best move was " + eval_best_move[i].san + " (" + eval_afterbest_score[i] + ")";
  }
  else if (move_type === 3) {
    hcolor="#ffbb55";
    comment = "Mistake (" + eval_best_score[i + 1] + "). Best move was " + eval_best_move[i].san + " (" + eval_afterbest_score[i] + ")";
  }
  else if (move_type === 2) {
    hcolor="#ffff00";
    comment = "Inaccuracy (" + eval_best_score[i + 1] + "). Best move was " + eval_best_move[i].san + " (" + eval_afterbest_score[i] + ")";
  }
  else if (move_type === 1) {
    hcolor="#99ff99";
    if (eval_best_move[i].san == hist[i].san) {
      comment = "Best move";
    }
    else {
      comment = "Good move (" + eval_best_score[i + 1] + "). Best move was " + eval_best_move[i].san + " (" + eval_afterbest_score[i] + ")";
    }
  }
  else if (move_type === 0) {
    hcolor="#ffffff";
    comment = '';
  }
  st += "<td title='" + comment + "' bgcolor=" + hcolor + ">&nbsp;";
  st += hist[i].san;
  st += "&nbsp;";
  return st;
}

function ShowPgn() {
  let mypgn = "";
  let hist = game.history({ verbose: true });
  let turn = hist.length;
  mypgn = "<table>";
  for (let i=0; i<turn; ++i) {
    if (i % 2 === 1) continue;
    if (typeof eval_best_move[i] === 'undefined') continue;
    mypgn += "<tr><td>" + Math.floor((i / 2) + 1) + ".";
    mypgn += GetMoveHtml(i, hist);
    if (i<turn - 1) {
      mypgn += GetMoveHtml(i + 1, hist);
    }
    else mypgn += "<td>";
    mypgn += "<td>&nbsp; best: &nbsp;";
    mypgn += "<td>&nbsp;" + eval_best_move[i].san + "&nbsp;";
    if (i<turn - 1) {
      mypgn += "<td>&nbsp;" + eval_best_move[i + 1].san + "&nbsp;";
    }
  }
  mypgn += "</table>";
  pgnEl.html(mypgn);
  // Scroll to bottom
  let pgnSel = $('#pgn');
  pgnSel.scrollTop(pgnSel[0].scrollHeight);
}

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
  MakeMove(posMoves2[randomIndex], 1);
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
  canvas.title = eval_score_st;
}

function ShowProgress() {
  let canvas = document.getElementById("progress");
  let ctx = canvas.getContext("2d");
  let pos1 = canvas.width * eval_cur_depth / engine_eval.depth;
  let pos2 = canvas.width * ana_cur_depth / engine_ana.depth;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#999999";
  ctx.fillRect(0, 0, pos1, 1);
  ctx.fillRect(0, 1, pos2, 2);
  if (engine['b']) {
    let pos3 = canvas.width * engine['b'].cur_depth / engine['b'].depth;
    //ctx.fillStyle = "#ff0000";
    ctx.fillRect(0, 2, pos3, 3);
  }
  if (engine['w']) {
    let pos3 = canvas.width * engine['w'].cur_depth / engine['w'].depth;
    //ctx.fillStyle = "#00ff00";
    ctx.fillRect(0, 3, pos3, 4);
  }
}

function ShowHint() {
  if (typeof eval_best_move[game.history().length] === 'undefined') return;
  boardEl.find('.highlight-red').removeClass('highlight-red');
  boardEl.find('.highlight-green').removeClass('highlight-green');
  let from = eval_best_move[game.history().length].from;
  let to = eval_best_move[game.history().length].to;
  boardEl.find('.square-' + from).addClass('highlight-green');
  boardEl.find('.square-' + to).addClass('highlight-green');
  window.setTimeout(HighlightPosMoves, 1500);
}

function init_engine_eval() {
  engine_eval = load_engine("Eval");
  engine_eval.depth = eval_depth;
  engine_eval.send("uci");
  engine_eval.set_level(20);
  //engine_eval.send("setoption name MultiPV value 200");
}

function init_engine_ana() {
  engine_ana = load_engine("Ana");
  engine_ana.depth = eval_depth;
  engine_ana.send("uci");
  engine_ana.set_level(20);
  //engine_eval.send("setoption name MultiPV value 200");
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
  engine[color] = load_engine(color);
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

init_engine_eval();
init_engine_ana();
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