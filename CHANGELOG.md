# Changelog

## v1.1
* Fixed the aro alias naming.
* Updated version requirement to ~1.0 for latest cakephp/plugin-installer.
* Updating docs to use correct config param for setting admin prefix.

## v1.0.5
* Fixed bug on "Update ACOs". Now use AclExtras to update ACOs.
* Use *AclManager.aros* to set AROs models. This make the plugin more configurable.
* Added *AclManager.admin* param to set admin prefix. This param is boolean type.