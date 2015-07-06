SamlSPBundle
============

[![Build Status](https://travis-ci.org/aerialship/SamlSPBundle.png)](https://travis-ci.org/aerialship/SamlSPBundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/aerialship/SamlSPBundle/badges/quality-score.png?s=ea75a9e869bb19543fb0ab9530f63010d8a8da95)](https://scrutinizer-ci.com/g/aerialship/SamlSPBundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/aerialship/SamlSPBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/aerialship/SamlSPBundle/?branch=master)
[![Sensio Insight](https://insight.sensiolabs.com/projects/1f623314-4a14-4d77-bcbf-03f4be8a711a/small.png)](https://insight.sensiolabs.com/projects/1f623314-4a14-4d77-bcbf-03f4be8a711a)
[![HHVM Status](http://hhvm.h4cc.de/badge/aerialship/saml-sp-bundle.svg)](http://hhvm.h4cc.de/package/aerialship/saml-sp-bundle)

The SamlSpBundle adds support for SAML 2.0 Service Provider in Symfony2. It provides security listener
that can be configured to authenticate users against one or more SAML Identity Providers.

Included features:
* Federation Metadata XML
* Discovery Service
* AuthnRequest / Single Sign On Login
* LogoutRequest / Single Logout
* Http Post and Http Redirect Bindings


Documentation
-------------

[Getting Started](src/AerialShip/SamlSPBundle/Resources/doc/index.md)

[Configuration Reference](src/AerialShip/SamlSPBundle/Resources/doc/configuration.md)

[Configuring/Implementing User Provider](src/AerialShip/SamlSPBundle/Resources/doc/user_provider.md)


CONTRIBUTING
------------
SamlSpBundle is an open source project and is open for contributions. Please follow guidelines from [Contributing and collaboration](https://github.com/aerialship/SamlSPBundle/wiki/Contributing-and-collaboration) wiki page.


Credits
------

Thanks to [FriendsOfSymfony/FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) and [formapro/FpOpenIdBundle](https://github.com/formapro/FpOpenIdBundle) open source projects that helped understand better how Symfony2 security works and learn how custom security extensions should be built.
