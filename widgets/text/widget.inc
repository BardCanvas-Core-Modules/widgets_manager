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
 * Imported globals:
 * @var settings $settings
 * 
 * @return string
 */

use hng2_base\module;
use hng2_base\settings;
use hng2_base\template;

global $settings;


$result = $settings->get($data_key);

if( preg_match('/(<\?|<\?php)\s+(.*)\s*(\?>)?/i', $result, $matches) )
    return eval($matches[2]);

return $result;
