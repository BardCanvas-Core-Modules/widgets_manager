<?php

namespace hng2_modules\widgets_manager;

use hng2_repository\abstract_record;

class placed_widget extends abstract_record
{
    public $sidebar;
    
    public $module_key;
    
    public $module_name;
    
    /**
     * @var string module_name.widget_id
     */
    public $id;
    
    public $seed;
    
    public $title;
    
    /**
     * @var string all|online|offline
     */
    public $user_scope;
    
    /**
     * @var string show|hide
     */
    public $page_scope;
    
    public $page_tags;
    
    /**
     * @var string enabled|disabled
     */
    public $state;
    
    public $custom_data = array();
    
    public function set_new_id()
    {
        $this->seed = "s" . randomPassword(11);
    }
    
    public function set_from_post()
    {
        parent::set_from_post();
        
        $this->page_tags = array();
        if( is_array($_POST["page_tags"]) )
        {
            foreach($_POST["page_tags"] as $page_tag)
            {
                $page_tag = trim($page_tag);
                if( empty($page_tag) ) continue;
                
                $this->page_tags[] = $page_tag;
            }
        }
        
        $this->custom_data = array();
        if( is_array($_POST["custom_data"]) )
        {
            foreach($_POST["custom_data"] as $key => $val)
            {
                $val = trim(stripslashes($val));
                if( empty($val) ) continue;
                
                $this->custom_data[$key] = $val;
            }
        }
    }
    
    public function set_from_object($object_or_array)
    {
        parent::set_from_object($object_or_array);
        
        if( empty($this->page_tags) )
            $this->page_tags = array();
        elseif( is_string($this->page_tags) )
            $this->page_tags = preg_split("/\s*,\s*/", $this->page_tags);
    }
}
