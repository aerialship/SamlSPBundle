UPGRADE FROM 1.0 TO 1.1
=======================

1.1.0
-----

 * Minimum supported symofny version is 2.3
 * Mongodb support for SSO state added
 * ``AerialShip\SamlSPBundle\State\SSO\SSOState`` is deprecated and moved to ``AerialShip\SamlSPBundle\Model\Model``
 * ``SSOState`` is made abstract
 * ``SSOState`` doctrine mappings are changed from annotations to xml
 * ``SSOState`` xml doctrine mappings for odm and mongodb
 * Bundle config ``model_manager_name`` added to support other doctrine managers than default one
 * ``AerialShip\SamlSPBundle\Entity\SSOStateStoreManager`` is deprecated and abstracted to ``AerialShip\SamlSPBundle\Doctrine\SSOStateStore``
 * Usage of parameters for service class is deprecated
