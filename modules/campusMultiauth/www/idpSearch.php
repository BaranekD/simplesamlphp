<?php

use SimpleSAML\Metadata\MetaDataStorageHandler;

header('Content-type: application/json');

$metadataStorageHandler = MetaDataStorageHandler::getMetadataHandler();
$metadata = $metadataStorageHandler->getList('metadata-edugain/saml20-idp-remote');

$searchTerm = $_GET['q'];
$filteredData = [];

foreach ($metadata as $idpentry) {
    if (is_array($idpentry['name'])) {
        foreach ($idpentry['name'] as $key => $value) {
            if (str_contains(strtolower($value), strtolower($searchTerm))) {
                array_push($filteredData, $idpentry);
                break;
            }
        }
    }

    if (false === array_search($idpentry, $filteredData) && is_array($idpentry['description'])) {
        foreach ($idpentry['description'] as $key => $value) {
            if (str_contains(strtolower($value), strtolower($searchTerm))) {
                array_push($filteredData, $idpentry);
                break;
            }
        }
    }

    if (false === array_search($idpentry, $filteredData) && is_array($idpentry['url'])) {
        foreach ($idpentry['url'] as $key => $value) {
            if (str_contains(strtolower($value), strtolower($searchTerm))) {
                array_push($filteredData, $idpentry);
                break;
            }
        }
    }
}

$data['items'] = [];
$id = 0;

foreach ($filteredData as $idpentry) {
    $item['id'] = $id;
    $item['idpentityid'] = $idpentry['entityid'];
    $item['text'] = $idpentry['name']['en'];

    if (!empty($idpentry['UIInfo']['Logo'])) {
        if (1 === count($idpentry['UIInfo']['Logo'])) {
            $item['image'] = $idpentry['UIInfo']['Logo'][0]['url'];
        } else {
            $logoSizeRatio = 1; // impossible value
            $candidateLogoUrl = null;

            foreach ($idpentry['UIInfo']['Logo'] as $logo) {
                $ratio = abs($logo['height'] - $logo['width']) / ($logo['height'] + $logo['width']);

                if ($ratio < $logoSizeRatio) { // then we found more square-like logo
                    $logoSizeRatio = $ratio;
                    $candidateLogoUrl = $logo['url'];
                }
            }

            $item['image'] = $candidateLogoUrl;
        }
    }

    array_push($data['items'], $item);
    $id++;
}

echo json_encode($data);
exit();
