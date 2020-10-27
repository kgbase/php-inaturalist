<?php
/**
 * php client functions for the iNaturalist API (php version 5.6 or above)
 * @author Konstantin Grebennikov <kgrebennikov@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License, version 3
 */

/**
 * iNaturalist Authentication by Resource Owner Password Credentials Flow:
 * @link https://www.inaturalist.org/pages/api+reference#auth
 * @param string $app_id id of your iNaturalist application
 * @param string $app_secret secret of your iNaturalist application
 * @param string $inat_user iNaturalist user id (owner of the application)
 * @param string $inat_pass iNaturalist user password (owner of the application)
 * @param string $baseurl endpoint
 * @return array|null response from iNaturalist.org: authorization token for app (["access_token","token_type","scope","created_at"]), authorization error message (if data is incorrect) or null if the request (curl_exec) was unsuccessfull
 */
function iNat_auth_request_by_passwd($app_id, $app_secret, $inat_user, $inat_pass, $baseurl = 'https://www.inaturalist.org/oauth/token')
{
    $curl = curl_init();
    $payload = array('client_id' => $app_id, 'client_secret' => $app_secret, 'grant_type' => "password", 'username' => $inat_user, 'password' => $inat_pass,);
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $baseurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}

/**
 * iNaturalist POST/observations:
 * @link https://www.inaturalist.org/pages/api+reference#post-observations
 * @param array $observation observation parameters
 * example of the array:
 * $observation = array(
 *  'species_guess'=>'Some name', //string
 *  'taxon_id'=>'taxon id from iNaturalist', //string
 *  'observed_on_string'=>'some datetime', //string
 *  'time_zone'=>'some zone', //some zone
 *  'latitude'=>0.0, //integer or float
 *  'longitude'=>0.0, //integer or float
 *  'geoprivacy'=>'open', //string
 * )
 * 'taxon_id' value may be received from iNat_get_taxa function: usually it is in [0]['id'] element of array returned (if taxon name is correct);
 * For receiving some parameters of the observation, some additional functions (see tools_functions.php) may be useful:
 * if there is some geotagged photo of the observation, 'observed_on_string', 'latitude' and 'longitude' values may be received from get_data_from_exif function;
 * 'time_zone' value may be received from get_timezone_data function.
 * @param string $access_token access token of your iNaturalist application (see iNat_auth_request_by_passwd function)
 * @param string $baseurl endpoint
 * @return array|null response from iNaturalist.org: new observation data, authorization error message (if token or data is incorrect) or null if the request (curl_exec) was unsuccessfull
 */
function iNat_post_observation($observation, $access_token, $baseurl = 'https://api.inaturalist.org/v1/observations')
{
    $header = array("Content-Type:application/json", "Authorization: Bearer $access_token");
    $payload = json_encode(array("observation" => $observation));
    $curl = curl_init();
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $baseurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}

/**
 * POST/observation_field_value:
 * @link https://www.inaturalist.org/pages/api+reference#post-observation_field_values
 * @param array $field_value observation parameters, for example:
 * $field_value=array('observation_id'=>[id of observation] (int),'observation_field_id'=>[some id] (int),'value'=>[some value] (depends on field),);
 * @param string $access_token access token of your iNaturalist application (see iNat_auth_request_by_passwd function)
 * @param string $baseurl endpoint
 * @return array|null response from iNaturalist.org: new observation field value data, authorization error message (if token or data is incorrect) or null if the request (curl_exec) was unsuccessfull
 * Notes:
 * iNaturalist does not set a fixed list of field values. Users can create new values themselves.
 * The list of existing values is available on the website: https://www.inaturalist.org/observation_fields
 * Value ID is a number in a URL to a value description, for example:
 * observation_field_id https://www.inaturalist.org/observation_fields/[!8036!]
 * Some 'observation_field_id's (useful for the observations via collected specimens):
 * Habitat: 10
 * collected: 634
 * Collector: 8036
 * Collector Field Number: 86
 * Number of Individuals Collected/Observed: 2866
 */
function iNat_post_observation_field_value($field_value, $access_token, $baseurl = 'https://api.inaturalist.org/v1/observation_field_values')
{
    $header = array("Content-Type:application/json", "Authorization: Bearer $access_token");
    $payload = json_encode($field_value, JSON_PRETTY_PRINT);
    $curl = curl_init();
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $baseurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}

/**
 * POST /observation_photos:
 * @link https://www.inaturalist.org/pages/api+reference#post-observation_photos
 * @param string $file_path path to the image
 * @param int $observation_id iNaturalist id of the observation
 * @param string $access_token access token of your iNaturalist application (see iNat_auth_request_by_passwd function)
 * @param string $baseurl endpoint
 * @return array|null response from iNaturalist.org: new observation photo of the observation data, authorization error message (if token or data is incorrect) or null if the request (curl_exec) was unsuccessfull
 */
function iNat_post_observation_photo($file_path, $observation_id, $access_token, $baseurl = 'https://api.inaturalist.org/v1/observation_photos')
{
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    $file_src = fopen($file_path, 'rb');
    $file_content = fread($file_src, filesize($file_path));
    fclose($file_src);
    $file_info = pathinfo($file_path);
    $file_name = $file_info['basename'];
    $header = array("Content-Type: multipart/form-data; boundary=$delimiter", "Authorization: Bearer $access_token",);
    $body = "--$delimiter\r\n";
    $body .= 'Content-Disposition: form-data; name="observation_photo[observation_id]"' . "\r\n\r\n$observation_id\r\n--$delimiter\r\n";
    $body .= 'Content-Disposition: form-data; name="file"; filename="' . $file_name . '"' . "\r\nContent-Type: image/jpeg\r\n\r\n";
    $body .= "$file_content\r\n--$delimiter--";
    $curl = curl_init();
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $baseurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}

/**
 * GET/taxa:
 * @link https://api.inaturalist.org/v1/docs/#!/Taxa/get_taxa
 * @param string $taxa name of the taxa for searching
 * @param string $inat_user iNaturalist user (or application) id (optional, see more on https://www.inaturalist.org/pages/api+recommended+practices)
 * @param null|string $rank taxonomic rank (optional, performing the search of all ranks by default)
 * @param string $baseurl endpoint
 * @return array|null response from iNaturalist.org: taxon(s) data, error message (if data is incorrect) or null if the request (curl_exec) was unsuccessfull
 */
function iNat_get_taxa($taxa, $inat_user = '', $rank = null, $baseurl = 'https://api.inaturalist.org/v1/taxa')
{
    $taxa = str_replace(' ', '%20', $taxa);
    if (!$rank) $url = $baseurl . '?q=' . $taxa; else $url = $baseurl . '?q=' . $taxa . '&rank=' . $rank;
    $curl = curl_init();
    if ($curl) {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, $inat_user);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        $object = json_decode($out);
        return json_decode(json_encode($object), true);
    } else {
        return null;
    }
}