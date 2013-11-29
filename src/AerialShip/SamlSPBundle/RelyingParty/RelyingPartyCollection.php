<?php

namespace AerialShip\SamlSPBundle\RelyingParty;

use Symfony\Component\HttpFoundation\Request;


class RelyingPartyCollection implements RelyingPartyInterface
{
    /** @var RelyingPartyInterface[] */
    protected $relyingParties = array();

    /**
     * @param RelyingPartyInterface $relyingParty
     */
    public function append(RelyingPartyInterface $relyingParty) {
        array_push($this->relyingParties, $relyingParty);
    }

    /**
     * @param RelyingPartyInterface $relyingParty
     */
    public function prepend(RelyingPartyInterface $relyingParty) {
        array_unshift($this->relyingParties, $relyingParty);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request) {
        return (bool) $this->findRelyingPartySupportedRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function manage(Request $request) {
        if (false == $relyingParty = $this->findRelyingPartySupportedRequest($request)) {
            throw new \InvalidArgumentException('The relying party does not support the request');
        }
        return $relyingParty->manage($request);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return RelyingPartyInterface|null
     */
    protected function findRelyingPartySupportedRequest(Request $request) {
        foreach ($this->relyingParties as $relyingParty) {
            if ($relyingParty->supports($request)) {
                return $relyingParty;
            }
        }
        return null;
    }
}