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
$move_color = secure_variable("move_color");
if ($move_color == "") $move_color = "w";
$fen = secure_variable("fen");
if ($fen == "") $fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";

if ($view == "mobile" || ($view == "" && $mobile_detect->isMobile())) $show_mobile = 1;

if ($rs_b == '') $rs_b = 0;
if ($rs_w == '') $rs_w = 0;

$title = "$site_name: Play";

login();
if (!$uid) {
  $ua = array();
  $ua['u_depth'] = 12;
  $ua['u_bestmoves'] = 1;
  $ua['u_hint'] = 1;
  $ua['u_score'] = 1;
  $ua['u_undo'] = 1;
}

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
  <link rel="stylesheet" href="plugin/bootstrap-4.0.0/bootstrap.min.css">

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
if ($ua['u_undo'])
  echo "<button onclick=\"Undo();\">Undo</button>\n";
echo "<button onclick=\"RandomMove();\">Random</button>\n";
if ($ua['u_hint']) echo "<button onclick=\"ShowHint();\">Hint</button>\n";
if ($show_mobile) {
  echo "<form style='display:inline;' role=search method=get action='' target=_blank>";
  echo "<input type='hidden' name='rs_b' value='$rs_b'>";
  echo "<input type='hidden' name='rs_w' value='$rs_w'>";
  echo "<input type='hidden' name='view' value='desktop'>";
  echo "<input type=submit value='Desktop view'></form>\n";
  echo "<form style='display:inline;' role=search method=get action='rulesets.php' target=_blank>";
  echo "<input type=submit value='Exit'></form>\n";
  echo "<button onclick=\"openAnalysis();\">Analysis</button>\n";
  echo " <span id=status></span>";
}
else {
  echo "<form style='display:inline;' role=search method=get action='' target=_blank>";
  echo "<input type='hidden' name='rs_b' value='$rs_b'>";
  echo "<input type='hidden' name='rs_w' value='$rs_w'>";
  echo "<input type='hidden' name='view' value='mobile'>";
  echo "<input type=submit value='Mobile view'></form>\n";
  echo "<button onclick=\"openAnalysis();\">Analysis</button>\n";
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
  <script language='JavaScript' type='text/javascript' src='js/lib.js'></script>
  <script language='JavaScript' type='text/javascript' src='js/stockfish_engine.js'></script>
  <script language='JavaScript' type='text/javascript' src='js/rules.js'></script>
<script>
  let game = new Chess('<?=$fen?>');

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
    send_js_var("rdif['b'][$rid]", get_difficulty($rid,
      $rpos['b'][$rid], $rpar['b'][$rid][0], $rpar['b'][$rid][1], $rpar['b'][$rid][2]));
  }
  if ($rpos['w'][$rid]) {
    send_js_var("rpos['w'][$rid]", $rpos['w'][$rid]);
    send_js_var("rpar['w'][$rid][0]", $rpar['w'][$rid][0]);
    send_js_var("rpar['w'][$rid][1]", $rpar['w'][$rid][1]);
    send_js_var("rpar['w'][$rid][2]", $rpar['w'][$rid][2]);
    send_js_var("rdif['w'][$rid]", get_difficulty($rid,
      $rpos['w'][$rid], $rpar['w'][$rid][0], $rpar['w'][$rid][1], $rpar['w'][$rid][2]));
  }
}

echo "rs_b = $rs_b;\n";
echo "rs_w = $rs_w;\n";
echo "eval_depth = $ua[u_depth];";
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
  if (game.game_over()) return;

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
    (typeof engine['b'] !== 'undefined' && engine['b'].state === 'Running') ||
    (typeof engine['w'] !== 'undefined' && engine['w'].state === 'Running')
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

function openAnalysis() {
  if (!game_id) return;
  window.open('game.php?g_id=' + game_id, '_blank');
}

