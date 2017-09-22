<?php

/**
 * CakePHP 3.x - Acl Manager
 * 
 * PHP version 5
 * 
 * Class AclManagerComponent
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @category CakePHP3
 * 
 * @package AclManager\Controller\Component
 * 
 * @author Ivan Amat <dev@ivanamat.es>
 * @copyright Copyright 2016, Iván Amat
 * @license MIT http://opensource.org/licenses/MIT
 * @link https://github.com/ivanamat/cakephp3-aclmanager
 * 
 * @author Jc Pires <djyss@live.fr>
 * @license MIT http://opensource.org/licenses/MIT
 * @link https://github.com/JcPires/CakePhp3-AclManager
 */

namespace AclManager\Controller\Component;

use Acl\Controller\Component\AclComponent;
use Acl\Model\Entity\Aro;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class AclManagerComponent extends Component {
    
    /**
     * Basic Api actions
     *
     * @var array
     */
    protected $config = [];
    
    /**
     * Initialize all properties we need
     *
     * @param array $config initialize cake method need $config
     *
     * @return null
     */
    public function initialize(array $config) {
        $this->config = $config;
        $this->controller = $this->_registry->getController();
        $registry = new ComponentRegistry();
        $this->Acl = new AclComponent($registry, Configure::read('Acl'));
        $this->Aco = $this->Acl->Aco;
        $this->Aro = $this->Acl->Aro;
        return null;
    }

    /**
     * Create aros
     * 
     * @return bool return true if all aros saved
     */
    public function arosBuilder() {
        
        $newAros = array();
        $counter = 0;        
        $parent = null;
        
        $models = Configure::read('AclManager.aros');
        // foreach ($models as $model) {
        for($i = 0; $i < count($models); $i++) {
            $model = $models[$i];
            $this->{$model} = TableRegistry::get($model);

            // Build the roles.
            $items = $this->{$model}->find('all');
            foreach($items as $item) {
                if($i > 0 && isset($models[$i-1])) {
                    $pk = strtolower(Inflector::singularize($models[$i-1])).'_id';
                    $parent = $this->Aro->find('all',
                        ['conditions' => [
                            'model' => $models[$i-1],
                            'foreign_key' => $item->{$pk}
                        ]])->first();
                }
                
                // Prepare alias
                $alias = null;
                if(isset($item->name)) {
                    $alias = $item->name;
                }
                
                if(isset($item->username)) {
                    $alias = $item->username;
                }
                
                // Create aro
                $aro = new Aro([
                    'alias' => $alias,
                    'foreign_key' => $item->id,
                    'model' => $model,
                    'parent_id' => (isset($parent->id)) ? $parent->id : Null
                ]);

                if($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {  
                    $counter++;
                }
            }
        }
        
        return $counter;
    }

    /**
     * Check if the aco exist and store it if empty
     *
     * @param string $path     the path like App/Admin/Admin/home
     * @param string $alias    the name of the alias like home
     * @param null   $parentId the parent id
     *
     * @return object
     */
    public function checkNodeOrSave($path, $alias, $parentId = null) {
        $node = $this->Aco->node($path);
        if ($node === false) {
            $data = [
                'parent_id' => $parentId,
                'model' => null,
                'alias' => $alias,
            ];
            $entity = $this->Aco->newEntity($data);
            $node = $this->Aco->save($entity);
            return $node;
        }
        return $node->first();
    }
    
    /**
     * Find aro in database and returns the number of matches
     * 
     * @param object $aro
     **/
    private function __findAro($aro) {
        
        
        if(isset($aro->parent_id)) {
            $conditions = [
                'parent_id' => $aro->parent_id,
                'foreign_key' => $aro->foreign_key,
                'model' => $aro->model
            ];
        } else {
            $conditions = [
                'parent_id IS NULL',
                'foreign_key' => $aro->foreign_key,
                'model' => $aro->model
            ];
        }

        return $this->Acl->Aro->find('all', [
            'conditions' => $conditions,
            'recursive' => -1
        ])->count();
    }

}