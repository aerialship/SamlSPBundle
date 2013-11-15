<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\NameIDPolicy;
use Symfony\Component\HttpFoundation\Request;


class SpMetaConfigProvider implements SpMetaProviderInterface
{
    /** @var  SpMeta */
    protected $spMeta;

    function __construct(array $config) {
        $this->spMeta = new SpMeta();
        if (isset($config['name_id_format'])) {
            $value = $config['name_id_format'];
            if ($value == 'persistent') {
                $value = NameIDPolicy::PERSISTENT;
            } else if ($value == 'transient') {
                $value = NameIDPolicy::TRANSIENT;
            }
            $this->spMeta->setNameIdFormat($value);
        }
        if (isset($config['binding']['authn_request'])) {
            $value = $config['binding']['authn_request'];
            if ($value == 'post') {
                $value = Bindings::SAML2_HTTP_POST;
            } else if ($value == 'redirect') {
                $value = Bindings::SAML2_HTTP_REDIRECT;
            }
            $this->spMeta->setAuthnRequestBinding($value);
        }
    }

    /**
     * @param Request $request
     * @return SpMeta
     */
    public function getSpMeta(Request $request) {
        return $this->spMeta;
    }

} 