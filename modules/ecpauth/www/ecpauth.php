<?php

use SimpleSAML\Auth\Source;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Module;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Module\ecpauth\Auth\Source\ECPAuth;
use SimpleSAML\Utils\HTTP;

if (!array_key_exists('AuthState', $_REQUEST)) {
    throw new BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['AuthState'];

$state = State::loadState($authStateId, UserPassBase::STAGEID);

$source = Source::getById($state[ECPAuth::AUTHID]);
if ($source === null) {
    throw new \Exception(
        'Could not find authentication source with id ' . $state[UserPassBase::AUTHID]
    );
}

if (array_key_exists('username', $_REQUEST)) {
    $username = $_REQUEST['username'];
} elseif ($source->getRememberUsernameEnabled() && array_key_exists($source->getAuthId() . '-username', $_COOKIE)) {
    $username = $_COOKIE[$source->getAuthId() . '-username'];
} elseif (isset($state['core:username'])) {
    $username = (string) $state['core:username'];
} else {
    $username = '';
}

if (array_key_exists('password', $_REQUEST)) {
    $password = $_REQUEST['password'];
} else {
    $password = '';
}

$errorCode = null;
$errorParams = null;
$queryParams = [];

if (isset($state['error'])) {
    $errorCode = $state['error']['code'];
    $errorParams = $state['error']['params'];
    $queryParams = ['AuthState' => $authStateId];
}

if (!empty($_REQUEST['username']) || !empty($password)) {
    // Either username or password set - attempt to log in

    if (array_key_exists('forcedUsername', $state)) {
        $username = $state['forcedUsername'];
    }

    if ($source->getRememberUsernameEnabled()) {
        $sessionHandler = \SimpleSAML\SessionHandler::getSessionHandler();
        $params = $sessionHandler->getCookieParams();

        if (isset($_REQUEST['remember_username']) && $_REQUEST['remember_username'] == 'Yes') {
            $params['expire'] = time() + 31536000;
        } else {
            $params['expire'] = time() - 300;
        }
        HTTP::setCookie($source->getAuthId() . '-username', $username, $params, false);
    }

    if ($source->isRememberMeEnabled()) {
        if (array_key_exists('remember_me', $_REQUEST) && $_REQUEST['remember_me'] === 'Yes') {
            $state['RememberMe'] = true;
            $authStateId = State::saveState(
                $state,
                UserPassBase::STAGEID
            );
        }
    }

    try {
        UserPassBase::handleLogin($authStateId, $username, $password);
    } catch (\SimpleSAML\Error\Error $e) {
        // Login failed. Extract error code and parameters, to display the error
        $errorCode = $e->getErrorCode();
        $errorParams = $e->getParameters();
        $state['error'] = [
            'code' => $errorCode,
            'params' => $errorParams
        ];
        $authStateId = State::saveState($state, UserPassBase::STAGEID);
        $queryParams = ['AuthState' => $authStateId];
    }
    if (isset($state['error'])) {
        unset($state['error']);
    }
}

$url = Module::getModuleURL('campusidp/selectsource.php');
HTTP::redirectTrustedURL($url, ['AuthState' => $authStateId, 'wrongUserPass' => true]);
