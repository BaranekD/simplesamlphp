<?php

use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\campusMultiauth\Auth\Source\Campusidp;
use SimpleSAML\XHTML\Template;

if (!array_key_exists('AuthState', $_REQUEST) && !array_key_exists('authstate', $_POST)) {
    throw new BadRequest('Missing AuthState parameter.');
}

empty($_REQUEST['AuthState']) ? $authStateId = $_POST['authstate'] : $authStateId = $_REQUEST['AuthState'];
$state = State::loadState($authStateId, Campusidp::STAGEID_USERPASS);

if (array_key_exists('source', $_POST)) {
    if (
        (array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) ||
        array_key_exists('idpentityid', $_POST)
    ) {
        Campusidp::delegateAuthentication($_POST['source'], $state);
    }
}

$metadataStorageHandler = MetaDataStorageHandler::getMetadataHandler();
$wayfConfig = $state['wayf_config'];

$metadata = $metadataStorageHandler->getList('metadata-edugain/saml20-idp-remote');
$metadata = array_diff_key($metadata, array_flip((array) $wayfConfig['idps']['exclude']));

$data = [];
$id = 0;

foreach ($metadata as $idpentry) {
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

    array_push($data, $item);
    $id++;
}

$globalConfig = Configuration::getInstance();
$t = new Template($globalConfig, 'campusMultiauth:selectsource.php');

array_key_exists('wrongUserPass', $_REQUEST) ? $t->data['wrongUserPass'] = true : $t->data['wrongUserPass'] = false;
$t->data['authstate'] = $authStateId;
$t->data['currentUrl'] = htmlentities($_SERVER['PHP_SELF']);
$t->data['metadata'] = $data;
$t->data['wayf_config'] = $state['wayf_config'];

$t->show();
exit();
