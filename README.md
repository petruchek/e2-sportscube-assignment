# Petruchek Sportscube Assignment

## The assignment

This task was done according to **challenge_software-engineer_php.md** which must be located in the very same directory as this README.md.

## Configuration

Please put your `apikey` into `config.php`. Script will be working without it only in a textual demo mode; in this case you have to specify it as a command line parameter **api_key** (see below).

## Running

There are two ways to run the demo script: as a command line tool (``index.php``) and as a widget (``widget.php``).

### Running from the command line

Example command to output 20 games for today with US-format for the date:

`php index.php --date_format="l, m/d/y" --days_future=1 --games_daily=20 vs_separator=' - '`

Available options:

 - `date_format`: date format (as in PHP DateTime implementation)
 - `time_format`: time format (as in PHP DateTime implementation)
 - `days_future`: how many days to fetch games for (1 means today only)
 - `games_daily`: how many top games to fetch for each day
 - `vs_separator`: string to concatenate participating teams
 - `team_name`: what field to use as a name of the team (`fullname`, `shortname` or `microname`)
 - `competition_name`: what field to use as a name of the competition (`name`, `shortname` or `microname`)
 - `api_key`: if for any reason you don't want to set your API key in `config.php`, you can pass it via this option

All parameters are optional as the code contains default values. Also, there's no validation of the parameters since we are not exposing them to the end user.

### Running as a widget

Simply open `widget.php` in your browser (via web-server, of course). It will read the API key from the `config.php` and will make one call to the `Matches` class with the following options:

```php
['date_format'=>"d.m.Y",'time_format'=>"H:i",'days_future'=>3,'games_daily'=>15]
```
The widget intentionally has no design (other than default bootstrap styling for tables) and doesn't use any template engines as this is out of the scope of this assignment.

## Encoding

Since I have no documentation for the API but can access the actual data, I've assumed all strings are UTF-8. Also, I've put someone else's implementation of `mb_str_pad()` function from github to make command line tool print tidy textual tables (PHP doesn't support multi-byte strings in native `str_pad()` function). 

## Timezones

Again, I don't have the API documentation, but my testing shows that all the timestamps in the API are GMT. I've assumed that kick-off times and expiration timestamps are GMTs. The application is generating all the output according to local timezone which can be configured in `config.php` (currently set to `Europe/Vienna`). Also there's a tricky moment with the `matchdate_to` parameter - we need to send not GMT midnight, but midnight in our timezone converted to GMT. This way we guarantee that response has no matches starting after the our local midnight on the last day (no need to filter them out). 

## Caching

I've implemented very basic caching to speed up the response time. The caching is based on an URL that we need to fetch. I hash the THE with `md5()` and check local storage for that hash. Then I check if the local version of that file has not expired yet. If it's still good, I omit the HTTP request for that particular URL and use locally saved response instead. Cache lifetime is stored as the file modification time (good cache file has its modification time in the future). I'm using `expires` field from the response as a cache lifetime.

I'm using local filesystem as a storage, directory can be configured in `config.php`; by default it's `var/cache` and it must be writeable or caching won't work. 

Note: there's no cleaning of the expired cache files (other than during the request). In a production-like environment it should be fixed. 

## Tests

This implementation contains no tests. The code is split into 3 independent classes: `Matches`, `Client`, and `Cache` to comply with the single-responsibility principle and make testing easier: test the `Cache` separately (doesn't depend on anything), test the `Client` separately (doesn't need to do anything besides fetching data), test the `Matches` separately (doesn't need real API responses to do the filtering/sorting according to `$options` passed).

## Improvements #todo

0. Clean expired cache
1. Introduce higher level of caching - besides caching per URL, cache per `Matches::get()` request within the same set of `$options` passed. Use `min(expires)` of response pages as a lifetime for this accumulative cache. This cache will reduce number of file reads and further parsing of the JSON data. 
2. Implement tests
3. Handle more exceptions 
4. Validate parameters entered by the user (maximum number of days, maximum number of matches per day etc)
5. Follow some code formatting rules
6. Write phpdoc-styled comments for methods

## 