function eval_pos(turn, color) {
  if (!engine_eval) return;
  engine_eval.state = 'Running';
  eval_turn = turn;
  eval_color = color;
  eval_chess = new Chess(game.fen());
  if (debugging) console.log("Called eval_pos with: ", eval_turn, eval_color);
  eval_best_move.length = eval_turn + 1;
  eval_score.length = eval_turn + 1;
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
      if (debugging) console.log("Score eval: ", eval_afterbest_score, eval_score, eval_best_move, game.history(), eval_turn)
      engine_eval.state = 'Wait';
      ShowPgn();
      ShowStatus();
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
      if (eval_color === "b") score *= -1;
      eval_score[eval_turn] = build_score(type, score);
      eval_score_st[eval_turn] = build_score_st(type, score);
      eval_cur_depth = depth;
      ShowRating(eval_score[eval_turn], eval_score_st[eval_turn]);
      ShowProgress();
    }
    else {
      matches = str.match(/depth 0 .*score (cp|mate) ([-\d]+)/);
      if (matches) {
        type = matches[1];
        score = Number(matches[2]);
        if (eval_color === "b") score *= -1;
        eval_score[eval_turn] = build_score(type, score);
        eval_score_st[eval_turn] = build_score_st(type, score);
        eval_cur_depth = depth;
        ShowProgress();
      }
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
      if (ana_color === "w") score *= -1;
      eval_afterbest_score[ana_turn] = build_score(type, score);
      eval_afterscore_st[ana_turn] = build_score_st(type, score);
      eval_afterbest_path[ana_turn] = matches[4].replace(/bmc.*/, '');
      ana_cur_depth = depth;
      ShowProgress();
      ShowPgn();
      if (debugging) {
        if (depth != engine_ana.depth) return;
        console.log("Score: ", eval_afterbest_score, eval_score, eval_best_move, game.history(), ana_turn);
      }
    }
    else {
      matches = str.match(/depth 0 .*score (cp|mate) ([-\d]+)/);
      if (matches) {
        type = matches[1];
        score = Number(matches[2]);
        if (ana_color === "b") score *= -1;
        eval_afterbest_score[ana_turn] = build_score(type, score);
        eval_afterscore_st[ana_turn] = build_score_st(type, score);
        eval_afterbest_path[ana_turn] = '';
        ana_cur_depth = depth;
        ShowProgress();
      }
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
    if (debugging) console.log(color + ": " + str);
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
    console.log("Score: ", type, score);
    pv = matches[4].split(" ");
    score = build_score(type, score);
    console.log("Score: ", type, score);
    engine[game.turn()].mpv[pv[0]] = score;
    engine[game.turn()].mpv2.push({score: score, move: pv[0]});
  });
}

function MakeMove(move, updateBoard) {
  if (debugging) console.log("Making move: ", move, updateBoard);
  if (engine_eval.state === 'Running' ||
    engine_ana.state === 'Running' ||
    (typeof engine['b'] !== 'undefined' && engine['b'].state === 'Running') ||
    (typeof engine['w'] !== 'undefined' && engine['w'].state === 'Running')
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
      rs_b: '<?=$rs_b?>',
      rs_w: '<?=$rs_w?>',
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
    let piece = game.geti(i);
    if (piece != null) {
      let pv = pvalue[piece.type];
      if (piece.color === color) {
        totalEvaluation += pv;
      }
    }
  }
  return totalEvaluation;
}

