# CakePHP 3.x AclManager

## Installation

### Composer

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require ivanamat/cakephp3-aclmanager
```


### Git submodule

    git submodule add git@github.com:ivanamat/cakephp3-aclmanager.git plugins/AclManager
    git submodule init
    git submodule update


### Manual installation

Download the .zip or .tar.gz file, unzip and rename the plugin folder "cakephp3-aclmanager" to "AclManager" then copy the folder to your plugins folder.

[Download release](https://github.com/ivanamat/cakephp3-aclmanager/releases)


## Getting started

* Install the CakePHP ACL plugin by running *composer require cakephp/acl*. [Read Acl plugin documentation](https://github.com/cakephp/acl).
* Include the Acl and AclManager plugins in *app/config/bootstrap.php*

```php
    Plugin::load('Acl', ['bootstrap' => true]);
    Plugin::load('AclManager', ['bootstrap' => true, 'routes' => true]);
```

## Creating ACL tables

To create ACL related tables, run the following Migrations command:

    bin/cake migrations migrate -p Acl


## Example schema

An example schema:

```sql
    CREATE TABLE `groups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `created` datetime DEFAULT NULL,
        `modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `roles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `group_id` int(11) DEFAULT NULL,
        `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `created` datetime DEFAULT NULL,
        `modified` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```


## Configure the plugin

See *AclManager/config/bootstrap.php*.

AclManager.aros : write in there your requester models aliases (the order is important).


## Auth

Include and configure the *AuthComponent* and the *AclComponent* in the *AppController*

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
            'controller' => 'Users',
            'action' => 'login'
        ],
        'unauthorizedRedirect' => [
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

Add $this->addBehavior('Acl.Acl', ['type' => 'requester']); to the initialize function in the files src/Model/Table/GroupsTable.php, src/Model/Table/RolesTable.php and src/Model/Table/UsersTable.php

```php
    public function initialize(array $config) {
        parent::initialize($config);

        $this->addBehavior('Acl.Acl', ['type' => 'requester']);
    }
```

### Implement parentNode function in Group entity

Add the following implementation of parentNode to the file src/Model/Entity/Group.php:

```php
    public function parentNode()
    {
        return null;
    }
```

### Implement parentNode function in Role entity

Add the following implementation of parentNode to the file src/Model/Entity/Role.php:

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

Add the following implementation of parentNode to the file src/Model/Entity/User.php:

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


## Access the plugin

Now navigate to */AclManager/Acl*, update your acos and your aros or just click *Restore to default* and enjoy!


## Known issues

* AclManager::arosBuilder Needs always Groups, Roles and Users, it must be read from Configure::read('AclManager.aros');.


## Todo list:

* Aro search engine


## Other configurations

When the arosBuilder issue is solved, you will be able to configure the plugin in different ways, using only the models that you want, as example only Roles and Users or Groups and Users.


## About CakePHP 3.x AclManager

CakePHP 3.x - Acl Manager is a single plugin for CakePHP 3.x based on the [original idea](https://github.com/FMCorz/AclManager) of [Frédéric Massart (FMCorz)](https://github.com/FMCorz) for CakePHP 2.x and getting help from [some idea](https://github.com/JcPires/CakePhp3-AclManager/blob/master/src/Controller/Component/AclManagerComponent.php) of [Jean-Christophe Pires (JcPires)](https://github.com/JcPires).


## Author


Iván Amat [on GitHub](https://github.com/ivanamat)

[www.ivanamat.es](http://www.ivanamat.es/)


## Licensed

[MIT License](https://opensource.org/licenses/MIT)