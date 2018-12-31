<?php
require_once "lib/config.php";
require_once "lib/auth.php";
require_once "lib/lib.php";
require_once "lib/clib.php";

$title = "$site_name: New game";

$g_id = secure_variable("g_id");
$rule_width = 400;
$pgn_height = 520;
$board_width = 600;
$board_width_padded = $board_width - 5;

login();

include "template/menu.php";
echo "<div class=container>";
echo "<br>";

load_rules();

$r = mysqli_query($ml, "SELECT 
    games.g_id, games.time_started, games.time_changed, rsb.rs_name AS rsb_name, rsw.rs_name AS rsw_name, 
    users.u_name, rs_b, rs_w, fen, pgn
    FROM games
    LEFT JOIN users USING (u_id)
    LEFT JOIN rulesets AS rsb ON (games.rs_b=rsb.rs_id) 
    LEFT JOIN rulesets AS rsw ON (games.rs_w=rsw.rs_id) 
    WHERE g_id='$g_id'");
echo mysqli_error($ml);
$w = mysqli_fetch_assoc($r);

$pgn = str_replace("\n", "\\n", $w['pgn']);
$pgn = str_replace("\r", "", $pgn);
// Detect move color
$move_color = 'w';
$sa = explode(" ", $pgn);
for ($i = count($sa) - 1; $i>=0; --$i) {
  if (is_numeric($sa[$i][0])) {
    if ($i == count($sa) - 2) $move_color = 'b';
  }
}

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
echo "<canvas style='display: block;' id='progress' width={$board_width_padded} height=2></canvas>";
echo "</table>";
echo "<td valign='top'>";
echo "<span id=status></span><br>";

echo "<div style='width: 400px' id=bcaptures></div>";
echo "<div style='line-height: 1; width: {$rule_width}px; height: {$pgn_height}px; overflow-y: scroll; border:1px solid black' id=pgn></div>";
echo "<div style='width: 400px; ' id=wcaptures></div><br>";
echo "</table>";

?>
<script language='JavaScript' type='text/javascript' src='js/vars.js'></script>
<script language='JavaScript' type='text/javascript' src='js/stockfish_engine.js'></script>
<script>
let game = Chess();
game.load_pgn('<?=$pgn?>');

eval_chess = Chess();
eval_chess.load_pgn('<?=$pgn?>');

ana_chess = Chess();
ana_chess.load_pgn('<?=$pgn?>');

let cur_move = game.history().length - 1;

board = ChessBoard('board', {
});

hist = game.history({ verbose: true });

for (let i=hist.length - 2; i>=0; --i) {
  game.undo();
  hist[i].fen = game.fen();
}
game.load_pgn('<?=$pgn?>');

// Go to first position
for (let i=hist.length - 1; i>=0; --i) {
  eval_chess.undo();
  ana_chess.undo();
}

board.position(game.fen());
$('#startBtn').on('click', board.start);
$('#clearBtn').on('click', board.clear);

function send_fen() {
  document.getElementById('input_fen').value = board.fen() + ' w KQkq - 0 1';
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
        //console.log("Evaluated", game.board[i], pv);
      }
    }
  }
  return totalEvaluation;
}

function GetMoveHtml(i) {
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
  if (typeof eval_best_move[i] === 'undefined') {
    hcolor="#ffffff";
    comment = '';
  }
  else if (eval_best_move[i].san == hist[i].san) {
    hcolor="#99ff99";
    comment = "Best move";
  }
  else if (move_type === 4) {
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
    comment = "Good move (" + eval_best_score[i + 1] + "). Best move was " + eval_best_move[i].san + " (" + eval_afterbest_score[i] + ")";
  }
  else if (move_type === 0) {
    hcolor="#ffffff";
    comment = '';
  }
  st += "<td title='" + comment + "' bgcolor=" + hcolor + ">";
  st += "<a href=# onclick='goToMove(" + i + ");'><font color=black>";
  if (i == cur_move) st += "<b>";
  st += "&nbsp;";
  st += hist[i].san;
  st += "&nbsp;";
  return st;
}

function ShowPgn() {
  let mypgn = "";
  let turn = hist.length;
  mypgn = "<table>";
  //console.log(hist);
  for (let i=0; i<turn; ++i) {
    if (i % 2 === 1) continue;
    mypgn += "<tr><td>" + Math.floor((i / 2) + 1) + ".";
    mypgn += GetMoveHtml(i, hist);
    if (i<turn - 1) {
      mypgn += GetMoveHtml(i + 1, hist);
    }
    else mypgn += "<td>";
    if (typeof eval_best_move[i] === 'undefined') continue;
    mypgn += "<td>&nbsp; best: &nbsp;";
    mypgn += "<td>&nbsp;" + eval_best_move[i].san + "&nbsp;";
    if (typeof eval_best_move[i + 1] === 'undefined') continue;
    if (i<turn - 1) {
      mypgn += "<td>&nbsp;" + eval_best_move[i + 1].san + "&nbsp;";
    }
  }
  mypgn += "</table>";
  // Scroll to bottom
  let pgnSel = $('#pgn');
  //pgnSel.scrollTop(pgnSel[0].scrollHeight);
  pgnSel.html(mypgn);
}

