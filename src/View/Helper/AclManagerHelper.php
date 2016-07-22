<?php

/**
 * CakePHP 3.x - Acl Manager
 * 
 * PHP version 5
 * 
 * Class AclHelper
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @category CakePHP3
 * 
 * @package  AclManager\View\Helper
 * 
 * @author Ivan Amat <dev@ivanamat.es>
 * @copyright Copyright 2016, Iván Amat
 * @license MIT http://opensource.org/licenses/MIT
 * @link https://github.com/ivanamat/cakephp3-aclmanager
 */

namespace AclManager\View\Helper;

use Acl\Controller\Component\AclComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\View;

class AclManagerHelper extends Helper {
    
    /**
     * Helpers used.
     *
     * @var array
     */
    public $helpers = ['Html'];
    
    /**
     * Acl Instance.
     *
     * @var object
     */
    public $Acl;

    public function __construct(View $View , $config = array()) {
        parent::__construct($View, $config);

        $collection = new ComponentRegistry();
        $this->Acl = new AclComponent($collection, Configure::read('Acl'));
    }

    /**
     *  Check if the User have access to the aco
     *
     * @param \App\Model\Entity\User $aro The Aro of the user you want to check
     * @param string                  $aco The path of the Aco like App/Blog/add
     *
     * @return bool
     */
    public function check($aro, $aco) {
        if (empty($aro) || empty($aco)) {
            return false;
        }
        
        return $this->Acl->check($aro, $aco);
    }

    public function getName($aro, $id) {
        return $this->__getName($aro, $id);
    }

    /**
     * Return value from permissions input
     * 
     * @param string $value
     * @return boolean
     */
    public function value($value = NULL) {
        if($value == NULL) {
            return false;
        }
        
        $o = explode('.',$value);
        $data = $this->request->data;
        return $data[$o[0]][$o[1]][$o[2]];
    }

    protected function __getName($aro, $id) {
        $model = TableRegistry::get($aro);
        $data = $model->get($id, array(
            'recursive' => -1
        ));

        return $data;
    }
}