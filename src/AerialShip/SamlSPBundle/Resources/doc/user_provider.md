USER PROVIDER
=============

There are several ways to use user provider.

UserManagerInterface
---------------------

Implement UserManagerInterface in the provider class given to the firewall.

Initially authentication provider calls `loadUserBySamlInfo(SamlSpInfo $samlInfo)` method that's supposed to
return the `UserInterface` or throw `UsernameNotFoundException`. If exception is thrown and `create_user_if_not_exists`
config is true authentication provider will call `createUserFromSamlInfo(SamlSpInfo $samlInfo)` method.

```yml
security:
    providers:
        saml_user_provider:
            id: my.custom.service # implements UserManagerInterface
    firewalls:
        saml:
            pattern: ^/
                provider: saml_user_provider
```

UserProviderInterface
--------------------
If a user provider given to the firewall does not implement `UserManagerInterface` then adapter service is used to
wrap provided `UserProviderInterface` and map SamlSpInfo to the username. Default adapter implementation
is using nameID as username.

```yml
security:
    providers:
        in_memory: # implements only UserProviderInterface
            memory:
                users:
                    user:  { password: userpass, roles: [ 'ROLE_USER' ] }
                    admin: { password: adminpass, roles: [ 'ROLE_ADMIN' ] }
    firewalls:
        saml:
            pattern: ^/
                provider: in_memory
```


Custom User Provider Adapter
----------------------------

You can provide your own user provider adapter by setting the value of the container parameter
`aerial_ship_saml_sp.user_provider_adapter.class` to your class that implements `UserManagerInterface`.
As the first constructor argument the user provider from the firewall is given.

```yml
parameters:
    aerial_ship_saml_sp.user_provider_adapter.class: My\Adapter\That\Implements\UserManagerInterface\Class
```