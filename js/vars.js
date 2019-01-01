let game_status = '';
let engine_eval;
let eval_depth = 12;
let eval_best_move = [];
let eval_afterbest_path = [];
let eval_score = [];
let move_comment = '';
let move_comment2 = '';
let move_hcolor = 'white';
let eval_afterbest_score = [];
let eval_ponder = [];
let eval_turn = 0;
let eval_color = 'w';
let eval_chess;
let eval_score_st = [];
let eval_afterscore_st = [];
let ana_chess;
let ana_turn = 0;
let ana_color = 'w';
let ana_cur_depth = 0;
let eval_cur_depth = 0;

let engine = [];

let ptypes = ['p', 'b', 'n', 'r', 'q', 'k'];

let game_id = 0;
let MAX_RULES = 300;
let rname = []; // Rule names
let rdesc = []; // Rule descriptions
let rpos = []; // Rule possibility for each player
rpos['b'] = [];
rpos['w'] = [];
let rpar = []; // Rule parameters for each player
rpar['b'] = [];
rpar['w'] = [];
for (let i=0; i<MAX_RULES; ++i) {
  rpar['b'][i] = [];
  rpar['w'][i] = [];
}
let rs_b, rs_w;

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
  boardEl = $('#board'),
  statusEl = $('#status'),
  brulesEl = $('#brules'),
  wrulesEl = $('#wrules'),
  bcapturesEl = $('#bcaptures'),
  wcapturesEl = $('#wcaptures'),
  fenEl = $('#fen'),
  pgnEl = $('#pgn'),
  tnum,
  // Last captured piece in history
  last_cap,
  // Last captured piece in history
  prelast_cap,
  // History of moves
  hist = []
;

// Possible moves
let posMoves = [];
let posMoves2 = [];
// Active rules
let ract = [];
// Moves disabled by each rule
let rdis = [];
