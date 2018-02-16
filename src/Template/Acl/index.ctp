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
            <?php foreach ($manage as $k => $item): ?>
            <li><?php echo $this->Html->link(__('Manage {0}', strtolower($item)), ['controller' => 'Acl', 'action' => 'Permissions', $item]); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
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
            <li><?php echo $this->Html->link(__('Revoke permissions and set defaults'), ['controller' => 'Acl', 'action' => 'RevokePerms'], ['confirm' => __('Do you really want to revoke all permissions? This will remove all above assigned permissions and set defaults. Only first item of last ARO will have access to panel.')]); ?></li>
            <li><?php echo $this->Html->link(__('Drop ACOs and AROs'), ['controller' => 'Acl', 'action' => 'drop'], ['confirm' => __('Do you really want delete ACOs and AROs? This will remove all above assigned permissions.')]); ?></li>
            <li><?php echo $this->Html->link(__('Update ACOs and AROs and set default values'), ['controller' => 'Acl', 'action' => 'defaults'], ['confirm' => __('Do you want restore defaults? This will remove all above assigned permissions. Only first item of last ARO will have access to panel.')]); ?></li>
        </ul>
    </div>
</div>

<?php if($this->request->session()->read('Flash')) { ?>
<div class="row panel">
    <div class="columns large-12">
        <h3>Response</h3>
        <hr />
        <?php echo $this->Flash->render(); ?>
    </div>
</div>
<?php } ?>

<div class="row panel">
    <div class="columns large-12">
        <h2><?php echo __('About CakePHP 3.x - Acl Manager'); ?></h2>
        <p><?php echo $this->Html->link(__('CakePHP 3.x - Acl Manager on GitHub'), ['url' => 'https://github.com/ivanamat/cakephp3-aclmanager', 'target' => '_blank']); ?></p>
    </div>
</div>
