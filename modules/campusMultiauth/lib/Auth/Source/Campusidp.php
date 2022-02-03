<?php

declare(strict_types=1);

namespace SimpleSAML\Module\campusMultiauth\Auth\Source;

use Exception;
use SimpleSAML\Auth;
use SimpleSAML\Auth\Source;
use SimpleSAML\Error\UnserializableException;
use SimpleSAML\Module;
use SimpleSAML\Session;
use SimpleSAML\Utils;

class Campusidp extends Source
{
    public const AUTHID = '\SimpleSAML\Module\campusidp\Auth\Source\Campusidp.AuthId';
    public const STAGEID_USERPASS = '\SimpleSAML\Module\core\Auth\UserPassBase.state';
    public const SOURCESID = '\SimpleSAML\Module\campusidp\Auth\Source\Campusidp.SourceId';
    public const SESSION_SOURCE = 'campusMultiauth:selectedSource';
    public const WAYF_CONFIG = 'wayf_config';

    private $sources;
    private $wayfConfig;


    public function __construct($info, $config)
    {
        parent::__construct($info, $config);

        if (!array_key_exists('sources', $config)) {
            throw new Exception('The required "sources" config option was not found');
        }

        $this->sources = [];

        $sources = $config['sources'];
        foreach ($sources as $source => $info) {
            $class_ref = [];
            if (array_key_exists('AuthnContextClassRef', $info)) {
                $ref = $info['AuthnContextClassRef'];
                if (is_string($ref)) {
                    $class_ref = [$ref];
                } else {
                    $class_ref = $ref;
                }
            }

            $this->sources[] = [
                'source' => $source,
                'AuthnContextClassRef' => $class_ref,
            ];
        }

        $this->wayfConfig = $config[self::WAYF_CONFIG];
        if (empty($this->wayfConfig)) {
            throw new Exception('Missing configuration option \'' . self::WAYF_CONFIG . '\'.');
        }
    }

    public function authenticate(&$state)
    {
        $state[self::AUTHID] = $this->authId;
        $state[self::SOURCESID] = $this->sources;
        $state[self::WAYF_CONFIG] = $this->wayfConfig;

        // Save the $state array, so that we can restore if after a redirect
        $id = Auth\State::saveState($state, self::STAGEID_USERPASS);

        /* Redirect to the select source page. We include the identifier of the
         * saved state array as a parameter to the login form
         */
        $url = Module::getModuleURL('campusMultiauth/selectsource.php');
        $params = ['AuthState' => $id];

        Utils\HTTP::redirectTrustedURL($url, $params);

        // The previous function never returns, so this code is never executed
        assert(false);
    }

    public static function delegateAuthentication($authId, $state)
    {
        $as = Auth\Source::getById($authId);
        $valid_sources = array_map(
            function ($src) {
                return $src['source'];
            },
            $state[self::SOURCESID]
        );
        if ($as === null || !in_array($authId, $valid_sources, true)) {
            throw new Exception('Invalid authentication source: ' . $authId);
        }

        // Save the selected authentication source for the logout process.
        $session = Session::getSessionFromRequest();
        $session->setData(
            self::SESSION_SOURCE,
            $state[self::AUTHID],
            $authId,
            Session::DATA_TIMEOUT_SESSION_END
        );

        try {
            $as->authenticate($state);
        } catch (Error\Exception $e) {
            Auth\State::throwException($state, $e);
        } catch (Exception $e) {
            $e = new UnserializableException($e);
            Auth\State::throwException($state, $e);
        }
        Auth\Source::completeAuth($state);
    }

    public function logout(&$state)
    {
        assert(is_array($state));

        // Get the source that was used to authenticate
        $session = Session::getSessionFromRequest();
        $authId = $session->getData(self::SESSION_SOURCE, $this->authId);

        $source = Auth\Source::getById($authId);
        if ($source === null) {
            throw new Exception('Invalid authentication source during logout: ' . $authId);
        }
        // Then, do the logout on it
        $source->logout($state);
    }
}
