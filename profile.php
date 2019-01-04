<?php
require_once "lib/lib.php";
require_once "lib/config.php";
require_once "lib/auth.php";

$act = secure_variable("act");
$name = secure_variable("name");
$login = secure_variable("login");
$hint = secure_variable("login");
$score = secure_variable("score");
$bestmoves = secure_variable("bestmoves");
$depth = secure_variable("depth");
$adepth = secure_variable("adepth");
$undo = secure_variable("undo");

$title = "$site_name: Settings";

if (!login()) {
  die ("<script language=javascript>location.replace('index.php');</script>");
}

if ($act == "save") {
  $q = "UPDATE users SET u_login='$login', u_name='$name', u_depth='$depth', u_adepth='$adepth', u_bestmoves='" .
    (isset($_GET['bestmoves'])?1:0) . "', u_hint='" .
    (isset($_GET['hint'])?1:0) . "', u_undo='" .
    (isset($_GET['undo'])?1:0) . "', u_score='" .
    (isset($_GET['score'])?1:0) . "' 
    WHERE u_id='$uid'";
  //echo $q;
  mysqli_query($ml,$q);
  echo mysqli_error($ml);
  //exit;
  die ("<script language=javascript>location.replace('profile.php');</script>");
}

include "template/menu.php";

echo "<div class=container>";
echo "<br>";
echo "<h3>Settings</h3>";
echo "<hr>";
?>

<form action=profile.php method=get>
  <input type="hidden" name="act" value="save">
  <div class="form-group">
    <label for="name"><b>Full name</b></label>
    <input type="text" class="form-control" id="name" name=name value="<?=$ua['u_name'];?>" placeholder="Enter your full name" required>
  </div>
  <div class="form-group">
    <label for="login"><b>Email address</b></label>
    <input type="email" class="form-control" id="login" name=login value="<?=$ua['u_login'];?>" aria-describedby="emailHelp" placeholder="Enter email" required>
    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
  </div>
  <div class="form-group">
    <label for="depth"><b>Game evaluation depth for Stockfish algorithm (13 recommended)</b></label>
    <input type="number" min=1 max=1000 class="form-control" id="depth" name=depth value="<?=$ua['u_depth'];?>" required>
  </div>
  <div class="form-group">
    <label for="adepth"><b>Analysis evaluation depth for Stockfish algorithm (18 recommended)</b></label>
    <input type="number" min=1 max=1000 class="form-control" id="adepth" name=adepth value="<?=$ua['u_adepth'];?>" required>
  </div>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" name="hint" id="hint" <? if ($ua['u_hint']) echo "checked";?>>
    <label class="form-check-label" for="hint">Allow to click Hint button to show recommended move during the game</label>
  </div>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" name="score" id="score" <? if ($ua['u_score']) echo "checked";?>>
    <label class="form-check-label" for="score">Show evaluation score during the game</label>
  </div>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" name="bestmoves" id="bestmoves" <? if ($ua['u_bestmoves']) echo "checked";?>>
    <label class="form-check-label" for="bestmoves">Show best moves and detect blunders/mistakes during the game</label>
  </div>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" name="undo" id="undo" <? if ($ua['u_undo']) echo "checked";?>>
    <label class="form-check-label" for="undo">Show Undo button during the game</label>
  </div>
  <br>
  <button type=submit value=submit name=submit class="btn btn-primary">Save settings</button>
</form>

<?php
echo "<br><br>You can change password by signing out and clicking 'Forgot password'<br>";
include "template/footer.php";
?>
