# php client and additional function for data processing

## phpinaturalist_functions.php

php client functions for the iNaturalist API

### Requirements for hpinaturalist_functions.php

PHP 5.6 or above with cURL

### Implemented at the moment

* Authentication by Resource Owner Password Credentials Flow
* Endpoints:
  * POST/observations
  * POST/observation_field_value
  * POST/observation_photos
  * GET/taxa

These functions allows to construct some "pipeline" to create observations and add field values and photos.

## tools_functions.php

Function library for data processing

### List of functions

* get_data_from_exif - prepare observation data (longitude, latitude, observed_on_string) in iNaturalist API compatible format from geotagged image.
* get_timezone_data - php client for time zone server (see "/time_zone_server" below) - allow to receive compatible with iNaturalist API time_zone name.

### Requirements for tools_functions.php

PHP 5.6 or above with cURL and exif
