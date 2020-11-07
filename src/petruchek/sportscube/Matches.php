<?php

namespace petruchek\sportscube;

//Primary class to be called to obtain list of matches according to $options. Must be called statically. 
//All $options are optional, default values can be changed in the defaultize() method of this class.
class Matches
{
	const SECONDS_24H = 86400;
	
	//takes user options, calls default options, builds params for the Client, tasks the Client to grab the games according to params
	//extracts data needed for sorting, formats date and time, formats the names, sorts the games, cleans the returning set 
	//returns array of arrays: key is the day => values are lists of matches sorted by 1) competitionscore DESC 2) kick-off time ASC
	public static function get($options = [])
	{
		$options = self::defaultize($options);

		$params = [
			'apikey' => $options['api_key'],
			'attach' => 'matches.competition',
			'states' => 'PRE',
			'matchdate_to' => gmdate("Y-m-d\TH:i:s\Z",strtotime('today')+self::SECONDS_24H*$options['days_future']-1),
		];

		$client = new Client($params);
		$games = $client->grab();

		foreach ($games as &$game)
		{
			$game['teams'] = $game['team_home_names'][$options['team_name']] . $options['vs_separator'] . $game['team_away_names'][$options['team_name']];
			$game['competition'] = $game['competition_names'][$options['competition_name']];
			$game['time_sort'] = date("H:i", $game['timestamp']);
			$game['time'] = date($options['time_format'], $game['timestamp']);
			$game['date_sort'] = date("Y-m-d", $game['timestamp']);
			$game['date_show'] = date($options['date_format'], $game['timestamp']);
		}

		usort($games, function($a, $b) { 
			return $a['date_sort'] <=> $b['date_sort'] ?: $b['competition_score'] <=> $a['competition_score'] ?: $a['time_sort'] <=> $b['time_sort'];
		});

		$result = [];
		foreach ($games as $game)
		{
			$date = $game['date_show'];
			if (!array_key_exists($date,$result))
				$result[$date] = [];
			if (count($result[$date]) >= $options['games_daily'])
				continue;
			$result[$date][] = array_intersect_key($game, array_flip(['time','teams','competition']));
		}

		return $result;
	}

	//takes array of $options and merges with array of default options to ensure none of them are missed
	private static function defaultize($options)
	{
		return $options + [
			'date_format' => "d.m.Y",
			'time_format' => 'H:i',
			'days_future' => 3,
			'games_daily' => 15,
			'vs_separator' => ' vs. ',
			'team_name' => 'shortname', //fullname, shortname or microname
			'competition_name' => 'name', //name, shortname or microname
			'api_key' => API_CLIENT_ID,
		];
	}
}