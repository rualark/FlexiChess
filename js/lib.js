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

function first(p) {
  for (let i in p) {
    return p[i];
  }
}

function amin(arr) {
  let min_val = first(arr);
  arr.forEach(function(item, i, arr) {
    if (item < min_val) {
      min_val = item;
    }
  });
}

function amax(arr) {
  let max_val = first(arr);
  arr.forEach(function(item, i, arr) {
    if (item > max_val) {
      max_val = item;
    }
  });
}

function build_score(type, score) {
  if (type === "mate") {
    return Math.round(1000000 - Math.abs(score + 1) * 100) *
      (score + 1) / Math.abs(score + 1);
  }
  else {
    return score;
  }
}

function build_score_st(type, score) {
  if (type === "mate") {
    if (score)
      return "mate in " + Math.abs(score);
    else return "mate";
  }
  else {
    return (score>0 ? "+" : "") + Math.round(score / 10) / 10;
  }
}

function build_move_analysis(i, move, best_move, move_score, best_score, move_score_st, best_score_st, best_path) {
  // Return nothing if data is not ready
  if (typeof move_score === 'undefined' ||
    typeof best_score === 'undefined' ||
    typeof best_move === 'undefined') {
    move_hcolor = 'white';
    move_comment = '';
    move_comment2 = '';
    return;
  }
  let best_path_st = '';
  if (best_path !== '') {
    best_path_st = ': ' + best_path;
  }
  // Return best move if move is the same
  if (best_move.san == move.san) {
    move_hcolor = '#99ff99';
    move_comment = "Best move (" + best_score_st + " or " + move_score_st + ")" + best_path_st;
    move_comment2 = "<b>Best move</b> (" + move_score_st + ")";
    return;
  }
  let sign = 1;
  if (i % 2 === 1) sign = -1;
  let delta = sign * (best_score - move_score);
  // Return good move if my move is better than best move or has same score (not to fire faster mate)
  if (delta <= 0) {
    move_hcolor = '#99ff99';
    move_comment = "Good move (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    move_comment2 = "<b>Good move</b> (" + sign + " " + move_score + " " + best_score + " " + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    return;
  }
  // Detect faster mate
  if (move_score > 900000 && best_score > 900000) {
    move_hcolor = '#ffff00';
    if (best_score - move_score < 0) {
      move_comment = "Faster mate (" + move_score + " " + best_score + " " + delta + " " + sign + " " + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
      move_comment2 = "<b>Faster mate</b> (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    }
    else {
      move_comment = "Slower mate (" + move_score + " " + best_score + " " + delta + " " + sign + " " + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
      move_comment2 = "<b>Slower mate</b> (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    }
    return;
  }
  if (delta > 300) {
    move_hcolor = '#ff5555';
    move_comment = "Blunder (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    move_comment2 = "<b>Blunder</b> (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    return;
  }
  if (delta > 100) {
    move_hcolor = '#ffbb55';
    move_comment = "Mistake (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    move_comment2 = "<b>Mistake</b> (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    return;
  }
  if (delta > 50) {
    move_hcolor = '#ffff00';
    move_comment = "Inaccuracy (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    move_comment2 = "<b>Inaccuracy</b> (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
    return;
  }
  move_hcolor = '#99ff99';
  move_comment = "Good move (" + move_score_st + "). Best move was " + best_move.san + " (" + best_score_st + ")" + best_path_st;
  move_comment2 = "<b>Good move</b> (" + move_score_st + ")";
}
