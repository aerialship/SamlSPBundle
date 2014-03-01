security.xml
------------

    security:
        firewalls:
            saml:
                pattern: ^/
                anonymous: true
                aerial_ship_saml_sp:
                    login_path: /saml/sp/login
                    check_path: /saml/sp/acs
                    logout_path: /saml/sp/logout
                    failure_path: /saml/sp/failure
                    metadata_path: /saml/sp/FederationMetadata.xml
                    discovery_path: /saml/sp/discovery
                    # required - when saml logout is done it's redirected to this path that's supposed to be handled by std symfony logout
                    local_logout_path: /logout
                    default_target_path: /
                    # should implement UserManagerInterface
                    # if UserProviderInterface then it's wrapped by service aerial_ship_saml_sp.user_provider_adapter.class
                    provider: in_memory
                    create_user_if_not_exists: false
                    services:
                        somename:
                            idp:
                                # must implement EntityDescriptorProviderInterface
                                id: my.idp.ed.provider.service.id

                                # or use builtin EntityDescriptorFileProvider with specific file
                                file: "@AerialShipSamlTestBundle/Resources/azure-FederationMetadata.xml"
                                # in case of EntitiesDescriptor entity_id of the EntityDescriptor to use
                                entity_id: https://some.com/entity_id
                            sp:
                                config:
                                    # required
                                    entity_id: http://mysite.com/
                                    # if different then url being used in request
                                    # used for construction of assertion consumer and logout urls in SP entity descriptor
                                    base_url: https://100.200.100.200/
                                    want_assertions_signed: false
                                signing:
                                    # must implement SPSigningProviderInterface
                                    id: my.signing.provider.service.id

                                    # or use built in SPSigningProviderFile with specific certificate and key files
                                    cert_file: "@AerialShipSamlTestBundle/Resources/saml.crt"
                                    key_file: "@AerialShipSamlTestBundle/Resources/saml.pem"
                                    key_pass: ""
                                meta:
                                    # must implement SpMetaProviderInterface
                                    id: my.sp.provider.service.id

                                    # or use builtin SpMetaConfigProvider
                                    # any valid saml name id format or shortcuts: persistent or transient
                                    name_id_format: persistent
                                    binding:
                                        # any saml binding or shortcuts: post or redirect
                                        authn_request: redirect
                                        response: post
                                        logout_request: redirect
                # required for saml logout, set the same path to local_logout_path
                logout:
                    path: /logout
                    target: /
                    invalidate_session: false

        access_control:
            - { path: ^/saml/sp/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/saml/sp/acs, roles: IS_AUTHENTICATED_ANONYMOUSLY }
            - { path: ^/admin, roles: ROLE_USER }