function Undo() {
  if (engine_eval.state === 'Running' ||
    engine_ana.state === 'Running' ||
    (typeof engine['b'] !== 'undefined' && engine['b'].state === 'Running') ||
    (typeof engine['w'] !== 'undefined' && engine['w'].state === 'Running')
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
  console.log("Find: ", findObjectByKey(posMoves, 'disabled', 0));
  // Revert rules that give no possible moves
  if (findObjectByKey(posMoves, 'disabled', 0) === null) {
    RevertRule();
    ract[rid] = 3;
  }
  // Apply other rules
  else {
    rdis[rid] = [];
    rall[rid] = [];
    for (let i=0; i<posMoves.length; ++i) {
      if (posMoves[i].disabled === 1) {
        ract[rid] = 2;
        posMoves[i].disabled = 2;
        rdis[rid].push(posMoves[i]);
      }
      else if (posMoves[i].disabled === 0) {
        rall[rid].push(posMoves[i]);
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

function ChooseRules() {
  ract = [];
  rpos[game.turn()].forEach(function(item, i, arr) {
    if (item === 0) return;
    if (Math.random()*100 <= item) ract[i] = 1;
  });
  // Check if rules should be disabled
  let no_limits = 0;
  let sign = 1;
  if (game.turn() === 'b') sign = -1;
  if (ract[167] && sign * eval_score[game.history().length - 1] < rpar[game.turn()][167][1] * 100) {
    ract[167] = 2;
    no_limits = 1;
  }
  if (ract[168] && pvsum[game.turn()] - pvsum[game.them()] < rpar[game.turn()][168][1]) {
    ract[168] = 2;
    no_limits = 1;
  }
  if (ract[193]) {
    for (let i=0; i<rpar[game.turn()][193][1]; ++i) {
      if (hist.length <= i * 2) break;
      //console.log("Checking hist ", i, hist.length - 1 - i, hist[hist.length - 1 - i]);
      if (hist[hist.length - 1 - i * 2].captured) {
        ract[193] = 2;
        no_limits = 1;
        break;
      }
    }
  }
  if (ract[194]) {
    for (let i=0; i<rpar[game.turn()][194][1]; ++i) {
      if (hist.length <= i * 2 + 1) break;
      //console.log("Checking hist ", i, hist.length - 1 - i, hist[hist.length - 1 - i]);
      if (hist[hist.length - 2 - i * 2].captured) {
        ract[194] = 2;
        no_limits = 1;
        break;
      }
    }
  }
  if (no_limits) {
    ract.forEach(function(item, i, arr) {
      console.log("Disabling rule: ", i, rdif[game.turn()][i]);
      if (rdif[game.turn()][i] > 0)
        ract[i] = 0;
    });
  }
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
      if (typeof rdis[rid] !== 'undefined') {
        let rdis_st = "";
        for (let i = 0; i < rdis[rid].length; ++i) {
          if (rdis_st !== '') rdis_st += ', ';
          rdis_st += rdis[rid][i].from + '-' + rdis[rid][i].to;
        }
        st2 += "\nThis rule limits moves: " + rdis_st;
      }
      if (typeof rall[rid] !== 'undefined') {
        let rall_st = "";
        for (let i = 0; i < rall[rid].length; ++i) {
          if (rall_st !== '') rall_st += ', ';
          rall_st += rall[rid][i].from + '-' + rall[rid][i].to;
        }
        st2 += "\nThis rule allowed moves: " + rall_st;
      }
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
  pvsum['b'] = pValueSum('b');
  pvsum['w'] = pValueSum('w');

  hist = game.history({verbose: true});
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
      if (typeof posMoves[i].promotion === 'undefined') posMoves[i].promotion = '';
      posMoves[i].disabled = 0;
      posMoves[i].chess = new Chess(game.fen());
      posMoves[i].chess.move(posMoves[i]);
    }
    ChooseRules();
    DisableMoves();
    RemoveDisabledMoves();
    ShowRules();
    window.setTimeout(function() {AutoMove(game.turn())}, 500);
  }

  HighlightPosMoves();
  ShowStatus();
  fenEl.html(game.fen());
  //pgnEl.attr('data-content', mypgn);
  //pgnEl.popover('show');
  ShowPgn();
  bcapturesEl.html("<b>Black material balance: " + (pvsum['b'] - pvsum['w']));
  wcapturesEl.html("<b>White material balance: " + (pvsum['w'] - pvsum['b']));
};

function ShowStatus() {
  let st = game_status;
  let est = eval_score_st[game.history().length];
  if (typeof est != 'undefined' && est != '') {
    <? if ($ua['u_score']) echo "st += ' (' + est + ')';"; ?>
  }
  statusEl.html(st);
}

function GetMoveHtml(i, hist) {
  build_move_analysis(i, hist[i], eval_best_move[i],
    eval_score[i + 1], eval_afterbest_score[i],
    eval_score_st[i + 1], eval_afterscore_st[i],
    eval_afterbest_path[i]
  );
  let st2 = "<td data-html=true data-toggle=popover title='Move " + hist[i].san +
    " analysis' data-content='<table cellpadding=4><tr><td bgcolor=" + move_hcolor + ">" +
    move_comment2 + "' bgcolor=" + move_hcolor + ">";
  let st = "";
  <? if ($ua['u_bestmoves']) echo "st += st2;" ?>
  st += "&nbsp;";
  st += hist[i].san;
  st += "&nbsp;";
  return st;
}

function ShowPgn() {
  let mypgn = "";
  let hist = game.history({ verbose: true });
  let turn = hist.length;
  mypgn = "<table>";
  let popoverEls;
  popoverEls = $('[data-toggle="popover"]');
  if (popoverEls.length) {
    popoverEls.popover('hide');
  }
  for (let i=0; i<turn; ++i) {
    if (i % 2 === 1) continue;
    if (typeof eval_best_move[i] === 'undefined') continue;
    mypgn += "<tr><td>" + Math.floor((i / 2) + 1) + ".";
    mypgn += GetMoveHtml(i, hist);
    if (i<turn - 1) {
      mypgn += GetMoveHtml(i + 1, hist);
    }
    else mypgn += "<td>";
    let mypgn2 = '';
    mypgn2 += "<td>&nbsp; best: &nbsp;";
    mypgn2 += "<td>&nbsp;" + eval_best_move[i].san + "&nbsp;";
    if (i<turn - 1) {
      mypgn2 += "<td>&nbsp;" + eval_best_move[i + 1].san + "&nbsp;";
    }
    <? if ($ua['u_bestmoves']) echo "mypgn += mypgn2;" ?>
  }
  mypgn += "</table>";
  pgnEl.html(mypgn);
  // Scroll to bottom
  let pgnSel = $('#pgn');
  pgnSel.scrollTop(pgnSel[0].scrollHeight);
  popoverEls = $('[data-toggle="popover"]');
  if (popoverEls.length) {
    popoverEls.popover();
    popoverEls.on('click', function (e) {
      popoverEls.not(this).popover('hide');
    });
    $('.popover-dismiss').popover({
      trigger: 'focus'
    });
  }
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
  //console.log("Trying AutoMove", countObjectsByKey(posMoves2, 'disabled', 0), ract[101], game.history().length, rpar[color][101][0] * 2);
  if (countObjectsByKey(posMoves2, 'disabled', 0) === 1 ||
    (ract[101] && game.history().length <= rpar[color][101][0] * 2)) {
    RandomMove();
  }
}

function RandomMove() {
  //console.log("Trying RandomMove");
  let randomIndex = Math.floor(Math.random() * posMoves2.length);
  MakeMove(posMoves2[randomIndex], 1);
}

// Highlight possible moves
function HighlightPosMoves() {
  console.log("Clearing all highlights");
  boardEl.find('.highlight-red').removeClass('highlight-red');
  boardEl.find('.highlight-green').removeClass('highlight-green');
  boardEl.find('.highlight-yellow').removeClass('highlight-yellow');
  if (hist.length > 0) {
    boardEl.find('.square-' + hist[hist.length - 1].to).addClass('highlight-yellow');
    boardEl.find('.square-' + hist[hist.length - 1].from).addClass('highlight-yellow');
  }
  if (game.game_over()) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    boardEl.find('.square-' + move.from).addClass('highlight-red');
  }
}

let cfg = {
  draggable: true,
  position: game.fen(),
  onDragStart: onDragStart,
  onDrop: onDrop,
  onMouseoutSquare: onMouseoutSquare,
  onMouseoverSquare: onMouseoverSquare,
  onSnapEnd: onSnapEnd
};
board = ChessBoard('board', cfg);

function ShowRating(rating, st) {
  <? if (!$ua['u_score']) echo "return;" ?>
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
  canvas.title = st;
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
  canvas.title = 'Evaluation depth: ' + eval_depth;
}

function ShowHint() {
  <? if (!$ua['u_hint']) echo "return;" ?>
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