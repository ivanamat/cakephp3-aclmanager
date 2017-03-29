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
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use ReflectionClass;
use ReflectionMethod;
use Cake\Utility\Inflector;

class AclManagerComponent extends Component {

    /**
     * Base for acos
     *
     * @var string
     */
    protected $_base = 'controllers';

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
        $this->controller = $this->_registry->getController();
        $registry = new ComponentRegistry();
        $this->Acl = new AclComponent($registry, Configure::read('Acl'));
        $this->Aco = $this->Acl->Aco;
        $this->Aro = $this->Acl->Aro;
        $this->config = $config;
        return null;
    }

    /**
     * TODO: Select Aros from Configure::read('AclManager.aros'); 
     * @author Ivan Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * @return bool return true if all aros saved
     */
    public function arosBuilder() {
        
        $newAros = array();
        $counter = 0;        
        $parent = null;
        
        $models = Configure::read('AclManager.aros');
        foreach ($models as $model) {
            $this->{$model} = TableRegistry::get($model);

            // Build the roles.
            $items = $this->{$model}->find('all');
            foreach($items as $item) {
                $arrayItem = $item->toArray();
                $alias = null;
                if(isset($arrayItem["name"])) {
                    $alias = $arrayItem["name"];
                }
                
                if(isset($arrayItem["username"])) {
                    $alias = $arrayItem["username"];
                }
                
                $aro = new Aro([
                    'alias'=>$alias,
                    'foreign_key' => $item->id,
                    'model'=>$this->{$model}->alias(),
                    'parent_id' => (isset($parent->id)) ? $parent->id : null
                ]);

                if($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {  
                    if($counter < (count($models)-1)) {
                        $parent = $this->Aro->find('all',
                            ['conditions' => [
                                'model' => $model,
                                'foreign_key' => $arrayItem["id"]
                            ]])->first();
                    }
                    $counter++;
                }
            }
        }
        
        /*
        $this->Groups = TableRegistry::get('Groups');
        $this->Roles = TableRegistry::get('Roles');
        $this->Users = TableRegistry::get('Users');
        
        // Build the groups.
	$groups = $this->Groups->find('all')->toArray();
	foreach($groups as $group) {
            $aro = new Aro([
                'alias'=>$group->name,
                'foreign_key' => $group->id,
                'model'=>'Groups',
                'parent_id' => null
            ]);
            
            if($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {  
                $counter++;
            }
	}
        
	// Build the roles.
	$roles = $this->Roles->find('all');
	foreach($roles as $role) {
            $parent = $this->Aro->find('all',
                    ['conditions' => [
                        'model' => 'Groups',
                        'foreign_key' => $role->group_id
                    ]])->first();
            $aro = new Aro([
                'alias'=>$role->name,
                'foreign_key' => $role->id,
                'model'=>'Roles',
                'parent_id' => $parent->id
            ]);
            if($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {  
                $counter++;
            }
	}
        
	// Build the users.
	$users = $this->Users->find('all');
	foreach($users as $user) {
            $parent = $this->Aro->find('all',
                    ['conditions' => [
                        'model' => 'Roles',
                        'foreign_key' => $user->role_id
                    ]])->first();
            $aro = new Aro([
                'alias'=>$user->email,
                'foreign_key' => $user->id,
                'model'=>'Users',
                'parent_id' => $parent->id
            ]);
            if($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {  
                $counter++;
            }
	}
        */

        return $counter;
    }

    /**
     * Gets the data for the current tag
     * 
     * @author Ivan Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * @param string $options Field name.
     * @return string -1,0,1.
     */
    public function value($options = array()) {
        $o = explode('.',$options);
        $data = $this->request->data;
        return $data[$o[0]][$o[1]][$o[2]];
    }

    /**
     * Get all controllers with actions
     *
     * @return array like Controller => actions
     */
    private function __getResources() {
        $controllers = $this->__getControllers();
        $resources = [];
        foreach ($controllers as $controller) {
            $actions = $this->__getActions($controller);
            array_push($resources, $actions);
        }
        return $resources;
    }

    /**
     * Get all controllers with actions in Plugins
     * 
     * @author Iván Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * @return array like Controller => actions
     */
    private function __getPluginsResources(){
        $controllers = $this->__getPluginsControllers();
        $resources = [];
        foreach($controllers as $key => $plugin){
            foreach($plugin as $plugin => $controllers){
                $resourcesPlugin = [$plugin => []];
                foreach ($controllers as $controller) {
                    $actions = $this->__getPluginActions($plugin,$controller);
                    foreach($actions as $action) {
                        $resourcesPlugin[$plugin.'/'.$controller][] = $action;
                    }
                }
                array_push($resources, $resourcesPlugin);
            }
        }
        return $resources;
    }

    /**
     * Get all controllers only from "Controller path only"
     *
     * @return array return a list of all controllers
     */
    private function __getControllers() {
        $path = App::path('Controller');
        $dir = new Folder($path[0]);
        $files = $dir->findRecursive('.*Controller\.php');
        $results = [];
        foreach ($files as $file) {
            $controller = str_replace(App::path('Controller'), '', $file);
            $controller = explode('.', $controller)[0];
            $controller = str_replace('Controller', '', $controller);
            array_push($results, $controller);
        }
        return $results;
    }

    /**
     * Return all actions from the controller
     *
     * @param string $controllerName the controller to be check
     *
     * @return array
     */
    private function __getActions($controllerName) {
        $className = 'App\\Controller\\' . $controllerName . 'Controller';
        $class = new ReflectionClass($className);
        $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $controllerName = str_replace("\\", "/", $controllerName);
        $results = [$controllerName => []];
        $ignoreList = ['beforeFilter', 'afterFilter', 'initialize', 'beforeRender'];
        foreach ($actions as $action) {
            if ($action->class == $className && !in_array($action->name, $ignoreList)
            ) {
                array_push($results[$controllerName], $action->name);
            }
        }
        return $results;
    }
    
    /**
     * Get all controllers from active Plugins
     * 
     * @author Iván Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * @return array like Plugin => Controllers
     */
    private function __getPluginsControllers() {
        $results = [];
        $ignoreList = [
            '.', 
            '..', 
            'Component', 
            'AppController.php',
        ];
        $plugins = Plugin::loaded();
        
        foreach($plugins as $plugin) {
            $result = [$plugin => []];
            $path = Plugin::path($plugin);
            $path = $path.'src/Controller/';
            if(is_dir($path)){
                $files = scandir($path);
                foreach($files as $file){
                    if(!in_array($file, $ignoreList)) {
                        $controller = explode('.', $file)[0];
                        $controllerName = str_replace('Controller', '', $controller);
                        array_push($result[$plugin], $controllerName);
                    }            
                }
                if(!empty($result[$plugin])) {
                    array_push($results, $result);
                }
            }
        }

        return $results;
    }

    /**
     * Get all actions in plugin controllers
     * 
     * @author Iván Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * @return array like Controllers => actions
     */
    private function __getPluginActions($plugin,$controllerName) {
        $className = $plugin.'\\Controller\\' . $controllerName . 'Controller';
        $class = new ReflectionClass($className);
        $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $results = [$controllerName => []];
        $ignoreList = ['beforeFilter', 'afterFilter', 'initialize'];
        foreach ($actions as $action) {
            if ($action->class == $className && !in_array($action->name, $ignoreList)) {
                array_push($results[$controllerName], $action->name);
            }
        }
        return $results;
    }

    /**
     * Acos Builder, find all public actions from controllers and stored them
     * with Acl tree behavior to the acos table.
     * Alias first letter of Controller will
     * be capitalized and actions will be lowercase
     *
     * @return bool return true if acos saved
     */
    private function __setAcos($ressources) {
        $root = $this->checkNodeOrSave($this->_base, $this->_base, null);
        unset($ressources[0]);
        foreach ($ressources as $controllers) {
            foreach ($controllers as $controller => $actions) {
                $tmp = explode('/', $controller);
                if (!empty($tmp) && isset($tmp[1])) {
                    $path = [0 => $this->_base];
                    $slash = '/';
                    $parent = [1 => $root->id];
                    $countTmp = count($tmp);
                    for ($i = 1; $i <= $countTmp; $i++) {
                        $path[$i] = $path[$i - 1];
                        if ($i >= 1 && isset($tmp[$i - 1])) {
                            $path[$i] = $path[$i] . $slash;
                            $path[$i] = $path[$i] . $tmp[$i - 1];
                            $this->checkNodeOrSave(
                                    $path[$i], $tmp[$i - 1], $parent[$i]
                            );
                            $new = $this->Aco
                                    ->find()
                                    ->where(
                                            [
                                                'alias' => $tmp[$i - 1],
                                                'parent_id' => $parent[$i]
                                            ]
                                    )
                                    ->first();
                            $parent[$i + 1] = $new['id'];
                        }
                    }
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                    $controller . $action, $action, end($parent)
                            );
                        }
                    }
                } else {
                    $controllerName = array_pop($tmp);
                    $path = $this->_base . '/' . $controller;
                    $controllerNode = $this->checkNodeOrSave(
                            $path, $controllerName, $root->id
                    );
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                    $controller . '/' . $action, $action, $controllerNode['id']
                            );
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Plugins Acos Builder, find all public actions from plugin's controllers and stored them
     * with Acl tree behavior to the acos table.
     * Alias first letter of Controller will
     * be capitalized and actions will be lowercase
     *
     * @return bool return true if acos saved
     */
    private function __setPluginsAcos($ressources) {
        foreach ($ressources as $controllers) {

            foreach ($controllers as $controller => $actions) {
                
                $parent = [];
                $path = [];
                $tmp = [];
                $pluginName = '';
                $root = '';

                $tmp = explode('/', $controller);
                $pluginName = $tmp[0];
                $root = $this->checkNodeOrSave($pluginName, $pluginName, 1);
                $slash = '/';
                $parent = [1 => $root->id];
                $path = [0 => $pluginName];
                $countTmp = count($tmp);

                if (!empty($tmp) && isset($tmp[1])) {
                    for ($i = 1; $i <= $countTmp; $i++) {
                        if ($path[$i - 1] != $tmp[$i - 1]) {
                            $path[$i] = $path[$i - 1];
                        } else {
                            $path[$i] = '';
                        }
                        if ($i >= 1 && isset($tmp[$i - 1])) {
                            if ($path[$i]) {
                                $path[$i] = $path[$i] . $slash;
                            }
                            $path[$i] = $path[$i] . $tmp[$i - 1];
                            if ($tmp[$i - 1] == '') {
                                $tmp[$i - 1] = "Controller";
                            }
                            $new = $this->checkNodeOrSave(
                                    $this->_base . '/' . $path[$i], $tmp[$i - 1], $parent[$i]
                            );
                            $parent[$i + 1] = $new->id;
                        }
                    }
                    
                    $actions = array_shift($actions);
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                    $controller . '/' . $action, $action, end($parent)
                            );
                        }
                    }
                } else {
                    $controllerName = array_pop($tmp);
                    $path = $this->_base . '/' . $controller;
                    $controllerNode = $this->checkNodeOrSave(
                            $path, $controllerName, $root->id
                    );
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                    $controller . '/' . $action, $action, $controllerNode['id']
                            );
                        }
                    }
                }
            }
        }
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
     * Find aro and returns the number of matches
     * 
     * @author Ivan Amat <dev@ivanamat.es>
     * @copyright Copyright 2016, Iván Amat
     * Find Aro in database
     **/
    private function __findAro($aro) {
        
        $conditions = [
            'alias' => $aro->alias,
            'foreign_key' => $aro->foreign_key,
            'model' => $aro->model
        ];
        
        /*
        $conditions = [
            'model' => $aro->model
        ];
        */
        
        return $this->Acl->Aro->find('all', [
            'conditions' => $conditions,
            'recursive' => -1
        ])->count();
    }

}