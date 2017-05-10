<?php

namespace hng2_modules\widgets_manager;

class toolbox
{
    /**
     * @var module_widget[][] Array: [sidebar][handling_key]
     */
    public $available_widgets = array();
    
    /**
     * @var placed_widget[][] Array: [sidebar][handling_key]
     */
    public $placed_widgets = array();
    
    public $save_path;
    
    public function __construct()
    {
        global $config;
        
        $this->save_path = "{$config->datafiles_location}/widgets";
        if( ! is_dir($this->save_path) )
        {
            if( ! @mkdir($this->save_path) )
                throw new \Exception("Can't create {$this->save_path}");
            
            @chmod($this->save_path, 0777);
        }
        
        $this->build_available_widgets_collections();
        $this->build_placed_widgets_collections();
    }
    
    private function build_available_widgets_collections()
    {
        global $modules;
        
        foreach($modules as $module)
        {
            if( empty($module->widgets) ) continue;
            if( ! isset($module->widgets->widget) ) continue;
            
            foreach($module->widgets->widget as $widget)
            {
                $for = trim($widget["for"]);
                $id  = trim($widget["id"]);
                # echo "<pre>" . print_r($widget, true) . "</pre>";
                # echo "<pre>" . print_r($module->language->widgets->{$id}, true) . "</pre>";
                
                $instance = new module_widget(array(
                    "module_key"    => trim($module->name),
                    "module_name"   => trim($module->language->display_name),
                    "id"            => $id,
                    "type"          => trim($widget["type"]),
                    "is_clonable"   => trim($widget["is_clonable"]),
                    "file"          => trim($widget["file"]),
                    "added_classes" => trim($widget["added_classes"]),
                    "title"         => trim($module->language->widgets->{$id}->title),
                    "info"          => trim($module->language->widgets->{$id}->info),
                ));
                
                if( isset($module->language->widgets->{$id}->editable_specs->specs) )
                    $instance->editable_specs = $module->language->widgets->{$id}->editable_specs;
                
                if( empty($instance->title) ) $instance->title = $id;
                
                $this->available_widgets[$for][$id] = $instance;
            }
        }
    }
    
    private function build_placed_widgets_collections()
    {
        $this->placed_widgets["left_sidebar"]  = $this->build_placed_widgets("ls_layout", "left_sidebar");
        $this->placed_widgets["right_sidebar"] = $this->build_placed_widgets("rs_layout", "right_sidebar");
    }
    
    private function build_placed_widgets($settings_key, $sidebar_key)
    {
        global $settings, $modules;
        
        $return   = array();
        $contents = $settings->get("modules:widgets_manager.$settings_key");
        
        if( ! empty($contents) )
        {
            $lines = explode("\n", $contents);
            foreach($lines as $line)
            {
                $line = trim($line);
                if( empty($line) ) continue;
                
                $parts = explode("|", $line);
                list($module_key, $id) = explode(".", trim($parts[0]));
                
                $state = "enabled";
                if( substr($module_key, 0, 1) == "#" )
                {
                    $state      = "disabled";
                    $module_key = trim(str_replace("#", "", $module_key));
                }
                
                $module_name = isset($modules[$module_key])
                             ? trim($modules[$module_key]->language->display_name)
                             : ucwords(str_replace("_", " ", $module_key));
                
                $seed       = trim($parts[1]);
                $widget_key = "$sidebar_key-$module_key.$id-$seed";
                $return[$widget_key] = new placed_widget(array(
                    "state"       => $state,
                    "sidebar"     => $sidebar_key,
                    "module_key"  => $module_key,
                    "module_name" => $module_name,
                    "id"          => $id,
                    "seed"        => $seed,
                    "title"       => trim($parts[2]),
                    "user_scope"  => trim($parts[3]),
                    "page_scope"  => trim($parts[4]),
                    "page_tags"   => trim($parts[5]),
                ));
            }
        }
        
        return $return;
    }
    
