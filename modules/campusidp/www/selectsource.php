<?php

if (!array_key_exists('AuthState', $_REQUEST) && !array_key_exists('authstate', $_POST)) {
    throw new \SimpleSAML\Error\BadRequest('Missing AuthState parameter.');
}

$_REQUEST['AuthState'] === null ? $authStateId = $_POST['authstate'] : $authStateId = $_REQUEST['AuthState'];
$state = \SimpleSAML\Auth\State::loadState($authStateId, \SimpleSAML\Module\campusidp\Auth\Source\CampusIdp::STAGEID);

if (!array_key_exists('info', $state)) {
    throw new \SimpleSAML\Error\BadRequest('Missing info parameter.');
}

if (!array_key_exists('config', $state)) {
    throw new \SimpleSAML\Error\BadRequest('Missing config parameter.');
}

if (array_key_exists('source', $_POST)) {
    if ((array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) || array_key_exists('idpentityid', $_POST)) {
        $multiauth = new \SimpleSAML\Module\multiauth\Auth\Source\MultiAuth($state['info'], $state['config']);
        $multiauth->authenticate($state);
    }
}

$globalConfig = \SimpleSAML\Configuration::getInstance();
$t = new \SimpleSAML\XHTML\Template($globalConfig, 'campusidp:selectsource.tpl.php');

$t->data['authstate'] = $authStateId;

$t->show();
exit();
