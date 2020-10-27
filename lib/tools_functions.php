<?php
/**
 * Function library for data processing for php iNaturalist client (phpinaturalist_functions.php)
 * @author Konstantin Grebennikov <kgrebennikov@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License, version 3
 */

/**
 * @param string $image_path path to the image
 * @param int $coord_acc accuracy of coordinates (lon/lat) (by default meets the usual accuracy of most GPS receivers in cameras, phones, trackers, etc. (~10 m = ~0.0001 deg.))
 * @param int $alt_acc accuracy of altitude (m above mean sea level) (by default = 1 m (rounds altitude to int))
 * @return array datetime (ISO 8601) (['datetime']), longitude (WGS84) (['longitude']), latitude (WGS84) (['latitude']), altitude (m) (['altitude']) from the image
 */
function get_data_from_exif($image_path, $coord_acc = 4, $alt_acc = 0)
{
    //get exif data from $image
    if (!file_exists($image_path)) {
        exit("The file '$image_path' does not exists");
    }
    $exif = exif_read_data($image_path);
    $data = array();
    if (!isset($exif['DateTimeOriginal'])) {
        echo("No original datetime in $image_path");
    }
    //convert datetime to ISO 8601 format
    $datetime_interm = str_replace(' ', 'T', $exif['DateTimeOriginal']);
    $data['datetime'] = str_replace(':', '-', $datetime_interm);
    //if(!isset($exif['GPSLongitude'])){echo("No longitude in $image_path");}
    //convert longitule data to decimal
    $lon = eval('return (' . $exif['GPSLongitude'][0] . ')+((' . $exif['GPSLongitude'][1] . ')/60)+((' . $exif['GPSLongitude'][2] . ')/3600)');
    //if longitude in the Western Hemishere - convert value to negative
    if ($exif['GPSLongitudeRef'] == 'W') {
        $lon = 0 - $lon;
    }
    $data['longitude'] = round($lon, $coord_acc);
    //if(!isset($exif['GPSLatitude'])){echo("No latitude in $image_path");}
    //convert latitude data to decimal
    $lat = eval('return (' . $exif['GPSLatitude'][0] . ')+((' . $exif['GPSLatitude'][1] . ')/60)+((' . $exif['GPSLatitude'][2] . ')/3600)');
    //if latitude in the Southern Hemishere - convert value to negative
    if ($exif['GPSLatitudeRef'] == 'S') {
        $lat = 0 - $lat;
    }
    $data['latitude'] = round($lat, $coord_acc);
    //if(!isset($exif['GPSAltitude'])){echo("No altitude in $image_path");}
    //convert altitude data to decimal
    $alt = eval('return ' . $exif['GPSAltitude'] . ';');
    //if altitude in above the sea level - convert value to negative
    //convert GPSAltitudeRef from octal to decimal
    $alt_flag = octdec($exif['GPSAltitudeRef']);
    if ($alt_flag == 1) {
        $alt = 0 - $alt;
    }
    $data['altitude'] = round($alt, $alt_acc);
    //
    return ($data);
}

/**
 * @param string $tz_sever URL of server
 * @param string $tz_token valid token for access to the server
 * @param float|int $lon longitude in degrees (WGS) (between 180 and -180, e.g. -100.25)
 * @param float|int $lat latitude in degrees (WGS)  (between 90 and -90, e.g. -50.1)
 * @return array timezone data for coordinates via external server or error code with explanation
 */
function get_timezone_data($tz_sever, $tz_token, $lon, $lat)
{
    $url = "$tz_sever?lon=$lon&lat=$lat&token=$tz_token";
    $curl = curl_init();
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}