let sca_positionCount = 0;
let sca_eval_effect = 1;

let minimaxRoot = function(depth, game, isMaximisingPlayer) {

  let newGameMoves = game.moves();
  let bestMove = -9999;
  let bestMoveFound;

  for(let i = 0; i < newGameMoves.length; i++) {
    let newGameMove = newGameMoves[i];
    game.move(newGameMove);
    let value = minimax(depth - 1, game, -10000, 10000, !isMaximisingPlayer);
    game.undo();
    if(value >= bestMove) {
      bestMove = value;
      bestMoveFound = newGameMove;
      //console.log(bestMove, bestMoveFound);
    }
    //console.log(newGameMove, value);
  }
  return bestMoveFound;
};

let minimax = function (depth, game, alpha, beta, isMaximisingPlayer) {
  sca_positionCount++;
  if (depth === 0) {
    return -evaluateBoard(game);
  }

  let newGameMoves = game.moves();

  if (isMaximisingPlayer) {
    let bestMove = -9999;
    for (let i = 0; i < newGameMoves.length; i++) {
      game.move(newGameMoves[i]);
      bestMove = Math.max(bestMove, minimax(depth - 1, game, alpha, beta, !isMaximisingPlayer));
      game.undo();
      alpha = Math.max(alpha, bestMove);
      if (beta <= alpha) {
        return bestMove;
      }
    }
    return bestMove;
  } else {
    let bestMove = 9999;
    for (let i = 0; i < newGameMoves.length; i++) {
      game.move(newGameMoves[i]);
      bestMove = Math.min(bestMove, minimax(depth - 1, game, alpha, beta, !isMaximisingPlayer));
      game.undo();
      beta = Math.min(beta, bestMove);
      if (beta <= alpha) {
        return bestMove;
      }
    }
    return bestMove;
  }
};

let evaluateBoard = function (game) {
  let totalEvaluation = 0;
  for (let i = 0; i <= 119; i++) {
    /* did we run off the end of the board */
    if (i & 0x88) { i += 7; continue; }
    let pv = getPieceValue(game.geti(i), i % 16, Math.floor(i / 16));
    totalEvaluation += pv;
  }
  return totalEvaluation;
};

function getMyPVS(game, color) {
  let totalEvaluation = 0;
  for (let i = 0; i <= 119; i++) {
    /* did we run off the end of the board */
    if (i & 0x88) { i += 7; continue; }
    let piece = game.geti(i);
    if (piece === null) continue;
    if (piece.color !== color) continue;
    let pv;
    let x = i % 16;
    let y = Math.floor(i / 16);
    let isWhite = piece.color === 'w';
    if (piece.type === 'p') {
      pv = isWhite ? pawnEvalWhite[y][x] : pawnEvalBlack[y][x];
    } else if (piece.type === 'r') {
      pv = isWhite ? rookEvalWhite[y][x] : rookEvalBlack[y][x];
    } else if (piece.type === 'n') {
      pv = knightEval[y][x];
    } else if (piece.type === 'b') {
      pv = isWhite ? bishopEvalWhite[y][x] : bishopEvalBlack[y][x];
    } else if (piece.type === 'q') {
      pv = evalQueen[y][x];
    } else if (piece.type === 'k') {
      pv = isWhite ? kingEvalWhite[y][x] : kingEvalBlack[y][x];
    }
    console.log("Evaluated " + piece.type + " at " + x + "/" + y + " to " + pv);
    totalEvaluation += pv;
  }
  console.log("Tota: " + totalEvaluation);
  return totalEvaluation;
}

let reverseArray = function(array) {
  return array.slice().reverse();
};

let pawnEvalWhite =
  [
    [0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0],
    [5.0,  5.0,  5.0,  5.0,  5.0,  5.0,  5.0,  5.0],
    [1.0,  1.0,  2.0,  3.0,  3.0,  2.0,  1.0,  1.0],
    [0.5,  0.5,  1.0,  2.5,  2.5,  1.0,  0.5,  0.5],
    [0.0,  0.0,  0.0,  2.0,  2.0,  0.0,  0.0,  0.0],
    [0.5, -0.5, -1.0,  0.0,  0.0, -1.0, -0.5,  0.5],
    [0.5,  1.0, 1.0,  -2.0, -2.0,  1.0,  1.0,  0.5],
    [0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0]
  ];

let pawnEvalBlack = reverseArray(pawnEvalWhite);

