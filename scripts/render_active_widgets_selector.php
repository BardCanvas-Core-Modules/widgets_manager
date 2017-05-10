<?php
/**
 * Active widgets selector renderer.
 * Note: the entire functionality of "unique" widgets (not clonable) is disabled.
 * This because the same widget may need to be rendered more than once but with
 * different scopes.
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 * 
 * $_GET params:
 * @param string "sidebar"
 * 
 * @return string error|OK:<markup>
 */

use hng2_modules\widgets_manager\module_widget;
use hng2_modules\widgets_manager\placed_widget;
use hng2_modules\widgets_manager\toolbox;

include "../../config.php";
include "../../includes/bootstrap.inc";
header("Content-Type: text/html; charset=utf-8");
if( ! $account->_is_admin ) throw_fake_401();

if( ! in_array($_GET["sidebar"], array("left_sidebar", "right_sidebar")) )
    die($current_module->language->messages->invalid_sidebar);

$sidebar = $_GET["sidebar"];
$toolbox = new toolbox();

/** @var module_widget[] $all_widgets */
$all_widgets = $toolbox->available_widgets[$sidebar];
if( empty($all_widgets) ) $all_widgets = array();

/** @var placed_widget[] $placed_widgets */
$placed_widgets = $toolbox->placed_widgets[$sidebar];
if( empty($placed_widgets) ) $placed_widgets = array();

/** @var module_widget[] $placeable_widgets */
$placeable_widgets = $all_widgets;
//$placeable_widgets = array();
//foreach($all_widgets as $available_widget)
//{
//    # If it is clonable we just add it.
//    if( $available_widget->is_clonable == "true" )
//    {
//        $placeable_widgets[] = $available_widget;
//        
//        continue;
//    }
//    
//    # It isn't clonable. We need to see if it isn't placed
//    $is_placed = false;
//    foreach($placed_widgets as $placed_widget)
//    {
//        if( $placed_widget->id == "{$available_widget->module_key}.{$available_widget->id}" )
//        {
//            $is_placed = true;
//            
//            break;
//        }
//    }
//    
//    if( ! $is_placed ) $placeable_widgets[] = $available_widget;
//}

if( empty($placeable_widgets) ) die("
    <div class='framed_content state_ko'>
        <i class='fa fa-warning'></i>
        {$current_module->language->messages->no_widgets_found}
    </div>
");

echo "<form>";
$index = 0;
foreach($placeable_widgets as $widget):
    ?>
    <div class="field" data-widget="<?= "{$widget->module_key}.{$widget->id}" ?>">
        <div class="caption">
            <label>
                <input type="radio" name="widget" <? if($index == 0) echo "checked"; ?>
                       value="<?= "{$widget->module_key}.{$widget->id}" ?>">
                <?= $widget->title ?>
            </label>
        </div>
        <? if( ! empty($widget->info) ) echo "
            <div class='input info_handler'>
                {$widget->info}
            </div>
        "; ?>
        
        <div class="markup" style="display: none"><?
            $dummy = new placed_widget(array(
                "sidebar"     => $sidebar,
                "module_key"  => $widget->module_key,
                "module_name" => $widget->module_name,
                "id"          => $widget->id,
                "user_scope"  => "all",
                "page_scope"  => "show",
                "page_tags"   => array(),
                "state"       => "enabled",
            ));
            $dummy->set_new_id();
            echo htmlspecialchars("
                <li class='widget framed_content state_highlight'>
                    <span class='handle'>
                        <i class='fa fa-ellipsis-v' style='margin-right: 1px;'></i><i class='fa fa-ellipsis-v'></i>
                    </span>
                    {$toolbox->render_widget_control($dummy, $sidebar)}
                </li>
            ");
        ?></div>
    </div>
    <?
    $index++;
endforeach;
echo "</form>";
