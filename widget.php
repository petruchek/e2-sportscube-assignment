<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Petruchek Sportscube Demo Widget</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>
<div class="col-sm-4">
<h3>Intentionally poorly-designed widget</h3>
<?php

require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";

$matches = petruchek\sportscube\Matches::get(['date_format'=>"d.m.Y",'time_format'=>"H:i",'days_future'=>3,'games_daily'=>15]);
echo "<table class='petruchek-sportscube-widget table table-sm table-striped'>\n";
foreach ($matches as $day=>$daily_games)
{
	echo "\t<tr class='one-day'><th colspan='3'>$day</th></tr>\n";
	foreach ($daily_games as $game)
	{
		echo "\t<tr class='one-match'>\n\t\t<td class='match-time'>{$game['time']}</td>\n\t\t<td class='match-teams'>".htmlspecialchars($game['teams'])."</td>\n\t\t<td class='match-competition'>".htmlspecialchars($game['competition'])."</td>\n\t</tr>\n";
	}
}
echo "</table>\n";
?>
</div>
</body>
</html>