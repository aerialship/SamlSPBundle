SIGNING AND CERTIFICATES
========================

SamlSpBundle relies on [LightSaml](https://github.com/aerialship/lightsaml) SAML 2.0 implementation in PHP which also
handles all signing and certificate features.

[LightSaml documentation about signing and certificates](https://github.com/aerialship/lightsaml/blob/master/doc/signing_and_certificates.md)

The SamlSpBundle relies on the service implementing SPSigningProviderInterface. The bundle comes with builtin service
that will provide certificate and key from files specified in the security firewall config. For advanced usage (for
example when certificate and key are not in files on disk) you can specify you own service id that is implementing
the SPSigningProviderInterface.



Creating self signed certificate
--------------------------------

Run the following command to generate pem certificate and key

``` bash
$ openssl req -new -x509 -days 3652 -nodes -out saml.crt -keyout saml.pem
```





