# CakePHP 3.x Acl Manager

## Installation

### Composer

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require ivanamat/cakephp3-aclmanager
```


### Git submodule
```
git submodule add git@github.com:ivanamat/cakephp3-aclmanager.git plugins/AclManager
git submodule init
git submodule update
```

### Manual installation

Download the .zip or .tar.gz file, unzip and rename the plugin folder "cakephp3-aclmanager" to "AclManager" then copy the folder to your plugins folder.

[Download release](https://github.com/ivanamat/cakephp3-aclmanager/releases)


## Getting started

* Install the CakePHP ACL plugin by running *composer require cakephp/acl*. [Read Acl plugin documentation](https://github.com/cakephp/acl).
* Set AclManager configuration. ***AclManager.aros***. Must be specified before load plugin.
* Load the Acl and AclManager plugins in *app/config/bootstrap.php*.

```php
# Example configuration for an schema based on Groups, Roles and Users
Configure::write('AclManager.aros', array('Groups', 'Roles', 'Users'));

Plugin::load('Acl', ['bootstrap' => true]);
Plugin::load('AclManager', ['bootstrap' => true, 'routes' => true]);
```

**Warning:** It is not recommended to use Plugin::loadAll();. if you use Plugin::loadAll(); make sure it will not load any plugin several times with Plugin::load('PluginName').

### Configuration parameters

Must be specified before load plugin.

* **AclManager.aros** Required. Sets the AROs to be used. The value of this parameter must be an array with the names of the AROs to be used.
```php
# Example configuration for an schema based on Groups, Roles and Users
Configure::write('AclManager.aros', array('Groups', 'Roles', 'Users'));
```
* **AclManager.admin** Optional. Set 'admin' prefix. The value of this parameter must be boolean.
```php
# Set prefix admin ( http://www.domain.com/admin/AclManager )
Configure::write('AclManager.admin', true);
```
* ***AclManager.hideDenied*** Hide plugins, controllers and actions denied in ACLs lists.
```php
Configure::write('AclManager.hideDenied', true);
```
* ***AclManager.ignoreActions*** Ignore all plugins, controllers and actions you don't want to add to your ACLs. The value of this parameter must be an array.
```php
    # Ecample:
    Configure::write('AclManager.ignoreActions', array(
        'actionName', // ignore action
        'Plugin.*', // Ignore the plugin
        'Plugin.Controller/*', // Ignore the plugin controller
        'Plugin.Controller/Action', // Ignore specific action from the plugin.
        'Error/*' // Ignore the controller
        'Error/Action' // Ignore specifc action from controller
    ));
```

## Creating ACL tables

To create ACL related tables, run the following Migrations command.

    bin/cake migrations migrate -p Acl


## Example schema

An example schema based on Groups, Roles and Users.

```sql
    CREATE TABLE `groups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `created` datetime DEFAULT NULL,
        `modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `roles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_id` int(11) DEFAULT NULL,
        `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `created` datetime DEFAULT NULL,
        `modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_id` int(11) NOT NULL,
        `role_id` int(11) NOT NULL,
        `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `password` char(255) COLLATE utf8_unicode_ci NOT NULL,
        `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `created` datetime DEFAULT NULL,
        `modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

## Auth

Include and configure the *AuthComponent* and the *AclComponent* in the *AppController*.

```php
    public $components = [
        'Acl' => [
            'className' => 'Acl.Acl'
        ]
    ];
    
    $this->loadComponent('Auth', [
        'authorize' => [
            'Acl.Actions' => ['actionPath' => 'controllers/']
        ],
        'loginAction' => [
            'plugin' => false,
            'controller' => 'Users',
            'action' => 'login'
        ],
        'loginRedirect' => [
            'plugin' => false,
            'controller' => 'Posts',
            'action' => 'index'
        ],
        'logoutRedirect' => [
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'display'
        ],
        'unauthorizedRedirect' => [
            'plugin' => false,
            'controller' => 'Users',
            'action' => 'login',
            'prefix' => false
        ],
        'authError' => 'You are not authorized to access that location.',
        'flash' => [
            'element' => 'error'
        ]
    ]);
```

## Model Setup

### Acting as a requester

Add $this->addBehavior('Acl.Acl', ['type' => 'requester']); to the initialize function in the files src/Model/Table/GroupsTable.php, src/Model/Table/RolesTable.php and src/Model/Table/UsersTable.php.

```php
    public function initialize(array $config) {
        parent::initialize($config);

        $this->addBehavior('Acl.Acl', ['type' => 'requester']);
    }
```

### Implement parentNode function in Group entity

Add the following implementation of parentNode to the file src/Model/Entity/Group.php.

```php
    public function parentNode()
    {
        return null;
    }
```

### Implement parentNode function in Role entity

Add the following implementation of parentNode to the file src/Model/Entity/Role.php.

```php
    public function parentNode() {
        if (!$this->id) {
            return null;
        }
        if (isset($this->group_id)) {
            $groupId = $this->group_id;
        } else {
            $Users = TableRegistry::get('Users');
            $user = $Users->find('all', ['fields' => ['group_id']])->where(['id' => $this->id])->first();
            $groupId = $user->group_id;
        }
        if (!$groupId) {
            return null;
        }
        return ['Groups' => ['id' => $groupId]];
    }
```

### Implement parentNode function in User entity

Add the following implementation of parentNode to the file src/Model/Entity/User.php.

```php
    public function parentNode() {
        if (!$this->id) {
            return null;
        }
        if (isset($this->role_id)) {
            $roleId = $this->role_id;
        } else {
            $Users = TableRegistry::get('Users');
            $user = $Users->find('all', ['fields' => ['role_id']])->where(['id' => $this->id])->first();
            $roleId = $user->role_id;
        }
        if (!$roleId) {
            return null;
        }
        return ['Roles' => ['id' => $roleId]];
    }
```

## Create a group, role, and user.

Allow all. Add in AppController.php.
```php
public function initialize() {
	parent::initialize();
	
	...
	$this->Auth->allow();
}
```
Now create a group, role, and user.

## Access the plugin

Now navigate to http://www.domain.com/AclManager ( or http://www.domain.com/admin/AclManager If AclManager.admin is set to true ), just click *"Update ACOs and AROs and set default values"*, after update ACOs and AROs, remove *$this->Auth->allow()* from *AppController.php* and enjoy!


## Known issues

* Not known.


## Changelog

### v1.3

#### Added

* ***AclManager.hideDenied*** Hide plugins, controllers and actions denied in ACLs lists.

#### Changed

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

## About CakePHP 3.x Acl Manager

CakePHP 3.x - AclManager is a single plugin for manage CakePHP 3.x ACLs, based on the [original idea](https://github.com/FMCorz/AclManager) of [Frédéric Massart (FMCorz)](https://github.com/FMCorz) for CakePHP 2.x.

This project will be deprecated in favor of CakePHP 4.x - AclManager.

All code will be moved to the repository https://github.com/ivanamat/cakephp-aclmanager in order to continue future versions.

## Author

Iván Amat [on GitHub](https://github.com/ivanamat)  
[www.ivanamat.es](http://www.ivanamat.es/)

## Licensed

[MIT License](https://opensource.org/licenses/MIT)