let knightEval =
  [
    [-5.0, -4.0, -3.0, -3.0, -3.0, -3.0, -4.0, -5.0],
    [-4.0, -2.0,  0.0,  0.0,  0.0,  0.0, -2.0, -4.0],
    [-3.0,  0.0,  1.0,  1.5,  1.5,  1.0,  0.0, -3.0],
    [-3.0,  0.5,  1.5,  2.0,  2.0,  1.5,  0.5, -3.0],
    [-3.0,  0.0,  1.5,  2.0,  2.0,  1.5,  0.0, -3.0],
    [-3.0,  0.5,  1.0,  1.5,  1.5,  1.0,  0.5, -3.0],
    [-4.0, -2.0,  0.0,  0.5,  0.5,  0.0, -2.0, -4.0],
    [-5.0, -4.0, -3.0, -3.0, -3.0, -3.0, -4.0, -5.0]
  ];

let bishopEvalWhite = [
  [ -2.0, -1.0, -1.0, -1.0, -1.0, -1.0, -1.0, -2.0],
  [ -1.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -1.0],
  [ -1.0,  0.0,  0.5,  1.0,  1.0,  0.5,  0.0, -1.0],
  [ -1.0,  0.5,  0.5,  1.0,  1.0,  0.5,  0.5, -1.0],
  [ -1.0,  0.0,  1.0,  1.0,  1.0,  1.0,  0.0, -1.0],
  [ -1.0,  1.0,  1.0,  1.0,  1.0,  1.0,  1.0, -1.0],
  [ -1.0,  0.5,  0.0,  0.0,  0.0,  0.0,  0.5, -1.0],
  [ -2.0, -1.0, -1.0, -1.0, -1.0, -1.0, -1.0, -2.0]
];

let bishopEvalBlack = reverseArray(bishopEvalWhite);

let rookEvalWhite = [
  [  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0],
  [  0.5,  1.0,  1.0,  1.0,  1.0,  1.0,  1.0,  0.5],
  [ -0.5,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -0.5],
  [ -0.5,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -0.5],
  [ -0.5,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -0.5],
  [ -0.5,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -0.5],
  [ -0.5,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -0.5],
  [  0.0,   0.0, 0.0,  0.5,  0.5,  0.0,  0.0,  0.0]
];

let rookEvalBlack = reverseArray(rookEvalWhite);

let evalQueen = [
  [ -2.0, -1.0, -1.0, -0.5, -0.5, -1.0, -1.0, -2.0],
  [ -1.0,  0.0,  0.0,  0.0,  0.0,  0.0,  0.0, -1.0],
  [ -1.0,  0.0,  0.5,  0.5,  0.5,  0.5,  0.0, -1.0],
  [ -0.5,  0.0,  0.5,  0.5,  0.5,  0.5,  0.0, -0.5],
  [  0.0,  0.0,  0.5,  0.5,  0.5,  0.5,  0.0, -0.5],
  [ -1.0,  0.5,  0.5,  0.5,  0.5,  0.5,  0.0, -1.0],
  [ -1.0,  0.0,  0.5,  0.0,  0.0,  0.0,  0.0, -1.0],
  [ -2.0, -1.0, -1.0, -0.5, -0.5, -1.0, -1.0, -2.0]
];

let kingEvalWhite = [

  [ -3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
  [ -3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
  [ -3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
  [ -3.0, -4.0, -4.0, -5.0, -5.0, -4.0, -4.0, -3.0],
  [ -2.0, -3.0, -3.0, -4.0, -4.0, -3.0, -3.0, -2.0],
  [ -1.0, -2.0, -2.0, -2.0, -2.0, -2.0, -2.0, -1.0],
  [  2.0,  2.0,  0.0,  0.0,  0.0,  0.0,  2.0,  2.0 ],
  [  2.0,  3.0,  1.0,  0.0,  0.0,  1.0,  3.0,  2.0 ]
];

let kingEvalBlack = reverseArray(kingEvalWhite);

let getPieceValue = function (piece, x, y) {
  if (piece == null) {
    return 0;
  }
  let getAbsoluteValue = function (piece, isWhite, x ,y) {
    if (piece.type === 'p') {
      return 10 + sca_eval_effect * ( isWhite ? pawnEvalWhite[y][x] : pawnEvalBlack[y][x] );
    } else if (piece.type === 'r') {
      return 50 + sca_eval_effect * ( isWhite ? rookEvalWhite[y][x] : rookEvalBlack[y][x] );
    } else if (piece.type === 'n') {
      return 30 + sca_eval_effect * knightEval[y][x];
    } else if (piece.type === 'b') {
      return 30 + sca_eval_effect * ( isWhite ? bishopEvalWhite[y][x] : bishopEvalBlack[y][x] );
    } else if (piece.type === 'q') {
      return 90 + sca_eval_effect * evalQueen[y][x];
    } else if (piece.type === 'k') {
      return 900 + sca_eval_effect * ( isWhite ? kingEvalWhite[y][x] : kingEvalBlack[y][x] );
    }
    throw "Unknown piece type: " + piece.type;
  };

  let absoluteValue = getAbsoluteValue(piece, piece.color === 'w', x, y);
  return piece.color === game.them() ? absoluteValue : -absoluteValue;
};
