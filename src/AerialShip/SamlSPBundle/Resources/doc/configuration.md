
security.xml
------------

    security:
        firewalls:
            saml:
                pattern: ^/
                logout: true
                anonymous: true
                aerial_ship_saml_sp:
                    sp:
                        entity_id: http://localhost/aerial/test/web/app_dev.php
                        want_assertions_signed: true
                    login_path: /saml/login
                    check_path: /saml/acs
                    logout_path: /saml/logout
                    logout_receive_path: /saml/logout_receive
                    failure_path: /saml/failure
                    metadata_path: /saml/FederationMetadata.xml
                    discovery_path: /saml/discovery
                    default_target_path: /
                    provider: in_memory
                    create_user_if_not_exists: false
                    services:
                        azure:
                            idp:
                                file: "@AerialShipSamlTestBundle/Resources/azure-FederationMetadata.xml"
                            sp:
                                name_id_format: persistent
                                binding:
                                    authn_request: post
                        b1:
                            idp:
                                file: "@AerialShipSamlTestBundle/Resources/b1-FederationMetadata.xml"
                            sp:
                                name_id_format: persistent
                                binding:
                                    authn_request: post
                logout:
                    path: /logout
                    target: /
                    invalidate_session: false


        access_control:
            - { path: ^/login_saml, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/login_check_saml, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/admin, roles: ROLE_USER }
            #- { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }
