<?php

use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\campusidp\Auth\Source\Campusidp;
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
        Logger::debug('PPPPPPPPPPPP ' . print_r($_POST['idpentityid'], true));
        Campusidp::delegateAuthentication($_POST['source'], $state);
    }
}

$globalConfig = Configuration::getInstance();
$t = new Template($globalConfig, 'campusidp:selectsource.php');

$metadataStorageHandler = MetaDataStorageHandler::getMetadataHandler();

array_key_exists('wrongUserPass', $_REQUEST) ? $t->data['wrongUserPass'] = true : $t->data['wrongUserPass'] = false;
$t->data['authstate'] = $authStateId;
$t->data['currentUrl'] = htmlentities($_SERVER['PHP_SELF']);
$t->data['metadata'] = $metadataStorageHandler->getList('metadata-edugain/saml20-idp-remote');

$t->show();
exit();

//public function buildContinueUrl(
//    string $entityID,
//    string $return,
//    string $returnIDParam,
//    string $idpEntityId
//): string {
//    return '?' .
//        'entityID=' . urlencode($entityID) . '&' . // https://login.elixir-czech.org/proxy/
//        'return=' . urlencode($return) . '&' . // https://login.elixir-czech.org/proxy/module.php/saml/sp/discoresp.php?AuthID=_5f4140722ab4b1ebdd7969f2563ba0336624dacde2
//        'returnIDParam=' . urlencode($returnIDParam) . '&' . // idpentityid
//        'idpentityid=' . urlencode($idpEntityId);
//}
