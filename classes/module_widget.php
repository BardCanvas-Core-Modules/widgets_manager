<?php

namespace hng2_modules\widgets_manager;

use hng2_repository\abstract_record;

class module_widget extends abstract_record
{
    public $module_key;
    public $module_name;
    public $id;
    public $type;
    public $is_clonable;
    public $file;
    public $added_classes;
    
    public $title;
    public $info;
    
    /**
     * @var \SimpleXMLElement
     */
    public $editable_specs;
    
    public function set_new_id() { }
}
