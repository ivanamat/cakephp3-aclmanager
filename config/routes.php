<?php

/**
 * CakePHP 3.x - Acl Manager
 * 
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @category CakePHP3
 * 
 * @author   Ivan Amat <dev@ivanamat.es>
 * @copyright     Copyright 2016, Iván Amat
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     https://github.com/ivanamat/cakephp3-aclmanager
 * 
 */

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Core\Configure;

// Connect routes for admin prefix.
if (!Configure::read('AclManager.admin') || Configure::read('AclManager.admin') != true) {
    Router::connect(
        'AclManager',
        ['plugin' => 'AclManager', 'controller' => 'Acl', 'action' => 'index']
    );

    Router::connect(
        'AclManager/:action/*',
        ['plugin' => 'AclManager', 'controller' => 'Acl']
    );
} else {
    Router::connect(
        'admin/AclManager',
        ['plugin' => 'AclManager', 'controller' => 'Acl', 'action' => 'index']
    );

    Router::connect(
        'admin/AclManager/:action/*',
        ['plugin' => 'AclManager', 'controller' => 'Acl']
    );
}