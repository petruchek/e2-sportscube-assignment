<?php

namespace petruchek\sportscube;

//very simple implementation of API response caching. Works for GET requests only (URL-based caching: cache filename == md5(URL) )
//cache expiration is stored in file modification time (good cache => filemtime in future, bad cache => otherwise)
//does some cleaning (but only when someone tries to read the cache that has already expired).
//needs some generic cleaning method that will iterate through all files in root() and purge files with filemtime() < time()
//expects global constant VAR_CACHE_DIR, doesn't create subdirectories (would make sense to separate cache by directories based on first/second character of the filename)
//all methods are static
class Cache
{
	//where we store cached files
	public static function root()
	{
		return VAR_CACHE_DIR.DIRECTORY_SEPARATOR;
	}

	//how we project $url onto our storage
	public static function path($url)
	{
		return self::root().md5($url).".cache";
	}

	//save $data from $url till $valid_till timestamp
	public static function set($url, $data, $valid_till)
	{
		if ($valid_till <= time())
			return false;

		if (!is_writeable(self::root()))
			return false;
					
		$path = self::path($url);
		file_put_contents($path, $data);
		touch($path, $valid_till);
		
		return true;
	}

	//return cached version of $url if 1) we have it 2) it's still good
	public static function get($url)
	{
		$path = self::path($url);
		if (!file_exists($path))
			return false;
		if (filemtime($path) < time())
		{
			unlink($path);
			return false;
		}
		return file_get_contents($path);
	}
}