function ShowStatus() {
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
  }
  let st = game_status;
  let est = eval_score_st[cur_move + 1];
  if (typeof est != 'undefined' && est != '') {
    st += " (" + est + ")";
  }
  $('#status').html(st);
  $('#bcaptures').html("<b>Black material balance: " + (pValueSum('b') - pValueSum('w')));
  $('#wcaptures').html("<b>White material balance: " + (pValueSum('w') - pValueSum('b')));
}

function goToMove(i) {
  game.load_pgn('<?=$pgn?>');
  for (let x=hist.length - 1; x>i; --x) {
    game.undo();
  }
  board.position(game.fen());
  cur_move = i;
  ShowStatus();
  ShowRating(eval_best_score[cur_move + 1], eval_score_st[cur_move + 1]);
  ShowPgn();
  ShowMove();
}

function ShowMove() {
  boardEl.find('.highlight-yellow').removeClass('highlight-yellow');
  boardEl.find('.highlight-green').removeClass('highlight-green');
  let from = hist[cur_move].from;
  let to = hist[cur_move].to;
  boardEl.find('.square-' + from).addClass('highlight-yellow');
  boardEl.find('.square-' + to).addClass('highlight-yellow');
  //window.setTimeout(HighlightPosMoves, 1500);
}

function eval_pos(turn, color) {
  if (!engine_eval) return;
  engine_eval.state = 'Running';
  eval_turn = turn;
  eval_color = color;
  if (debugging) console.log("Called eval_pos with: ", eval_turn, eval_color, eval_chess.history());
  engine_eval.send("position fen " + eval_chess.fen());
  engine_eval.send("go depth " + eval_depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      eval_chess.move({from: matches[1].substr(0, 2), to: matches[1].substr(2, 2), promotion: 'q'});
      eval_best_move[eval_turn] = eval_chess.history({ verbose: true })[eval_chess.history().length - 1];
      eval_chess.undo();
      if (debugging) console.log("Score eval: ", eval_afterbest_score, eval_best_score, eval_best_move, eval_chess.history(), eval_turn)
      if (eval_turn < hist.length) {
        ana_chess.move({from: matches[1].substr(0, 2), to: matches[1].substr(2, 2), promotion: 'q'});
        analyse_move(eval_turn, ana_chess.turn());
      }
      engine_eval.state = 'Wait';
      ShowRating(eval_best_score[cur_move + 1], eval_score_st[cur_move + 1]);
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

      // Convert the relative score to an absolute score.
      if (eval_color === "b") {
        score *= -1;
      }
      eval_score_st[eval_turn] = (score>0 ? "+" : "") + Math.round(score / 10) / 10;
      if (type === "mate") {
        eval_score_st[eval_turn] = "mate in " + Math.abs(score);
        score = 100000 * score;
      }

      eval_cur_depth = depth;
      ShowProgress();
      eval_cur_depth = depth;
      eval_best_score[eval_turn] = score;
      ShowPgn();
    }
  });
}

function analyse_move(turn, color) {
  if (!engine_ana) return;
  engine_ana.state = 'Running';
  if (debugging) console.log("Called analyse_move with: ", turn, color, ana_chess.history());
  ana_turn = turn;
  ana_color = color;
  engine_ana.send("position fen " + ana_chess.fen());
  engine_ana.send("go depth " + eval_depth, function ongo(str)
  {
    let matches = str.match(/^bestmove\s(\S+)(?:\sponder\s(\S+))?/);
    if (matches) {
      ana_chess.undo();
      ana_chess.move(hist[eval_turn]);
      eval_chess.move(hist[eval_turn]);
      eval_pos(eval_turn + 1, eval_chess.turn());
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
      if (ana_color === "b") {
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

function ShowProgress() {
  let canvas = document.getElementById("progress");
  let ctx = canvas.getContext("2d");
  let pos1 = canvas.width * eval_cur_depth / engine_eval.depth;
  let pos2 = canvas.width * ana_cur_depth / engine_ana.depth;
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#999999";
  ctx.fillRect(0, 0, pos1, 1);
  ctx.fillRect(0, 1, pos2, 2);
}

function ShowRating(rating, st) {
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

//debugging = 1;
init_engine_eval();
init_engine_ana();
eval_pos(0, eval_chess.turn());
ShowPgn();

$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});
</script>
<?php

echo "<a class='btn btn-primary' href='startplay.php?move_color=$move_color&fen=$w[fen]&rs_b=$w[rs_b]&rs_w=$w[rs_w]'>Continue game</a> ";
echo "<button class='btn btn-disabled' data-html=true data-toggle=popover title='PGN' data-content='<pre>$w[pgn]'>Show PGN</button> ";

include "template/footer.php";
?>