function load_engine()
{
  let worker = new Worker("js/stockfish/stockfish.js"),
    engine = {
      started: Date.now(),
      ponder: [],
      best_move: [],
      mpv: [],
      depth: 0,
      err_prob: 0,
      max_err: 0,
      state: 'Wait',
      level: 0
    },
    que = [];

  function get_first_word(line)
  {
    let space_index = line.indexOf(" ");

    /// If there are no spaces, send the whole line.
    if (space_index === -1) {
      return line;
    }
    return line.substr(0, space_index);
  }

  function determine_que_num(line, que)
  {
    let cmd_type,
      first_word = get_first_word(line),
      cmd_first_word,
      i,
      len;

    if (first_word === "uciok" || first_word === "option") {
      cmd_type = "uci"
    } else if (first_word === "readyok") {
      cmd_type = "isready";
    } else if (first_word === "bestmove" || first_word === "info") {
      cmd_type = "go";
    } else {
      /// eval and d are more difficult.
      cmd_type = "other";
    }

    len = que.length;

    for (i = 0; i < len; i += 1) {
      cmd_first_word = get_first_word(que[i].cmd);
      if (cmd_first_word === cmd_type || (cmd_type === "other" && (cmd_first_word === "d" || cmd_first_word === "eval"))) {
        return i;
      }
    }

    /// Not sure; just go with the first one.
    return 0;
  }

  worker.onmessage = function (e)
  {
    let line = e.data,
      done,
      que_num = 0,
      my_que;

    if (debugging) console.log(e.data);

    /// Stream everything to this, even invalid lines.
    if (engine.stream) {
      engine.stream(line);
    }

    /// Ignore invalid setoption commands since valid ones do not repond.
    if (line.substr(0, 14) === "No such option") {
      return;
    }

    que_num = determine_que_num(line, que);

    my_que = que[que_num];

    if (!my_que) {
      return;
    }

    if (my_que.stream) {
      my_que.stream(line);
    }

    if (typeof my_que.message === "undefined") {
      my_que.message = "";
    } else if (my_que.message !== "") {
      my_que.message += "\n";
    }

    my_que.message += line;

    /// Try to determine if the stream is done.
    if (line === "uciok") {
      /// uci
      done = true;
      engine.loaded = true;
    } else if (line === "readyok") {
      /// isready
      done = true;
      engine.ready = true;
    } else if (line.substr(0, 8) === "bestmove") {
      /// go [...]
      done = true;
      /// All "go" needs is the last line (use stream to get more)
      my_que.message = line;
    } else if (my_que.cmd === "d" && line.substr(0, 15) === "Legal uci moves") {
      done = true;
    } else if (my_que.cmd === "eval" && /Total Evaluation[\s\S]+\n$/.test(my_que.message)) {
      done = true;
    } else if (line.substr(0, 15) === "Unknown command") {
      done = true;
    }
    ///NOTE: Stockfish.js does not support the "debug" or "register" commands.
    ///TODO: Add support for "perft", "bench", and "key" commands.
    ///TODO: Get welcome message so that it does not get caught with other messages.
    ///TODO: Prevent (or handle) multiple messages from different commands
    ///      E.g., "go depth 20" followed later by "uci"

    if (done) {
      if (my_que.cb && !my_que.discard) {
        my_que.cb(my_que.message);
      }
    }
  };

  engine.send = function send(cmd, cb, stream)
  {
    cmd = String(cmd).trim();

    /// Can't quit. This is a browser.
    ///TODO: Destroy the engine.
    if (cmd === "quit") {
      return;
    }

    if (debugging) {
      console.log(cmd);
    }

    /// Only add a que for commands that always print.
    ///NOTE: setoption may or may not print a statement.
    if (cmd !== "ucinewgame" && cmd !== "flip" && cmd !== "stop" && cmd !== "ponderhit" && cmd.substr(0, 8) !== "position"  && cmd.substr(0, 9) !== "setoption") {
      que[que.length] = {
        cmd: cmd,
        cb: cb,
        stream: stream
      };
    }
    worker.postMessage(cmd);
  };

  engine.stop_moves = function stop_moves()
  {
    let i,
      len = que.length;

    for (i = 0; i < len; i += 1) {
      if (debugging) {
        console.log(i, get_first_word(que[i].cmd))
      }
      /// We found a move that has not been stopped yet.
      if (get_first_word(que[i].cmd) === "go" && !que[i].discard) {
        engine.send("stop");
        que[i].discard = true;
      }
    }
  };

  engine.get_cue_len = function get_cue_len()
  {
    return que.length;
  };

  engine.set_level = function set_level(lvl)
  {
    if (lvl < 0) {
      lvl = 0;
    }
    if (lvl > 20) {
      lvl = 20;
    }
    engine.level = lvl;

    engine.send("setoption name Skill Level value " + lvl);

    ///NOTE: Stockfish level 20 does not make errors (intentially), so these numbers have no effect on level 20.
    /// Level 0 starts at 1
    engine.err_prob = Math.round((lvl * 6.35) + 1);
    /// Level 0 starts at 5
    engine.max_err = Math.round((lvl * -0.25) + 5);

    engine.send("setoption name Skill Level Maximum Error value " + engine.max_err);
    engine.send("setoption name Skill Level Probability value " + engine.err_prob);

    ///NOTE: Could clear the hash to make the player more like it's brand new.
    /// player.engine.send("setoption name Clear Hash");
  };

  return engine;
}
