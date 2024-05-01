<?php

require_once __DIR__ . '/Controllers/Xml.php';
require_once __DIR__ . '/Controllers/Search.php';
require_once __DIR__ . '/Models/Database.php';
require_once __DIR__ . '/Models/Author.php';
require_once __DIR__ . '/Models/Book.php';

use Controllers\Xml;
use Controllers\Search;

$startTime = date("h:i:s");

# Get action and do it
$msg = '';
$result = [];
switch ($_POST['action']) {
    case 'reset-xml':
        $minAmountXml = $_POST['minAmountXml'];
        $maxAmountXml = $_POST['maxAmountXml'];
        $minElementsInXml = $_POST['minElementsInXml'];
        $maxElementsInXml = $_POST['maxElementsInXml'];
        $depth = $_POST['depth'];

        $xml = new Xml();
        $xml->recursiveClearDirectory($xml::startFolder);
        $result = $xml->createFolderStructure($xml::startFolder, $depth, $minAmountXml, $maxAmountXml, $minElementsInXml, $maxElementsInXml);
        $msg = 'Directories and XML files successfully created';
        break;

    case 'parse-xml':
        $xml = new Xml();

        $result = $xml->parseXML($xml::startFolder);
        $msg = "Parsing completed successfully";
        break;

    case 'search':
        $searchController = new Search();
        $result = $searchController->searchAuthor($_POST['query']);
        if (is_string($result)) {
            $msg = $result;
        } else {
            $msg = "Search completed successfully";
        }
        break;

    default:
        $msg = "Error. This action does not exist";
        break;
}

# Get time
$endTime = date("h:i:s");
$difference = strtotime($endTime) - strtotime($startTime);

$hours = floor($difference / 3600);
$minutes = floor(($difference / 60) % 60);
$seconds = $difference % 60;

# Return reply
$response = json_encode([
    'msg' => "$msg, Duration: $hours:$minutes:$seconds",
    'result' => $result
], JSON_THROW_ON_ERROR);

echo $response;
