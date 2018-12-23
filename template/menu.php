<!doctype html>
<html lang="en">
<head>
  <?php
  share_header("$url_share",
    "$site_name",
    "Hotseat chess with flexible rules",
    "$url_share/img/flexichess_600.png");
  ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="icons/king.ico">

  <title><?=$title ?></title>

  <!-- Bootstrap core CSS -->
  <link rel="stylesheet" href="plugin/bootstrap-4.0.0/bootstrap.min.css">

  <!-- Custom styles for this template -->
  <link href="css/flexichess.css" rel="stylesheet">
</head>

<body>
<?php
require_once __DIR__ . "/../analytics.php";
show_chatovod("artquiz");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="index.php"><b><?=$site_name;?></b></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
      <?php
      if ($uid) {
        ?>
        <ul class="navbar-nav mr-auto">
          <li class=nav-item><a class=nav-link href="rulesets.php"><b>Rule sets</b></a></li>
          <li class=nav-item><a class=nav-link href="ruleset.php?act=new">New</a></li>
          <li class=nav-item><a class=nav-link href="contact.php">Contact</a></li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?=$ua['u_name'] ?></a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
              <a class="dropdown-item" href="profile.php">Profile</a>
              <?php
              if ($ua['u_admin']) {
                echo "<a target=_blank class='dropdown-item' href='stats.php'>Stats</a>";
              }
              ?>
              <a class="dropdown-item" href="login.php?action=out">Sign out</a>
            </div>
          </li>
        </ul>
        <?php
      }
      else {
        ?>
        <ul class="navbar-nav mr-auto">
          <li class=nav-item><a class=nav-link href="rulesets.php"><b>Rule sets</b></a></li>
          <li class=nav-item><a class=nav-link href="contact.php">Contact</a></li>
        </ul>
        <ul class="navbar-nav">
          <li class=nav-item><a class=nav-link href="login.php"><b>Login</b></a></li>
          <li class=nav-item><a class=nav-link href="reg.php"><b>Sign up</b></a></li>
        </ul>
        <?php
      }
      ?>
    </div>
  </div>
</nav>
<script language='JavaScript' type='text/javascript' src='js/jquery.min.js'></script>
