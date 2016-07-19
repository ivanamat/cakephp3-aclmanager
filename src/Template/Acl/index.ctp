<?php 

/**
 * CakePHP 3.x - Acl Manager
 * 
 * PHP version 5
 * 
 * index.ctp
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @category CakePHP3
 * 
 * @author Ivan Amat <dev@ivanamat.es>
 * @copyright Copyright 2016, Iván Amat
 * @license MIT http://opensource.org/licenses/MIT
 * @link https://github.com/ivanamat/cakephp3-aclmanager
 */

echo $this->Html->css('AclManager.default',['inline' => false]); 
?>

<div class="row">
    <div class="columns large-12">
        <h1><?php echo __('CakePHP 3.x - Acl Manager'); ?></h1>
    </div>
</div>

<div class="row panel">
    <div class="columns large-4">
        <h3><?php echo __('Manage'); ?></h3>
        <ul class="options">
            <?php if($this->request->session()->check('Auth.User.role_id') && $this->request->session()->read('Auth.User.role_id') == 1) { ?>
                <li><?php echo $this->Html->link(__('Manage groups'), ['controller' => 'Acl', 'action' => 'Permissions', 'Groups']); ?></li>
            <?php } ?>
            <li><?php echo $this->Html->link(__('Manage roles'), ['controller' => 'Acl', 'action' => 'Permissions', 'Roles']); ?></li>
            <li><?php echo $this->Html->link(__('Manage users'), ['controller' => 'Acl', 'action' => 'Permissions', 'Users']); ?></li>
        </ul>
    </div>
    <?php if($this->request->session()->check('Auth.User.role_id') && $this->request->session()->read('Auth.User.role_id') == 1) { ?>
    <div class="columns large-4">
        <h3><?php echo __('Update'); ?></h3>
        <ul class="options">
            <li><?php echo $this->Html->link(__('Update ACOs'), ['controller' => 'Acl', 'action' => 'UpdateAcos']); ?></li>
            <li><?php echo $this->Html->link(__('Update AROs'), ['controller' => 'Acl', 'action' => 'UpdateAros']); ?></li>
        </ul>
    </div>
    <div class="columns large-4">
        <h3><?php echo __('Drop and restore'); ?></h3>
        <ul class="options">
            <li><?php echo $this->Html->link(__('Drop permissions'), ['controller' => 'Acl', 'action' => 'RevokePerms'], ['confirm' => __('Do you really want to delete all permissions? After removing all permissions, only root users can access.')]); ?></li>
            <li><?php echo $this->Html->link(__('Drop ACOs/AROs'), ['controller' => 'Acl', 'action' => 'drop'], ['confirm' => __('Do you really want to delete ACOs and AROs? This too will remove all permissions. Make sure the method isAuthorized returns true if you want update ACOs and AROs after remove all them, otherwise you can\'t get acces to manage ACOs and AROs.')]); ?></li>
            <li><?php echo $this->Html->link(__('Restore to default'), ['controller' => 'Acl', 'action' => 'defaults'], ['confirm' => __('Do you want restore all permissions? This will override all above assigned permissions. Only root users can access after reset to defaults.')]); ?></li>
        </ul>
    </div>
    <?php } ?>
</div>

<div class="row panel">
    <div class="columns large-12">
        <h2><?php echo __('About CakePHP 3.x - Acl Manager'); ?></h2>
        <p><?php echo $this->Html->link(__('CakePHP 3.x - Acl Manager on GitHub'), ['url' => 'https://github.com/ivanamat/cakephp3-aclmanager', 'target' => '_blank']); ?></p>
    </div>
</div>