SamlSpBundle Getting Started
============================

Prerequisites
-------------

This version of bundle requires Symfony 2.2+


Installation
------------

Installation is quick 4 steps:

1. Download SamlSpBundle with composer
2. Enable the bundle
3. Create your SSO State Entity class
4. Configure SamlSpBundle
5. Configure application's security.yml
6. Import SamlSpBundle routing


Step 1: Download SamlSpBundle with composer
-------------------------------------------

Add SamlSpBundle to your composer.json requirements:

```js
{
    "require": {
        "aerialship/saml-sp-bundle": "dev-master"
    }
}
```

And run composer to download the bundle with the command

``` bash
    $ php composer.phar update aerialship/saml-sp-bundle
```

Composer will install the bundle the the `vendor/aerialship/saml-sp-bundle` directory of your project


Step 2: Enable the bundle
-------------------------

Add the SamlSpBundle the the kernel of your project:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new AerialShip\SamlSPBundle\AerialShipSamlSPBundle(),
    );
}
```


Step 3: Create your SSO State Entity class
------------------------------------------

Bundle has to persist SSO State of the user authenticated against IDP so when IDP calls for logout in another session
it is possible to delete that session, so it can logout the user from your app once he comes back. At this version
of the bundle only doctrine orm driver is supported. You need to create entity class for it in your project by
extending `AerialShip\SamlSPBundle\Entity\SSOStateEntity` class.

For example:

``` php
<?php

namespace Acme\SamlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="saml_sso_state")
 */
class SSOState extends \AerialShip\SamlSPBundle\Entity\SSOStateEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }


}
```

After the entity class is created you should update your database schema by running

``` bash
$ php app/console doctrine:schema:update --force
```


Step 4: Configure SamlSpBundle
------------------------------

Now you have to tell to the Bundle what's your entity class

``` yaml
# app/config/config.yml
aerial_ship_saml_sp:
    driver: orm
    sso_state_entity_class: Acme\SamlBundle\Entity\SSOState

```


Step 5: Configure application's security.yml
--------------------------------------------

In order Symfony's security component to use the SamlSpBundle you must configure it in the `security.yml` file by
adding a firewall with `aerial_ship_saml_sp` configuration. Here's the minimal configuration:

``` yaml
# app/config/security.yml
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    providers:
        in_memory:
            memory:
                users:
                    user:  { password: userpass, roles: [ 'ROLE_USER' ] }
                    admin: { password: adminpass, roles: [ 'ROLE_ADMIN' ] }

    firewalls:
        saml:
            pattern: ^/
            anonymous: true
            aerial_ship_saml_sp:
                local_logout_path: /logout
                provider: in_memory
                services:
                    somename:
                        idp:
                            file: "@AcmeSamlBundle/Resources/idp-FederationMetadata.xml"
                        sp:
                            config:
                                entity_id: https://mysite.com/
            logout:
                path: /logout

    access_control:
        - { path: ^/secure, roles: ROLE_USER }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

Full configuration you can see at [Configuration Reference](configuration.md).
For details about user provider check the [User Provider](user_provider.md) documentation.


Step 6: Import SamlSpBundle routing
-----------------------------------

You need to import routing files with default paths for SAML login, assertion consumer, logout, discovery and metadata.

``` yml
# app/config/routing.yml

aerialship_saml_sp_bundle:
    resource: "@AerialShipSamlSPBundle/Resources/config/routing.yml"

```

**Note:**

> If you are changing default paths for the saml sp listener then you would need to ensure those paths
> are defined in the routing and you would need to do that yourself since only default paths are defined
> in the SamlSpBundle routing.


Step 7: Exchange metadata
-------------------------

Download your SP metadata by visiting the (configurable) URL `/saml/sp/FederationMetadata.xml` and send the file to the IdP. Save the IdP metadata in your bundle at the configured location (e.g. `@AcmeSamlBundle/Resources/idp-FederationMetadata.xml`).


Next Steps
----------

This document explains basic setup of the SamlSpBundle, after which you can learn about more advanced features
and usages of the bundle.

Following documents are available:

* [Configuration Reference](configuration.md)
* [User Provider](user_provider.md)
* [Certificates and Signing](signing_and_certificates.md)
