
security.xml
------------

security:
    firewalls:
        saml:
            pattern: ^/
            logout: true
            anonymous: true
            aerial_ship_saml_sp:
                login_path: /login_saml
                check_path: /login_check_saml
                logout_path: /logout_saml
                provider: in_memory
                entity_descriptor:
                    sp:
                        file: "@AerialShipSamlTestBundle/Resources/sp.xml"
                    idp:
                        file: "@AerialShipSamlTestBundle/Resources/b1-FederationMetadata.xml"
                sp_meta:
                    config:
                        name_id_format: persistent
                        binding:
                            authn_request: redirect
            logout:
                path: /logout
                target: /
                invalidate_session: false
            anonymous:    true

    access_control:
        - { path: ^/login_saml, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/login_check_saml, roles: IS_AUTHENTICATED_ANONYMOUSLY }
