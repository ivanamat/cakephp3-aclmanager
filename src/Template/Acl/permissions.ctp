<?php 
use Cake\Core\Configure; 
use Cake\Utility\Inflector;

echo $this->Html->css('AclManager.default',['inline' => false]);
?>
<section class="content">
  <div class="container">
	<div class="row justify-content-md-center">
		

		<?php if($this->request->session()->read('Flash')) { ?>
		<div class="row panel">
			<div class="columns col-md-12">
				<h3><?=__('Response');?></h3>
				<hr />
				<?php echo $this->Flash->render(); ?>
			</div>
		</div>
		<?php } ?>

		<div class="row panel">
			<div class="columns col-md-12">
				<h2><?php echo sprintf(__($model)); ?></h2>
				<hr />
                <div class="row panel">
                    <div class="columns col-md-12">
                        <div class="row">
                            <div class="columns col-md-4">
                                <p><?php echo $this->Html->image('AclManager.deny_32.png') . ' ' . __('Denied') ?></p>
                                <p><?php echo $this->Html->image('AclManager.deny_inherited_32.png') . ' ' . __('Denied by inheritance') ?></p>
                            </div>
                            <div class="columns col-md-4">
                                <p><?php echo $this->Html->image('AclManager.allow_32.png') . ' ' . __('Allowed') ?></p>
                                <p><?php echo $this->Html->image('AclManager.allow_inherited_32.png') . ' ' . __('Allowed by inheritance') ?></p>
                            </div>
                            <div class="columns col-md-4">
                                <p><?php echo __('All permissions are denied by default. When the permissions are set, this ACO\'s children inherit permissions.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
				<?php echo $this->Form->create('Perms'); ?>
                <table id="datatable" cellpadding="0" cellspacing="0" class="table table-bordered table-hover">
					<thead>
						<tr>
							<th><?= __('Action');?></th>
							<?php foreach ($aros as $aro): ?>
								<?php $aro = array_shift($aro); ?>
								<th>
									<?php
									$parentNode = $aro->parentNode();
									if (!is_null($parentNode)) {
										$key = key($parentNode);
										$subKey = key($parentNode[$key]);
										$gData = $this->AclManager->getName($key, $parentNode[key($parentNode)][$subKey]);
										echo h($aro[$aroDisplayField]) . ' ( ' . $gData['name'] . ' )';
									} else {
										echo h($aro[$aroDisplayField]);
									}
									?>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php
						$uglyIdent = Configure::read('AclManager.uglyIdent');
						$lastIdent = null;
						foreach ($acos as $id => $aco) {
							$action = $aco['Action'];
							$alias = $aco['Aco']['alias'];
							$ident = substr_count($action, '/');

							if ($ident <= $lastIdent && !is_null($lastIdent)) {
								for ($i = 0; $i <= ($lastIdent - $ident); $i++) {
									echo "</tr>";
								}
							}

							if ($ident != $lastIdent) {
								echo "<tr class='aclmanager-ident-" . $ident . "'>";
							}
							
							$uAllowed = true;
							if($hideDenied) {
								$uAllowed = $this->AclManager->Acl->check(['Users' => ['id' => $this->request->session()->read('Auth.User.id')]], $action);
							}

							if ($uAllowed) {
								echo "<td>";
								echo Inflector::humanize(($ident == 1 ? "<strong>" : "" ) . ($uglyIdent ? str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $ident) : "") . h($alias) . ($ident == 1 ? "</strong>" : "" ));
								echo "</td>";

								foreach ($aros as $aro):
									$inherit = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}-inherit");
									$allowed = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}");

									$mAro = $model;
									$mAllowed = $this->AclManager->Acl->check($aro, $action);
									$mAllowedText = ($mAllowed) ? 'Allow' : 'Deny';

									// Originally based on 'allowed' above 'mAllowed'
									$icon = ($mAllowed) ? $this->Html->image('AclManager.allow_32.png') : $this->Html->image('AclManager.deny_32.png');

									if ($inherit) {
										$icon = $this->Html->image('AclManager.inherit_32.png');
									}

									if ($mAllowed && !$inherit) {
										$icon = $this->Html->image('AclManager.allow_32.png');
										$mAllowedText = 'Allow';
									}

									if ($mAllowed && $inherit) {
										$icon = $this->Html->image('AclManager.allow_inherited_32.png');
										$mAllowedText = 'Inherit';
									}

									if (!$mAllowed && $inherit) {
										$icon = $this->Html->image('AclManager.deny_inherited_32.png');
										$mAllowedText = 'Inherit';
									}

									if (!$mAllowed && !$inherit) {
										$icon = $this->Html->image('AclManager.deny_32.png');
										$mAllowedText = 'Deny';
									}

									echo "<td class=\"select-perm\">";
											echo $icon . ' ' . $this->Form->select("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}", array('inherit' => __('Inherit'), 'allow' => __('Allow'), 'deny' => __('Deny')), array('empty' => __($mAllowedText), 'class' => 'form-control'));
									echo "</td>";
								endforeach;

								$lastIdent = $ident;
							}
						}

						for ($i = 0; $i <= $lastIdent; $i++) {
							echo "</tr>";
						}
						?>
					</tbody>
				</table>

				<div class="row">
					<div class="columns col-md-12">

						 <button type="submit" class="btn btn-primary right"><?php echo __("Save"); ?></button>
					</div>
                    <div class="columns col-md-12">
                        <br />
                    </div>
				</div>

				<?php echo $this->Form->end(); ?>
				
			</div>
		</div>
	</div>
  </div>
</section>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowgroup/1.0.2/css/rowGroup.dataTables.min.css">
<script type="text/javascript" language="javascript" src="//cdn.datatables.net/rowgroup/1.0.2/js/dataTables.rowGroup.min.js"></script>
<script>
    $(document).ready(function(){
        $('#datatable').DataTable({
            'paging'         : true,
            'lengthChange'   : false,
            'searching'      : false,
            'ordering'       : false,
            'info'           : false,
            'autoWidth'      : false,
            'iDisplayLength' : 500,
            'language'	     : {"url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Hungarian.json"},
            'aoColumnDefs'   : [
                {
                    "bSortable" : false,
                    "aTargets" : [ "noSort" ]
                }
            ]

        });
    });

</script>
