<?php
require_once("global.php");
if ($_GET['do'] == "rate" && in_array($_GET['val'], array("bad", "good"))) {
    $ex = alreadyRated();
    if (!in_array($_GET['id'], $ex) && is_numeric($_GET['id'])) {
        if ($_GET['val'] == "good") {
            $val = 100;
        } else {
            $val = 0;
        }
        addRating($_GET['id'], $val);
        setcookie("already", $_COOKIE['already'] . $_GET['id'] . "A", time()+86400*365);
        header('Location: index.php');
        die;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Pokemon Episode Downloader version <?=VERSION?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Bootstrap core CSS -->
        <link href="css/bootstrap.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="container">
            <h2 align='center'>Pokemon Episode Downloader</h2>
            <h4 align='center'>Version <?=VERSION?></h4>
            <p class="text-muted" align="center">Full source available on <a href='https://github.com/solewolf/Pokemon-Episode-Downloader'>Github</a></p>
            <a href="https://twitter.com/solewolf1993" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @solewolf1993</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script><a href="https://twitter.com/intent/tweet?text=Check%20out%20this%20awesome%20pokemon%20episode%20downloader!%0Ahttp://guysthatcode.com/projects/poke%0A#pokemon #downloader" class="twitter-hashtag-button" data-size="large" data-related="solewolf1993">Tweet #pokemondownloader</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            <p><b>Season</b></p>
            <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#1" data-toggle="tab" title="Season 1 - <?=$seaname[1]?>">1</a></li>
                <?php
                    for ($a = 2; $a <= count($seaname); $a++) {
                        echo "<li><a href=\"#" . $a . "\" data-toggle=\"tab\" title=\"Season " . $a . " - " . $seaname[$a] . "\">" . $a . "</a></li>\n";
                    }
                ?>
            </ul>
            <div id="myTabContent" class="tab-content">
<?php
$b = 1;
for ($a = 1; $a <= count($seaname); $a++) {
    $active = null;
    if ($a == 1) {
        $active = "active";
    }
?>
                <div class="tab-pane fade in <?=$active?>" id="<?=$a?>">
                    <table class="table table-hover centered">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Rating</th>
                            <th>Mirror 1</th>
                            <th>Mirror 2</th>
                            <th>Mirror 3</th>
                            <th>Mirror 4</th>
                            <th>Mirror 5</th>
                            <th>Total Downloads</th>
                            <th>Rate</th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$already = alreadyRated();
for (;$b <= $seanum[$a+1]; $b++) {
?>                      <tr>
                            <td><?=$b?></td>
                            <td><?=getTitle($b)?></td>
                            <td><?=hpbar(getValue($b, "rating"), 100, getValue($b, "total"))?></td>
                            <td><a href='poke.php?mirror=1&id=<?=$b?>'>Download</a></td>
                            <td><a href='poke.php?mirror=2&id=<?=$b?>'>Download</a></td>
                            <td><a href='poke.php?mirror=3&id=<?=$b?>'>Download</a></td>
                            <td><a href='poke.php?mirror=4&id=<?=$b?>'>Download</a></td>
                            <td><a href='poke.php?mirror=5&id=<?=$b?>'>Download</a></td>
                            <td><?=number_format(getValue($b, "downloads"))?></td>
<?php
if (in_array($b, $already)) {
?>
                            <td><button type="button" class="btn btn-danger" disabled>Bad</button>&nbsp;<button type="button" class="btn btn-success" disabled>Good</button></td>
<?php
} else {
?>
                            <td><button type="button" class="btn btn-danger" onclick="window.location.href='index.php?do=rate&id=<?=$b?>&val=bad';">Bad</button>&nbsp;<button type="button" class="btn btn-success" onclick="window.location.href='index.php?do=rate&id=<?=$b?>&val=good';">Good</button></td>
<?php
}
?>
                        </tr>
<?
}
?>
                        </tbody>
                    </table>
                </div>
<?php
}
?>
            </div>
        </div>
            <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    </body>
</html>
