<?php

namespace AerialShip\SamlSPBundle\RelyingParty;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use Symfony\Component\HttpFoundation\Request;

interface RelyingPartyInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo|null
     */
    public function manage(Request $request);
}
