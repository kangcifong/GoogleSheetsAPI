
<?php
#require_once __DIR__ . '/vendor/autoload.php';
/*
require_once __DIR__ . '/src/Google/Service.php';
require_once __DIR__ . '/src/Google/Auth/OAuth2.php';
require_once __DIR__ . '/src/Google/Client.php';
require_once __DIR__ . '/src/Google/Model.php';
require_once __DIR__ . '/src/Google/Collection.php';
require_once __DIR__ . '/src/Google/Config.php';
require_once __DIR__ . '/src/Google/Service/Resource.php';
require_once __DIR__ . '/src/Google/Service/Sheets.php';
require_once __DIR__ . '/src/Google/Service/Drive.php';
*/
#set_include_path(get_include_path() . PATH_SEPARATOR . '/Users/kang/Sites/gas/google-api-php-client/src');

//require_once __DIR__ . '/merge/merge.php';
include __DIR__ . '/merge/merge.php';
//require_once __DIR__ . '/src/Google/Service/Sheets.php';

define('APPLICATION_NAME', 'Google Sheets API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::DRIVE,
  Google_Service_Sheets::SPREADSHEETS)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = file_get_contents($credentialsPath);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->authenticate($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, $accessToken);
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
//#$spreadsheetId = "1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms";
$spreadsheetId = "1R8qUEUwe4ATwscLP0S1ts1vmxHvKjg6xTecNsexViR0";
//$category = "project";
$category = "pr/interview";
//$category = "general";

$range = $category."!A2:H";

$response = $service->spreadsheets_values->get($spreadsheetId, $range);

//echo "response\n";
$start_time=time();
$values = $response->getValues();
/*
if (count($values) == 0) {
  print "No data found.\n";
} else {
  print "Name, Major:\n";
  foreach ($values as $row) {
    printf("%s, %s\n", $row[0], $row[4]);
  }
}

printf("Number of rows %d\n", count($values) );
*/
$range = $category."!A".(string)(count($values)+2).":H";
$vRan = new Google_Service_Sheets_ValueRange();
$vRan->setMajorDimension("ROWS");
$vRan->setRange($range);
$val = array
  (
	array(date("Y/m/d H:i:s",time()), "Project", "PHPName", "PHPCompany","mail@php.com","PHPDetail","PHPVenue","PHPExhibition")
  );
$vRan->setValues($val);
$type="USER_ENTERED";// $type="RAW";
$response = $service->spreadsheets_values->update($spreadsheetId, $range, $vRan, array("valueInputOption"=>$type));
//echo $response;
printf("Execution Time: %d\n",time()-$start_time);
