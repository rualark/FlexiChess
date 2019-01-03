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

function DisableMinProtect(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
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
  // Get maximum protection
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

function DisableMinProtectIfDecr(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let base_acnt = game.all_attacks(game.turn(), game.turn());
  // Get maximum protection
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
  // Get maximum protection
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

function DisableBadPosition(rid) {
  if (!ract[rid]) return;
  if (hist.length > rpar[game.turn()][rid][0] * 2) return;
  let pvs_cur = getMyPVS(game, game.turn());
  console.log("PVS: ", pvs_cur);
  for (let i=0; i<posMoves.length; ++i) {
    let move = posMoves[i];
    console.log(getMyPVS(move.chess));
    if (pvs_cur <= getMyPVS(move.chess, game.turn()))
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
  DisableBadPosition(181);
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
