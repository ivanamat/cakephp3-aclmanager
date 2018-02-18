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
 * @author        Frédéric Massart - FMCorz.net
 * @copyright     Copyright 2011, Frédéric Massart
 * @link          http://github.com/FMCorz/AclManager
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Core\Configure;

/**
 * List of AROs (Class aliases)
 * Order is important! Parent to Children
 */
// Configure::write('AclManager.aros', array('Groups', 'Roles', 'Users'));

/**
 * Limit used to paginate AROs
 * Replace {alias} with ARO alias
 * Configure::write('AclManager.{alias}.limit', 3)
 */
// Configure::write('AclManager.Role.limit', 3);

/**
 * Routing Prefix
 * Set the prefix you would like to restrict the plugin to
 * @see Configure::read('Routing.prefixes')
 */
// Configure::write('AclManager.prefix', 'admin');

/**
 * Ugly identation?
 * Turn off when using CSS
 */
Configure::write('AclManager.uglyIdent', true);

/**
 * Hide denied on ACL list
 * Format: boolean ( true/false)
 */
if(!Configure::check('AclManager.hideDenied')) {
    Configure::write('AclManager.hideDenied', true);
}

/**
 * Actions to ignore when looking for new ACOs
 * Format: 'action', 'Controller/action' or 'Plugin.Controller/action'
 */
if(!Configure::check('AclManager.ignoreActions')) {
    Configure::write('AclManager.ignoreActions', array(
        'isAuthorized',
        'Acl.*',
        'Error/*'
    ));
}

/**
 * List of ARO models to load
 * Use only if AclManager.aros aliases are different than model name
 */
// Configure::write('AclManager.models', array('Group', 'Customer'));

/**
 * END OF USER SETTINGS
 */

/**
 * AclManager settings
 */
Configure::write("AclManager.version", "1.3");
if (!is_array(Configure::read('AclManager.aros'))) {
	Configure::write('AclManager.aros', array(Configure::read('AclManager.aros')));
}
if (!is_array(Configure::read('AclManager.ignoreActions'))) {
	Configure::write('AclManager.ignoreActions', array(Configure::read('AclManager.ignoreActions')));
}
if (!Configure::read('AclManager.models')) {
	Configure::write('AclManager.models', Configure::read('AclManager.aros'));
}
