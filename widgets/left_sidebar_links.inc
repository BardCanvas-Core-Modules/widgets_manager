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
 * @var toolbox           $toolbox
 *
 * @return string
 */

use hng2_base\module;
use hng2_base\template;
use hng2_modules\widgets_manager\toolbox;

global $config, $account;

include_once ROOTPATH . "/lib/phpQuery-onefile.php";

if( empty($config->globals["widgets_manager:toolbox"]) )
    $config->globals["widgets_manager:toolbox"] = new toolbox();
$toolbox = $config->globals["widgets_manager:toolbox"];

$placed_widget = $toolbox->get_widget_from_data_key($data_key);
if( is_null($placed_widget) ) return "";

$data = $toolbox->load_widget_data($placed_widget);
if( empty($data["contents"]) ) return "";

if( preg_match('/(<\?|<\?php)\s+(.*)\s*(\?>)?/i', $data["contents"], $matches) )
    return eval($matches[2]);

$pq = phpQuery::newDocumentHTML($data["contents"]);

foreach($config->user_levels_by_level as $level => $caption)
{
    $pq->find(".item[data-min-level='$level']")->each(function($element) {
        global $account;
        if( pq($element)->attr("data-min-level") > $account->level )
            pq($element)->remove();
    });
}

return $pq->html();
