<?php

namespace SimpleSAML\Module\campusUserPass\Auth\Source;

use SimpleSAML\Auth\State;
use SimpleSAML\Error\Error;
use SimpleSAML\Module;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Utils\Attributes;
use SimpleSAML\Utils\HTTP;

class UserPassAuth extends UserPassBase
{
    public function __construct($info, $config)
    {
        parent::__construct($info, $config);

        $this->users = [];

        // Validate and parse our configuration
        foreach ($config['users'] as $userpass => $attributes) {
            if (!is_string($userpass)) {
                throw new \Exception(
                    'Invalid <username>:<password> for authentication source ' . $this->authId . ': ' . $userpass
                );
            }

            $userpass = explode(':', $userpass, 2);
            if (count($userpass) !== 2) {
                throw new \Exception(
                    'Invalid <username>:<password> for authentication source ' . $this->authId . ': ' . $userpass[0]
                );
            }
            $username = $userpass[0];
            $password = $userpass[1];

            try {
                $attributes = Attributes::normalizeAttributesArray($attributes);
            } catch (\Exception $e) {
                throw new \Exception('Invalid attributes for user ' . $username .
                    ' in authentication source ' . $this->authId . ': ' . $e->getMessage());
            }
            $this->users[$username . ':' . $password] = $attributes;
        }
    }

    public function authenticate(&$state)
    {
        $state[self::AUTHID] = $this->authId;
        $id = State::saveState($state, self::STAGEID);

        $url = Module::getModuleURL('campusUserPass/campusUserPass.php');
        $params = ['AuthState' => $id];

        if (isset($_POST['username']) && isset($_POST['password'])) {
            $params['username'] = $_POST['username'];
            $params['password'] = $_POST['password'];
        }

        HTTP::redirectTrustedURL($url, $params);
    }

    protected function login($username, $password)
    {
        $userpass = $username . ':' . $password;
        if (!array_key_exists($userpass, $this->users)) {
            throw new Error('WRONGUSERPASS');
        }

        return $this->users[$userpass];
    }
}
