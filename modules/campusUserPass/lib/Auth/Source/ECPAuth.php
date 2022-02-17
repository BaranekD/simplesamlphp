<?php

declare(strict_types=1);

namespace SimpleSAML\Module\campusUserPass\Auth\Source;

use SAML2\Assertion;
use SAML2\DOMDocumentFactory;
use SAML2\Message;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;
use SimpleSAML\Auth\Source;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\AuthSource;
use SimpleSAML\Error\Error;
use SimpleSAML\Error\Exception;
use SimpleSAML\Logger;
use SimpleSAML\Module;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Store;
use SimpleSAML\Utils\HTTP;
use SimpleXMLElement;

class ECPAuth extends UserPassBase
{
    private $sp;
    private $ecpIdpUrl;

    public function __construct($info, $config)
    {
        parent::__construct($info, $config);

        $this->sp = $config['sp'];
        if (empty($this->sp)) {
            throw new Exception('Missing mandatory configuration option \'sp\'.');
        }

        $this->ecpIdpUrl = $config['ecpIdpUrl'];
        if (empty($this->ecpIdpUrl)) {
            throw new Exception('Missing mandatory configuration option \'ecpIdpUrl\'.');
        }

        $this->expectedIssuer = $config['expectedIssuer'];
        if (empty($this->expectedIssuer)) {
            throw new Exception('Missing mandatory configuration option \'expectedIssuer\'.');
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
        $source = Source::getById($this->sp);
        if ($source === null) {
            throw new AuthSource($this->sp, 'Could not find authentication source.');
        }

        $spMetadata = $source->getMetadata();

        $xml = new SimpleXMLElement(
            '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"></S:Envelope>'
        );

        $xmlBody = $xml->addChild('S:Body');
        $xmlRequest = $xmlBody->addChild(
            'samlp:AuthnRequest',
            null,
            'urn:oasis:names:tc:SAML:2.0:protocol'
        );
        $xmlRequest->addAttribute('ID', '' . rand() . '');
        $xmlRequest->addAttribute('IssueInstant', '' . gmdate('Y-m-d\TH:i:s\Z', time()) . '');
        $xmlRequest->addAttribute('Version', '2.0');
        $xmlRequest->addAttribute('AssertionConsumerServiceIndex', '2');

        $xmlRequest->addChild(
            'saml:Issuer',
            $spMetadata->getString('entityid'),
            'urn:oasis:names:tc:SAML:2.0:assertion'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->ecpIdpUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml->asXML());
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $document = DOMDocumentFactory::fromString($response);
        $xml = $document->firstChild;
        $results = Utils::xpQuery($xml, '/soap-env:Envelope/soap-env:Body/*[1]');
        $response = Message::fromXML($results[0]);

        $issuer = $response->getIssuer();
        if ($issuer === null) {
            foreach ($response->getAssertions() as $a) {
                if ($a instanceof Assertion) {
                    $issuer = $a->getIssuer();
                    break;
                }
            }
            if ($issuer === null) {
                throw new Exception('Missing <saml:Issuer> in message delivered to AssertionConsumerService.');
            }
        }
        if ($issuer instanceof Issuer) {
            $issuer = $issuer->getValue();
            if ($issuer === null) {
                throw new Exception('Missing <saml:Issuer> in message delivered to AssertionConsumerService.');
            }
        }
        if ($this->expectedIssuer !== $issuer) {
            throw new Exception('Unexpected issuer in the ECP response');
        }

        $idpMetadata = $source->getIdPmetadata($issuer);
        Logger::debug('Received SAML2 Response from ' . var_export($issuer, true) . '.');

        try {
            $assertions = Module\saml\Message::processResponse($spMetadata, $idpMetadata, $response);
        } catch (Module\saml\Error $e) {
            if (str_contains($e->getMessage(), 'WRONGUSERPASS')) {
                throw new Error('WRONGUSERPASS');
            }

            throw new Exception('Error while processing the response: ' . $e);
        }

        $attributes = [];
        foreach ($assertions as $assertion) {
            // check for duplicate assertion (replay attack)
            $store = Store::getInstance();
            if ($store !== false) {
                $aID = $assertion->getId();
                if ($store->get('saml.AssertionReceived', $aID) !== null) {
                    throw new Exception('Received duplicate assertion.');
                }

                $notOnOrAfter = $assertion->getNotOnOrAfter();
                if ($notOnOrAfter === null) {
                    $notOnOrAfter = time() + 24 * 60 * 60;
                } else {
                    $notOnOrAfter += 60; // we allow 60 seconds clock skew, so add it here also
                }

                $store->set('saml.AssertionReceived', $aID, true, $notOnOrAfter);
            }

            $attributes = array_merge($attributes, $assertion->getAttributes());
        }

        return $attributes;
    }
}
