# standalone server for determine the time zone by geographic longitude and latitude

The iNaturalist API endpoint [POST/observations](https://www.inaturalist.org/pages/api+reference#post-observations) requires the name of time zone according to fixed list of the names. In fact, this list of time zones is quite peculiar, and does not correspond to the names of the zones neither in [The Time Zone Database IANA](https://www.iana.org/time-zones), nor in various public services to determine time zones.  Thus, defining the time zone in a format compatible with the iNaturalist API is significantly complicated.

To solve this problem, a spatial MySQL database with time zone boundaries was created, and the interface to address it. The zone boundaries have been created based on the [TZ timezones of the world](http://efele.net/maps/tz/world/), the iNaturalist API zone names have been defined based on the [List of tz database time zones](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones) wikipedia page, [The Time Zone Database IANA](https://www.iana.org/time-zones)  and the [time.is](https://time.is/) service.

## Requirements

MySQl server (5.6 or above), PHP (5.6 or above with mysqli)

## Installation

1. Import timezone_database.sql in your MySQL database.
2. Create your own access token(s) in `tokens` table/
3. Put index.php (endpoint) on your web server.
4. Set actual values in "server configuration" section.
5. Your server is ready to use.

## Usage

Just send GET request to http(s):/path-where-your-endpoint-is-located/ with 3 parameters:

* `lon` - longitude in degrees (WGS) (between 180 and -180, e.g. -100.25)
* `lat` - latitude in degrees (WGS) (between 180 and -180, e.g. -100.25)
* `token` - alid token for access to the server

The result of your request will be json like:

```json
{

   "code":"200",

   "message":"time zone is defined",

   "result":{

      "ianaid":"Europe\/Volgograd",

      "inatid":"Volgograd",

      "utc_offset":"+04:00"

   }

}
```

where:

* `ianaid` - name of time zone in IANA database
* `inatid` - name of time zone in iNaturalist API list of time zones
* `utc_offset` - difference in hours and minutes from Coordinated Universal Time (UTC)

If the request was unsuccessful - the server will return json with error code and its explanation.

The database does not include water area of seas and oceans, as well as Antarctica - in this case the server will return error code 400 and the message "longitude and latitude in the request is out of any time zone".

The database, as well as the source data for its creation, is provided under the [CC0 1.0 Universal (CC0 1.0) Public Domain Dedication](https://creativecommons.org/publicdomain/zero/1.0/) license.
