<?php

namespace petruchek\sportscube;

//Special class that returns all the games (from cache or from the API)
//Handles pagination. Glues required information from $teams and $competitions into $matches
//Doesn't do sorting, filtering or any other modification to set of $matches returned by the API
class Client
{
	const BASE_URL = "https://api.sports-cube.com/v3/de_DE/42/matches";	

	private $params;
	private $root_url;

	//remember params, extract root_url that we'll use multiple times for pagination requests
	public function __construct($params)
	{
		$this->params = $params;

		$url = parse_url(self::BASE_URL);
		$this->root_url = $url['scheme']."://".$url['host'];
	}

	//start with first page, follow the next pages until we have any, store all matches, teams and competitions in their arrays
	//before returning array of matches attach team names, competition names and scores to them
	public function grab()
	{
		$url = self::BASE_URL."?".http_build_query($this->params);
		$matches = $teams = $competitions = [];
		do {
			$raw_data = $this->fetch($url);
			if (array_key_exists('data', $raw_data))
			{
				foreach ($raw_data['data'] as $match_url)
				{
					$match = $raw_data['attachments'][$match_url];

					$home_team_url = $match['teams']['home'];
					$away_team_url = $match['teams']['away'];
					$competition_url = $match['competition'];

					$matches[$match_url] = $match;
					$teams[$home_team_url] = $raw_data['attachments'][$home_team_url];
					$teams[$away_team_url] = $raw_data['attachments'][$away_team_url];
					$competitions[$competition_url] = $raw_data['attachments'][$competition_url];
				}
			}
			if (array_key_exists('pagination', $raw_data) && array_key_exists('next',$raw_data['pagination']))
				$url = $this->build_url($raw_data['pagination']['next']);
			else
				$url = '';
		} while ($url); 

		$result = [];
		foreach ($matches as $match)
		{
			$game = [];
			$game['timestamp'] = strtotime($match['matchdate']);
			$game['team_home_names'] = $this->get_names($teams[$match['teams']['home']]);
			$game['team_away_names'] = $this->get_names($teams[$match['teams']['away']]);
			$game['competition_names'] = $this->get_names($competitions[$match['competition']]);
			$game['competition_score'] = $competitions[$match['competition']]['globalImportance'];
			$result[] = $game;
		}

		return $result;		
	}	

	//'sign' the request by appending our apikey to it
	private function build_url($url)
	{
		return $this->root_url.$url."&apikey=".$this->params['apikey'];
	}

	//since we allow user to choose what to return
	private function get_names($object)                                                                                        
	{
		return array_intersect_key($object, array_flip(['name','shortname','microname','fullname']));
	}

	//get, check if response is json, save to cache, return
	private function fetch($url)
	{
		$response = $this->get($url);
		$jsondata = json_decode($response, true);

		if (json_last_error() != JSON_ERROR_NONE)
			return false;

		if (array_key_exists('expires',$jsondata))
			Cache::set($url, $response, strtotime($jsondata['expires']));
	
		return $jsondata;
	}

	//try cache, if not then file_get_contents and fall back to curl
	private function get($url)
	{
		if ($cached = Cache::get($url))
			return $cached;
		if (preg_match('/on|yes|1|true/i', ini_get('allow_url_fopen')))
			return file_get_contents($url);
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return false;
	}
}