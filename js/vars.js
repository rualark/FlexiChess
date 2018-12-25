let evaler;
let eval_depth = 12;
let eval_best_move = [];
let eval_ponder = [];

let ptypes = ['p', 'b', 'n', 'r', 'q', 'k'];

let game_id = 0;
let MAX_RULES = 300;
let rname = []; // Rule names
let rdesc = []; // Rule descriptions
let rpos = []; // Rule possibility for each player
rpos[0] = [];
rpos[1] = [];
let rpar = []; // Rule parameters for each player
rpar[0] = [];
rpar[1] = [];
for (let i=0; i<MAX_RULES; ++i) {
  rpar[0][i] = [];
  rpar[1][i] = [];
}
let rs_id0, rs_id1;

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

let debugging = 1;

let board,
  game = new Chess(),
  boardEl = $('#board'),
  statusEl = $('#status'),
  brulesEl = $('#brules'),
  wrulesEl = $('#wrules'),
  bcapturesEl = $('#bcaptures'),
  wcapturesEl = $('#wcaptures'),
  fenEl = $('#fen'),
  pgnEl = $('#pgn'),
  // Current Player id
  pid,
  // Other Player id
  pid2,
  // Current turn number in history
  tnum,
  // Last captured piece in history
  last_cap,
  // Last captured piece in history
  prelast_cap,
  // History of moves
  hist
;

// Possible moves
let posMoves, posMoves2,
  // Active rules
  ract,
  // Moves disabled by each rule
  rdis
;
