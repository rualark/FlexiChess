// Must move pawns
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

function DisableMoveFromClose(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    //console.log(move, move.chess.closeCnt(game.them(), move.from));
    if (!tpiece && game.closeCnt(game.them(), move.from) > rpar[game.turn()][rid][1]) {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableMoveToClose(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    //console.log(move, move.chess.closeCnt(game.them(), move.from));
    if (!tpiece && move.chess.closeCnt(game.them(), move.to) > rpar[game.turn()][rid][1]) {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableMoveNotFromClose(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let accnt0 = game.all_closeCnt();
  console.log('Accnt', accnt0);
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    //console.log(move, move.chess.closeCnt(game.them(), move.from));
    if (tpiece) continue;
    let ccnt0 = game.closeCnt(game.them(), move.from);
    let ccnt1 = move.chess.closeCnt(game.them(), move.to);
    let accnt1 = move.chess.all_closeCnt();
    if (ccnt0 > rpar[game.turn()][rid][1] && ccnt1 >= ccnt0) {
      DisableMove(i);
    }
    if (accnt0 > rpar[game.turn()][rid][2] && accnt1 >= accnt0) {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableLen(rid, ptype, moveonly) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (move.piece !== ptype) continue;
    console.log(ptype, move.piece, move.from, move.to);
    if (moveonly === 'moveonly' && tpiece) continue;
    let dist = distance(move.from, move.to);
    if (dist < rpar[game.turn()][rid][1] || dist > rpar[game.turn()][rid][2]) {
      DisableMove(i);
    }
  }
  ValidateRule(rid);
}

function DisableNotBackLine(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let piece;
  let found = 100;
  for (let x=0; x<8; ++x) {
    piece = game.get(make_square(x, 0));
    if (piece && (piece.type === 'r' || piece.type === 'q') && piece.color === game.them()) {
      found = Math.min(found, 0);
    }
    piece = game.get(make_square(x, 1));
    //console.log('Checking', piece);
    if (piece && (piece.type === 'r' || piece.type === 'q') && piece.color === game.them()) {
      found = Math.min(found, 1);
    }
  }
  if (found === 100) return;
  //console.log('Found', found);
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    //console.log(move.from, move.to);
    if (found === 0) {
      if (move.from[1] !== '8' && move.from[1] !== '7') {
        DisableMove(i);
      }
      if (move.to[1] === '8') {
        DisableMove(i);
      }
    }
    if (found === 1) {
      if (move.from[1] !== '7') {
        //console.log('Disabing', move);
        DisableMove(i);
      }
      if (move.to[1] === '7' || move.to[1] === '8') {
        DisableMove(i);
      }
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

function DisableCheckCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  if (hist.length < 1) return;
  // Check if previous move was check
  let undo_move = game.undo();
  let in_check = game.in_check();
  game.move(undo_move);
  if (!in_check) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    // Disable if taking
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

// Must move pawns each YY moves
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
  let base_acnt = game.all_attacks(game.turn(), game.them());
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
  let base_acnt = game.all_attacks(game.turn(), game.them());
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
  let base_acnt = game.all_attacks(game.turn(), game.them());
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
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except increasing attacks by protected
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
  let base_acnt = game.all_attacks(game.turn(), game.them());
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
  let prom = engine[game.turn()].best_move[game.history().length].substr(4, 1);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.from !== from || move.to !== to || move.promotion !== prom)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableStockfishAvailable(rid) {
  if (!ract[rid]) return;
  let best_score = -1000000000;
  let best_moves = [];
  if (debugging) console.log("MPV", engine[game.turn()].mpv);
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.disabled) continue;
    let score = engine[game.turn()].mpv[move.from + move.to + move.promotion];
    if (typeof score === 'undefined') {
      if (debugging) console.log("Move " + move.from + move.to + move.promotion + " not found");
      score = -100000000 - Math.random() * 100000;
    }
    if (score > best_score) {
      best_score = score;
      best_moves = [];
      best_moves.push(move.from + move.to + move.promotion);
    }
    else if (score === best_score) {
      best_moves.push(move.from + move.to + move.promotion);
    }
  }
  // Disambiguation
  let best_move = best_moves[Math.floor(Math.random() * best_moves.length)];
  // Disable all moves except Stockfish best move
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (best_move !== move.from + move.to + move.promotion)
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

function DisableProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Disable all moves except increasing protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableProtectOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Disable all moves except increasing protection or capture
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (base_acnt >= move.chess.all_attacks(game.turn(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMaxProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
  let max_prot = -1;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  //console.log("Max protection: ", max_prot, base_acnt, prot);
  // Disable all moves except max protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Disable all moves except decreasing protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt <= move.chess.all_attacks(game.turn(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnProtectOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Disable all moves except decreasing protection or capture
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (base_acnt <= move.chess.all_attacks(game.turn(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get min protection
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinProtectOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get min protection
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinProtectIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get min protection
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinProtectIfDecrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get min protection
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNotProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Disable all moves except decreasing protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt < move.chess.all_attacks(game.turn(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Disable all moves except decreasing attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt <= move.chess.all_attacks(game.them(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnAttackOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Disable all moves except decreasing attack or capture
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (base_acnt <= move.chess.all_attacks(game.them(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Get min attack
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.them(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    console.log("Disabling ", move.from, move.to, move.chess.all_attacks(game.them(), game.turn()), base_acnt, min_prot);
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinAttackOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Get min attack
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.them(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinAttackIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Get min attack
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.them(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinAttackIfDecrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Get min attack
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.them(), game.turn());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNotAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.them(), game.turn());
  // Disable all moves except decreasing attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt < move.chess.all_attacks(game.them(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnIAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  let base_ycnt = game.all_attacks(game.them(), game.turn());
  if (base_ycnt <= rpar[game.turn()][rid][1] && base_acnt + base_ycnt <= rpar[game.turn()][rid][2]) return;
  // Disable all moves except decreasing attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt <= move.chess.all_attacks(game.turn(), game.them()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableUnIAttackOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  let base_ycnt = game.all_attacks(game.them(), game.turn());
  if (base_ycnt <= rpar[game.turn()][rid][1] && base_acnt + base_ycnt <= rpar[game.turn()][rid][2]) return;
  // Disable all moves except decreasing attack or capture
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (base_acnt <= move.chess.all_attacks(game.them(), game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinIAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Get min attack
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.them());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinIAttackOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Get min attack
  let min_prot = 1000000;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.them());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinIAttackIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Get min attack
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.them());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMinIAttackIfDecrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Get min attack
  let min_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.them());
    if (prot[i] < min_prot)
      min_prot = prot[i];
  }
  // Disable all moves except min attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] > min_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNotIAttack(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.them());
  // Disable all moves except decreasing attack
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (base_acnt < move.chess.all_attacks(game.turn(), game.them()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMaxProtectIfIncr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
  let max_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  //console.log("Max protection: ", max_prot, base_acnt, prot);
  // Disable all moves except max protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMaxProtectIfIncrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
  let max_prot = base_acnt;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  //console.log("Max protection: ", max_prot, base_acnt, prot);
  // Disable all moves except max protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMaxProtectOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
  let max_prot = -1;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.all_attacks(game.turn(), game.turn());
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  // Disable all moves except max protection
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoMoveToUnprotected(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (move.chess.attackedCnt(game.turn(), move.to) < 1)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoveToMaxProtected(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  // Get maximum protected
  let max_prot = 0;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.attackedCnt(game.turn(), move.to);
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableNoMoveToUnprotectedOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (move.chess.attackedCnt(game.turn(), move.to) < 1)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableMoveToMaxProtectedOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  // Get maximum protected
  let max_prot = 0;
  let prot = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    prot[i] = move.chess.attackedCnt(game.turn(), move.to);
    if (prot[i] > max_prot)
      max_prot = prot[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (prot[i] < max_prot)
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorsePosition(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_cur <= getMyPVS(move.chess, game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorstPosition(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_min = 1000000;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] < pvs_min)
      pvs_min = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_min < pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorstPositionIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_min = pvs_cur;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] < pvs_min)
      pvs_min = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_min < pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorsePositionOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_cur <= getMyPVS(move.chess, game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorstPositionOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_min = 1000000;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] < pvs_min)
      pvs_min = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_min < pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableWorstPositionIfDecrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_min = pvs_cur;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] < pvs_min)
      pvs_min = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_min < pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBetterPosition(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_cur >= getMyPVS(move.chess, game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBestPosition(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get best position
  let pvs_max = -1000000;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] > pvs_max)
      pvs_max = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_max > pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBestPositionIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_max = pvs_cur;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] > pvs_max)
      pvs_max = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    if (pvs_max > pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBetterPositionOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_cur >= getMyPVS(move.chess, game.turn()))
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBestPositionOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_max = -1000000;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] > pvs_max)
      pvs_max = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_max > pvs[i])
      DisableMove(i);
  }
  ValidateRule(rid);
}

function DisableBestPositionIfDecrOrCapture(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  // Get worst position
  let pvs_max = pvs_cur;
  let pvs = [];
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    pvs[i] = getMyPVS(move.chess, game.turn());
    if (pvs[i] > pvs_max)
      pvs_max = pvs[i];
  }
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    let tpiece = game.get(move.to);
    if (tpiece) continue;
    if (pvs_max > pvs[i])
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
  DisableCheckCapture(220);
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
  DisableNotAttack(202);
  DisableUnIAttack(203);
  DisableUnIAttackOrCapture(204);
  DisableNotBackLine(219);
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
  DisableCreateAttackOrCapture(147);
  DisableCreateAttackByProtected(148);
  DisableCreateAttackByProtectedOrCaptureStronger(149);
  DisableProtect(169);
  DisableProtectOrCapture(170);
  DisableMaxProtect(171);
  DisableMaxProtectIfIncr(173);
  DisableMaxProtectOrCapture(172);
  DisableMaxProtectIfIncrOrCapture(174);

  DisableUnProtect(175);
  DisableUnProtectOrCapture(176);
  DisableMinProtect(177);
  DisableMinProtectOrCapture(178);
  DisableMinProtectIfDecr(179);
  DisableMinProtectIfDecrOrCapture(180);
  DisableNotProtect(195);
  DisableUnAttack(196);
  DisableUnAttackOrCapture(197);
  DisableMinAttack(198);
  DisableMinAttackOrCapture(199);
  DisableMinAttackIfDecr(200);
  DisableMinAttackIfDecrOrCapture(201);
  DisableMinIAttack(205);
  DisableMinIAttackOrCapture(206);
  DisableMinIAttackIfDecr(207);
  DisableMinIAttackIfDecrOrCapture(208);
  DisableNotIAttack(209);
  DisableMoveFromClose(210);
  DisableMoveToClose(211);
  DisableMoveNotFromClose(212);
  DisableLen(213, 'b', 'moveonly');
  DisableLen(214, 'r', 'moveonly');
  DisableLen(215, 'q', 'moveonly');
  DisableLen(216, 'b');
  DisableLen(217, 'r');
  DisableLen(218, 'q');

  DisableWorsePosition(181);
  DisableWorstPosition(182);
  DisableWorstPositionIfDecr(183);
  DisableWorsePositionOrCapture(184);
  DisableWorstPositionOrCapture(185);
  DisableWorstPositionIfDecrOrCapture(186);
  DisableBetterPosition(187);
  DisableBestPosition(188);
  DisableBestPositionIfDecr(189);
  DisableBetterPositionOrCapture(190);
  DisableBestPositionOrCapture(191);
  DisableBestPositionIfDecrOrCapture(192);
  DisableMustTakeWithPawn(150);
  DisableCreateAttackNotAttacked(154);
  DisableMustTakeUnprotectedOrStronger(151);
  DisableMustTakeStrongest(152);
  DisableMustTakeWithWeakest(153);
  DisableNoMoveToUnprotected(163);
  DisableNoMoveToUnprotectedOrCapture(164);
  DisableMoveToMaxProtected(165);
  DisableMoveToMaxProtectedOrCapture(166);
  DisableStockfishAvailable(160);
}
