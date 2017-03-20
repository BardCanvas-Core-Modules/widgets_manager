<?php
/**
 * Custom links group on the left sidebar
 * Called as include() from within a function
 *
 * @package    HNG2
 * @subpackage categories
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
 * @return string
 */

use hng2_base\module;
use hng2_base\template;

global $settings;

$result = $settings->get($data_key);

if( preg_match('/(<\?|<\?php)\s+(.*)\s*(\?>)?/i', $result, $matches) )
    return eval($matches[2]);

return $result;
