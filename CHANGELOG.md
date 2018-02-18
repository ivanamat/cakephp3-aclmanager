# Changelog

## v1.3

### Added

* ***AclManager.hideDenied*** Hide plugins, controllers and actions denied in ACLs lists.

### Changed

* ***AclManager.ignoreActions*** Ignore all plugins, controllers and actions you don't want to add to your ACLs.
```php
    Configure::write('AclManager.ignoreActions', array(
        'actionName', // ignore action
        'Plugin.*', // Ignore the plugin
        'Plugin.Controller/*', // Ignore the plugin controller
        'Plugin.Controller/Action', // Ignore specific action from the plugin.
        'Error/*' // Ignore the controller
        'Error/Action' // Ignore specifc action from controller
    ));
```
* Updated indexctp and permissioins.ctp: Show or hide ACLs that do not have permissions in the ACL list. Show flash messages below the actions panel.
* Fixed acoUpdate syncronization.

## v1.2

### Added

* ***AclManager.ignoreActions*** Ignore all actions you don't want to add to your ACLs.

## v1.1

### Changed

* Fixed the aro alias naming.
* Updated version requirement to ~1.0 for latest cakephp/plugin-installer.
* Updating docs to use correct config param for setting admin prefix.

### Contributtors

* pfuri [on GitHub](https://github.com/pfuri)  
* tjanssl [on GitHub](https://github.com/tjanssl)

## v1.0.5

### Changed
* Fixed bug on "Update ACOs". Now use AclExtras to update ACOs.
* Use *AclManager.aros* to set AROs models. This make the plugin more configurable.
* Added *AclManager.admin* param to set admin prefix. This param is boolean type.