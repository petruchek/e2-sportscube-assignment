# SportsCube Challenge

SportsCube is basically the term for e2's database.

You're required to create a widget showing the top soccer sport events for the upcoming days.
Top events means they got the highest "score" within the next 3 days.
 
## Data Source

The data is provided by our REST API.

Via this axis you can read all upcoming matches up to whatever-timestamp, eg.: '2017-03-18T09:27:00Z' from the API: `https://api.sports-cube.com/v3/de_DE/42/matches?apikey=APIKEY&attach=matches.competition&matchdate_to=2017-03-18T09:27:00Z&states=PRE`. There is another `matchdate_from` parameter which can be used to filter.

Each response contains a `pagination` field, providing you the link for the next page.

Each attached competition entity contains a field `globalImportance`. Use its value to decide if a match is worth showing. The `country` field URL should be used to retrieve the name for the required output.

The APIKEY value for access control is communicated via e-mail.

## Required Output

Generate some HTML that shows the top upcoming matches for each of the next 3 days (midnight to midnight):
* _Top 15 matches_ per day sorted by their `competition.globalImportance` (then sorted by their kickoff-time).
* Timezone for kick-off dates should be 'Europe/Vienna'.

If today would be 17.03.2017, print something like this in your HTML:
```
17.03.2017

| 20:45 | Bayern Munich vs. Real Madrid | International - Champions League |
| 20:15 | Wolfsburg vs. BVB Dortmund | Deutschland -  Bundesliga |
| 16:00 | Rapid Wien vs. Austria Wien | Österreich - Bundesliga |
<!-- ...12 more matches -->

18.03.2017

| 20:15 | Barcelona vs. Liverpool | International - Champions League |
| 14:00 | Stuttgart vs. 1. FC Köln | Deutschland -  Bundesliga |
| 16:00 | Rapid Wien vs. Austria Wien | Österreich - Bundesliga |
<!-- ...12 more matches -->

19.03.2017

| 20:15 | Bayern Munich vs. Real Madrid | International - Champions League |
| 20:45 | Wolfsburg vs. BVB Dortmund | Deutschland -  Bundesliga |
| 16:00 | Rapid Wien vs. Austria Wien | Österreich - Bundesliga |
<!-- ...12 more matches -->

```

Of course, you should not use historic data from 2017, but live data from the upcoming days.

## Finally

* ZIP only your code and send via Mail
* Answer the following questions:
	* How could you improve loading performance?
 	* Which function can you use to format localized weekday names (in German: Wed --> Mi)?

