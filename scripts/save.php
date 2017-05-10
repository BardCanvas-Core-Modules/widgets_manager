<?php
/**
 * Save widget layouts and data
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 */

use hng2_modules\widgets_manager\module_widget;
use hng2_modules\widgets_manager\placed_widget;
use hng2_modules\widgets_manager\toolbox;

include "../../config.php";
include "../../includes/bootstrap.inc";
if( ! $account->_is_admin ) throw_fake_404();

$toolbox = new toolbox();

/** @var placed_widget[][] $incoming_widgets */
$incoming_widgets = array();

#
# Forging of incoming collections
#

foreach($_POST["widgets"] as $sidebar => $sidebar_widgets)
{
    if( ! is_array($sidebar_widgets) ) die($current_module->language->messages->invalid_data_received);
    
    foreach($sidebar_widgets as $handler => $entries)
    {
        if( ! is_array($entries) ) die($current_module->language->messages->invalid_data_received);
        
        foreach($entries as $seed => $details)
        {
            if( ! is_array($details) ) die($current_module->language->messages->invalid_data_received);
            
            $file_key = "$sidebar-$handler-$seed";
            $state    = $details["state"];
            $data     = unserialize(base64_decode(stripslashes($details["data"])));
            if( ! $data instanceof placed_widget ) die($current_module->language->messages->invalid_data_received);
            
            $data->state = $state;
            $incoming_widgets[$sidebar][$file_key] = $data;
        }
    }
}

#
# Saving incoming collections
#

$collections = array(
    "left_sidebar"  => "modules:widgets_manager.ls_layout",
    "right_sidebar" => "modules:widgets_manager.rs_layout",
);
foreach($collections as $sidebar => $settings_key)
{
    $settings_lines = array();
    if( ! empty($incoming_widgets[$sidebar]) )
    {
        foreach($incoming_widgets[$sidebar] as $file_key => $widget)
        {
            # echo "incoming widget: [$file_key]" . print_r($widget, true);
            $settings_lines[] = trim( ($widget->state == "enabled" ? "" : "# " )
                              . "{$widget->module_key}.{$widget->id}" 
                              . " | {$widget->seed}"
                              . " | {$widget->title}"
                              . " | {$widget->user_scope}"
                              . " | {$widget->page_scope}"
                              . " | " . implode(",", $widget->page_tags) );
            
            /** @var module_widget $parent_widget */
            $parent_widget = $toolbox->available_widgets[$sidebar][$widget->id];
            # echo "parent widget := " . print_r($parent_widget, true);
            if( ! empty($parent_widget->editable_specs) )
            {
                /** @var placed_widget $placed_widget */
                $placed_widget = $toolbox->placed_widgets[$sidebar][$file_key];
                # echo "placed widget := " . print_r($placed_widget, true);
                
                $old_data = $toolbox->load_widget_data($placed_widget);
                $new_data = $widget->custom_data;
                
                if( $new_data != $old_data )
                {
                    # echo "data in $file_key:\n";
                    # echo "old := " . print_r($old_data, true) . "\n";
                    # echo "new := " . print_r($new_data, true) . "\n";
                    
                    $toolbox->save_widget_data($widget);
                    send_notification($account->id_account, "success", replace_escaped_objects(
                        $current_module->language->messages->widget_data_saved,
                        array('{$widget}' => "[$sidebar] {$widget->module_key} {$widget->id} #{$widget->seed}")
                    ));
                    sleep(1);
                }
            }
            
        }
        
        //if( ! empty($settings_lines) )
        //    echo "\n------------------------------\n" .
        //        implode("\n", $settings_lines) .
        //        "\n------------------------------\n";
        
    }
    
    # print_r($_POST["widgets_order"][$sidebar]);
    $reordered_settings = array();
    foreach($_POST["widgets_order"][$sidebar] as $anchor)
    {
        foreach($settings_lines as $index => $line)
        {
            if( stristr($line, $anchor) !== false )
            {
                $reordered_settings[] = $line;
                
                unset($settings_lines[$index]);
                break;
            }
        }
    }
    $settings_lines = $reordered_settings;
    
    $old_settings_data = $settings->get($settings_key);
    $new_settings_data = implode("\n", $settings_lines);
    # echo "\n$old_settings_data\n";
    # echo "\n$new_settings_data\n";
    
    if( $new_settings_data != $old_settings_data)
    {
        if( empty($new_settings_data) ) $settings->delete($settings_key);
        else                            $settings->set($settings_key, $new_settings_data);
        
        send_notification($account->id_account, "success", replace_escaped_objects(
            $current_module->language->messages->sidebar_widgets_saved,
            array('{$sidebar}' => $sidebar)
        ));
        sleep(1);
    }
}

#
# Deletions
#

foreach($toolbox->placed_widgets as $sidebar => $widgets)
{
    foreach($widgets as $file_key => $widget)
    {
        if( isset($incoming_widgets[$sidebar][$file_key]) ) continue;
        
        if( $toolbox->delete_widget_data($widget) )
        {
            send_notification($account->id_account, "success", replace_escaped_objects(
                $current_module->language->messages->widget_data_deleted,
                array('{$widget}' => "[$sidebar] {$widget->module_key} {$widget->id} #{$widget->seed}")
            ));
            sleep(1);
        }
    }
}

echo "OK";
