<?php
/**
 * Simple textual demo file that:
	0. loads config (@constant API_CLIENT_ID)
	1. parses command line arguments (all are optional), example to output 20 games for today with US-format for the date:
		php index.php --date_format="l, m/d/y" --days_future=1 --games_daily=20 vs_separator=' - '
	2. calls API Talker class (passing $arguments; getting $matches)
	3. calculates widths for the output to look nice
	4. outputs the $matches in a textual table day by day
 * 
 * php version 7.4.10
 *
 * @category Demo
 * @package  Petruchek_Sportscube_Assignment
 * @author   Val Petruchek <petruchek@gmail.com>
 * @license  https://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace petruchek\sportscube;

require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";

$arguments = getopt ( "", ['date_format::', 'time_format::', 'days_future::', 'games_daily::', 'vs_separator::', 'team_name::', 'competition_name::', 'api_key::']);

if (!defined("API_CLIENT_ID")) {
	if (array_key_exists('api_key', $arguments))
		define("API_CLIENT_ID", $arguments['api_key']);
	else
		throw new \Exception("API_CLIENT_ID must be defined by now.");
}

if (! ($matches = Matches::get($arguments)))
{
	throw new \Exception("\nNo matches fetched. Possible reasons:\n\t1) global lockdown caused by a pandemic\n\t2) network connectivity disabled in PHP\n\t3) invalid API key\n");
}

//------------------------------------------------------------------------------------------
//this is needed for this textual demo only; credits: https://gist.github.com/nebiros/226350
function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
{
	$diff = strlen( $input ) - mb_strlen( $input );
	return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
}
//------------------------------------------------------------------------------------------
define('H', '-');
define('V', '|');
define('L', "\n");

$x = 0; //width of time column
$y = 0; //width of game column
$z = 0; //width of competition column
foreach ($matches as $day=>$daily_games)
{
	foreach ($daily_games as $game)
	{
		$x = max($x, mb_strlen($game['time']));
		$y = max($y, mb_strlen($game['teams']));
		$z = max($z, mb_strlen($game['competition']));
	}
}

$width = 7+$x+$y+$z;
$line = str_repeat(H, 3+$width).L;
foreach ($matches as $day=>$daily_games)
{
	echo $line;
	echo V." ".mb_str_pad($day, $width, ' ').V.L;
	echo $line;
	foreach ($daily_games as $game)
	{
		echo V." ".mb_str_pad($game['time'], $x, ' ')." ".V." ".mb_str_pad($game['teams'], $y, ' ').' '.V." ".mb_str_pad($game['competition'], $z, ' ').' '.V.L;
	}
}
echo $line;