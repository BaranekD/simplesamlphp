<?php

namespace SimpleSAML\Module\campusidp\Auth\Source;

use SimpleSAML\Configuration;
use SimpleSAML\Module;
use SimpleSAML\Module\saml\Error\NoAuthnContext;
use SimpleSAML\Session;
use SimpleSAML\Auth;
use SAML2\Constants;
use SimpleSAML\Error;
use SimpleSAML\Utils;

class CampusIdp extends \SimpleSAML\Auth\Source
{
    public const STAGEID = '\SimpleSAML\Module\campusidp\Auth\Source\CampusIdp.StageId';
    public const SOURCESID = '\SimpleSAML\Module\campusidp\Auth\Source\CampusIdp.SourceId';

    private $info;
    private $config;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info Information about this authentication source.
     * @param array $config Configuration.
     */
    public function __construct($info, $config)
    {
        assert(is_array($info));
        assert(is_array($config));

        $this->info = $info;
        $this->config = $config;

        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);
    }

    /**
     * This method saves the information about the configured sources,
     * and redirects to a page where the user must select one of these
     * authentication sources.
     *
     * This method never return. The authentication process is finished
     * in the delegateAuthentication method.
     *
     * @param array &$state Information about the current authentication.
     * @return void
     */
    public function authenticate(&$state)
    {
        $state['info'] = $this->info;
        $state['config'] = $this->config;

        $id = Auth\State::saveState($state, self::STAGEID);

        $url = Module::getModuleURL('campusidp/selectsource.php');
        $params = ['AuthState' => $id];

        Utils\HTTP::redirectTrustedURL($url, $params);
    }
}
