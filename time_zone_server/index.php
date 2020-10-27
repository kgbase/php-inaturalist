<?php
/*
This script provides an api interface to MySQL spatial database wich contains spacial polygons of time zones of the world/
The source of the polygones is shape file from tz_world database (http://efele.net/maps/tz/world/).
- 'tzid' variable in the relults returning by this script is equivalent to the same field in original tz_world database
As additional attributes was adeed:
- name of the time zone in Time Zone Database (https://www.iana.org/time-zones) - 'ianaid'
- name of the time zone in value options of POST/observations method in iNaturalist api (https://www.inaturalist.org/pages/api+reference#post-observations) - 'inatid'
- UTC offset (difference in hours and minutes from Coordinated Universal Time (UTC) for the time zone) - 'utc_offset'
This script and the database were made as a part of iNaturalis php uploader project (https://github.com/kgbase/iNaturalist-php-upload)
the single method is get/request and has 3 parameters:
lon - longitude in degrees (WGS) (between 180 and -180, e.g. -100.25)
lat - latitude in degrees (WGS)  (between 90 and -90, e.g. -50.1)
token - access token (valid koken from 'tokens' table in the database)
The result is json file with values:
code - HTTP response code
message - explanation of the reason of code
resutl - the result (if thr request was successfull and code is 200 OK)
@author Konstantin Grebennikov <kgrebennikov@gmail.com>
@license GPLv3 @link https://www.gnu.org/licenses/gpl-3.0.html
*/
//server configuration
$db_user = 'user';
$db_pass = 'password';
$db_name = 'database_name';
$db_server = 'localhost';
//user input
@$lon = $_GET['lon'];
@$lat = $_GET['lat'];
@$token = $_GET['token'];
//check parameters
if (isset($lon) && isset($lat) && isset($token)) {
    //connect to mysql server
    $mysqli = mysqli_connect("$db_server", "$db_user", "$db_pass", "$db_name");
    if (mysqli_connect_errno()) {
        //send 500 HTTP error
        $response = array('code' => '500', 'message' => mysqli_connect_error(),);
        $response_json = json_encode($response);
        header('HTTP/1.0 500 Internal Server Error');
        header('application/json; charset=utf-8');
        echo $response_json;
    }
    //check token status
    $token_query = mysqli_query($mysqli, "SELECT `status` FROM `tokens` WHERE `token` = '$token'");
    $token_data = mysqli_fetch_assoc($token_query);
    if (isset($token_data["status"]) && $token_data["status"] = 'valid') {
        $tz_query = mysqli_query($mysqli, "SELECT `ianaid` , `inatid` , `utc_offset` FROM `time_zones` WHERE ST_Within( ST_GeomFromText( 'POINT($lon $lat)' ) , `WKT_GEOMETRY` ) =1");
        $tz_data = mysqli_fetch_assoc($tz_query);
        if ($tz_query && $tz_data !== null) {
            //send 200 OK response and time zone data
            $response = array('code' => '200', 'message' => 'time zone is defined', 'result' => $tz_data);
            $response_json = json_encode($response);
            header('HTTP/1.0 200 OK');
            header('application/json; charset=utf-8');
            echo $response_json;
        } else {
            //send 400 HTTP error
            $response = array('code' => '400', 'message' => 'longitude and latitude in the request is out of any time zone',);
            $response_json = json_encode($response);
            header('HTTP/1.0 400 Bad Request');
            header('application/json; charset=utf-8');
            echo $response_json;
        }
    } else {
        //send 401 HTTP error
        $response = array('code' => '401', 'message' => 'access token is invalid',);
        $response_json = json_encode($response);
        header('HTTP/1.0 401 Unauthorized');
        header('application/json; charset=utf-8');
        echo $response_json;
    }
} else {
    //send 400 HTTP error
    $response = array('code' => '400', 'message' => 'at list one parameter of the request (lon,lat,token) is missing',);
    $response_json = json_encode($response);
    header('HTTP/1.0 400 Bad Request');
    header('application/json; charset=utf-8');
    echo $response_json;
}