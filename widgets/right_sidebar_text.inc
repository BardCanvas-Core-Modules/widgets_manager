<?php
/**
 * Text widget for right sidebar
 * Called as include() from within a function
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 *
 * Trailing vars:
 * @var module[]          $modules
 * @var module            $this_module    widgets_manager
 * @var template          $template       The caller
 * @var \SimpleXMLElement $widget
 * @var string            $seed
 * @var string            $data_key
 * 
 * @var toolbox           $toolbox
 * 
 * @return string
 */

use hng2_base\module;
use hng2_base\template;
use hng2_modules\widgets_manager\toolbox;

global $settings, $config;

if( empty($config->globals["widgets_manager:toolbox"]) )
    $config->globals["widgets_manager:toolbox"] = new toolbox();
$toolbox = $config->globals["widgets_manager:toolbox"];

$placed_widget = $toolbox->get_widget_from_data_key($data_key);
if( is_null($placed_widget) ) return "";

$data = $toolbox->load_widget_data($placed_widget);
if( empty($data["contents"]) ) return "";

if( preg_match('/(<\?|<\?php)\s+(.*)\s*(\?>)?/i', $data["contents"], $matches) )
    return eval($matches[2]);

return $data["contents"];
