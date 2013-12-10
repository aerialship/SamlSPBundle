<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\NameIDPolicy;


class SpMetaConfigProvider implements SpMetaProviderInterface
{
    /** @var  SpMeta */
    protected $spMeta;


    public function __construct(array $config)
    {
        $this->spMeta = new SpMeta();
        $this->spMeta->setNameIdFormat($this->resolveNameIDFormat($config['name_id_format']));
        $this->spMeta->setAuthnRequestBinding($this->resolveBinding($config['binding']['authn_request']));
        $this->spMeta->setLogoutRequestBinding($this->resolveBinding($config['binding']['logout_request']));
    }



    /**
     * @return SpMeta
     */
    public function getSpMeta() {
        return $this->spMeta;
    }


    /**
     * @param $value
     * @return string
     */
    protected function resolveNameIDFormat($value)
    {
        switch ($value) {
            case 'persistent':
                $result = NameIDPolicy::PERSISTENT; break;
            case 'transient':
                $result = NameIDPolicy::TRANSIENT; break;
            default:
                $result = $value;
        }
        return $result;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function resolveBinding($value)
    {
        switch ($value) {
            case 'post':
                $result = Bindings::SAML2_HTTP_POST; break;
            case 'redirect':
                $result = Bindings::SAML2_HTTP_REDIRECT; break;
            default:
                $result = $value;
        }
        return $result;
    }

} 