<?php
/**
 * A handy model for your hierarchy structure. Extend and define $table as well as add any fields you need
 *
 * You can also use Hierarchy controller with any other model you have
 */
namespace hierarchy;
class Model_Hierarchy extends \SQL_Model {
    function init(){
        parent::init();

        $this->setController('hierarchy/Table')     // Hierarchy controller for Table model
            ->useField('parent_id');                // Referencing ourselves
    }
}