    /**
     * @param placed_widget $widget
     * @param string        $sidebar
     * @param bool          $target_markup_only
     *
     * @return string
     */
    public function render_widget_control(placed_widget $widget, $sidebar, $target_markup_only = false)
    {
        global $modules;
        
        $current_module = $modules["widgets_manager"];
        
        $widget_title = empty($this->available_widgets[$sidebar][$widget->id])
                      ? "<i class='fa fa-warning'> {$widget->id}</i>"
                      : $this->available_widgets[$sidebar][$widget->id]->title;
        
        $placed_title = empty($widget->title) ? $current_module->language->admin->untitled : $widget->title;
        
        $user_legend = "";
        switch($widget->user_scope)
        {
            case "all":
                $user_legend = "<i class='fa fa-users'></i> {$current_module->language->admin->user_scopes->all}";
                break;
            case "online":
                $user_legend = "<i class='fa fa-circle' style='color: darkgreen'></i> {$current_module->language->admin->user_scopes->online}";
                break;
            case "offline":
                $user_legend = "<i class='fa fa-circle' style='color: darkred'></i> {$current_module->language->admin->user_scopes->offline}";
                break;
        }
        
        $pages_legend = "";
        if( empty($widget->page_tags) )
            $pages_legend = "<i class='fa fa-asterisk'></i> {$current_module->language->admin->page_scopes->everywhere}";
        elseif($widget->page_scope == "show")
            $pages_legend = "<i class='fa fa-eye'></i> {$current_module->language->admin->page_scopes->show}";
        elseif($widget->page_scope == "hide")
            $pages_legend = "<i class='fa fa-eye-slash'></i> {$current_module->language->admin->page_scopes->hide}";
        
        $style_on  = $widget->state == "enabled" ? "" : "display: none;";
        $style_off = $widget->state != "enabled" ? "" : "display: none;";
        $data      = base64_encode(serialize($widget));
        
        $page_tags = "";
        if( ! empty($widget->page_tags) )
        {
            $tag_names = array();
            foreach($widget->page_tags as $tag)
                if( isset($current_module->language->page_tags->{$tag}) )
                    $tag_names[] = trim($current_module->language->page_tags->{$tag});
                else
                    $tag_names[] = $tag;
            
            $page_tags = "<span class='alternate'>" . implode(", ", $tag_names) . "</span>";
            
        }
        
        $target_markup = "
                <span class='title'>{$placed_title}</span>
                <span class='info'>{$widget->module_name}: {$widget_title}</span>
                <br clear='right'>
                
                <span class='user_legend framed_content inlined'>{$user_legend}</span>
                <span class='pages_legend framed_content inlined'>{$pages_legend}</span>
                <span class='page_tags'>{$page_tags}</span>
                
                <input type='hidden' name='widgets_order[$sidebar][]' value='{$widget->module_key}.{$widget->id} | {$widget->seed}'>
                <input type='hidden' name='widgets[$sidebar][{$widget->module_key}.{$widget->id}][{$widget->seed}][data]'
                       data-container='true' value='{$data}'>
        ";
        
        if( $target_markup_only ) return $target_markup;
        
        return "
            <span class='widget_state pull-right'>
                <span class='fa-pseudo-switch' data-value-on='enabled' data-value-off='disabled'
                      onclick='toggle_fa_pseudo_switch(this); toggle_widget_state_class(this);'>
                    <input type='hidden' name='widgets[$sidebar][{$widget->module_key}.{$widget->id}][{$widget->seed}][state]'
                           data-state='true' value='{$widget->state}'>
                    <span class='toggler toggle-on  fa fa-toggle-on'  style='$style_on'></span>
                    <span class='toggler toggle-off fa fa-toggle-off' style='$style_off'></span>
                </span>
                <button data-action='edit'   onclick='render_widget_editing_form(this, \"{$sidebar}\", \"{$widget->module_key}.{$widget->id}\", \"{$widget->seed}\"); return false;'><i class='fa fa-pencil'></i></button>
                <button data-action='delete' onclick='delete_widget(this); return false;'><i class='fa fa-times'></i></button>
            </span>
            
            <span class='update_target'>
                {$target_markup}
            </span>
        ";
    }
    
    /**
     * @param placed_widget $widget
     *
     * @throws \Exception
     */
    public function save_widget_data(placed_widget $widget)
    {
        $file_key = "{$widget->sidebar}-{$widget->id}-{$widget->seed}";
        $file      = "{$this->save_path}/{$file_key}.dat";
        
        if( file_exists($file) )
            if( ! is_writable($file) )
                throw new \Exception("Cannot save widget custom data: $file is not writable.");
        
        $data = serialize($widget->custom_data);
        file_put_contents($file, $data);
        @chmod($file, 0666);
    }
    
    /**
     * @param placed_widget $widget
     *
     * @return array
     */
    public function load_widget_data($widget)
    {
        if( is_null($widget) ) return array();
        
        $file_key = "{$widget->sidebar}-{$widget->id}-{$widget->seed}";
        $file      = "{$this->save_path}/{$file_key}.dat";
        
        if( ! file_exists($file) ) return array();
        
        return unserialize(file_get_contents($file));
    }
    
    /**
     * @param placed_widget $widget
     *
     * @return bool
     * @throws \Exception
     */
    public function delete_widget_data($widget)
    {
        if( is_null($widget) ) return false;
        
        $file_key = "{$widget->sidebar}-{$widget->id}-{$widget->seed}";
        $file      = "{$this->save_path}/{$file_key}.dat";
        if( ! file_exists($file) ) return false;
        
        if( ! is_writable($file) )
            throw new \Exception("Cannot delete widget custom data: $file is not writable.");
        
        return unlink($file);
    }
    
    /**
     * @param $data_key
     *
     * @return placed_widget|null
     */
    public function get_widget_from_data_key($data_key)
    {
        if( empty($data_key) ) return null;
        
        $parts = explode("-", $data_key);
        if( count($parts) < 2 ) return null;
        
        $sidebar = $parts[0];
        //list($module_key, $id) = explode(".", $parts[1]);
        //$seed = empty($parts[2]) ? "" : $parts[2];
        
        if( ! isset($this->placed_widgets[$sidebar][$data_key]) ) return null;
        
        return $this->placed_widgets[$sidebar][$data_key];
    }
}
