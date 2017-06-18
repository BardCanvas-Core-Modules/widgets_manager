<?php
/**
 * Widgets manager index
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 *
 * @var module[]          $modules
 * @var module            $current_module
 * @var \SimpleXMLElement $language
 * @var toolbox           $toolbox
 * @var placed_widget     $widget
 */

use hng2_base\module;
use hng2_modules\widgets_manager\placed_widget;
use hng2_modules\widgets_manager\toolbox;
?>

<!-- Expandible Text Area -->
<script type="text/javascript" src="<?=$config->full_root_path?>/lib/jquery.exptextarea.js"></script>
<script type="text/javascript"> $(document).ready(function() { $('.expandible_textarea').expandingTextArea(); }); </script>

<!-- CodeMirror -->
<script                 src="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/lib/codemirror.js"></script>
<script                 src="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/mode/xml/xml.js"></script>
<script                 src="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/mode/javascript/javascript.js"></script>
<script                 src="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/mode/css/css.js"></script>
<script                 src="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/mode/htmlmixed/htmlmixed.js"></script>
<link rel="stylesheet" href="<?=$config->full_root_path?>/lib/CodeMirror-5.16.2/lib/codemirror.css">
<style type="text/css">
    .CodeMirror {
        border:      1px solid silver;
        height:      auto;
        font-family: 'Lucida Console', 'Courier new', Courier, monospace !important; font-size: 8pt;
        width:       100%;
    }
    .CodeMirror-scroll { max-height: 300px; }
    .specs_info *:last-child { margin-bottom: 0; }
    .no_widgets { margin-top: 0; }
</style>

<script type="text/javascript">
    var weditor_ok_caption     = '<?= $language->ok ?>';
    var weditor_cancel_caption = '<?= $language->cancel ?>';
    var weditor_title          = '<?= $current_module->language->admin->editor->dialog_title ?>';
    var wcreator_title_left    = '<?= $current_module->language->admin->add_widget_to_left ?>';
    var wcreator_title_right   = '<?= $current_module->language->admin->add_widget_to_right ?>';
</script>
<link rel="stylesheet"        href="<?= $current_module->get_url() ?>/media/styles~v1.0.0-2.css">
<script type="text/javascript" src="<?= $current_module->get_url() ?>/media/functions~v1.0.1-1.js"></script>

<h1 class="clearfix">
    <button class="pull-right" onclick="location.href = '<?= $_SERVER["PHP_SELF"] ?>?wasuuup=<?= md5(mt_rand(1, 65535)) ?>'">
        <span class="fa fa-refresh"></span>
        <?= $language->reload ?>
    </button>
    <?= $current_module->language->admin->page_title ?>
</h1>

<form id="sidebar_widgets_form" method="post" action="<?= $current_module->get_url() ?>/scripts/save.php">
    <div class="multicol cols-2">
        
        <? $sidebar = "left_sidebar"; ?>
        <section class="col">
            <h2><?= $current_module->language->admin->left_sidebar ?></h2>
            <div class="framed_content" style="background-color: whitesmoke">
                
                <? $style = count($toolbox->placed_widgets[$sidebar]) == 0 ? "" : "display: none"; ?>
                <div class="no_widgets framed_content state_highlight" style="<?= $style ?>">
                    <i class="fa fa-info-circle"></i>
                    <?= $current_module->language->admin->none_placed ?>
                </div>
                
                <ul id="left_widgets" data-sidebar="left_sidebar" class="placed_widgets sortable">
                    <? foreach( $toolbox->placed_widgets[$sidebar] as $widget ):
                        $class = $widget->state == "enabled" ? "state_active" : "state_disabled";
                        $widget->custom_data = $toolbox->load_widget_data($widget); ?>
                        <li class="widget framed_content <?= $class ?>">
                            <span class="handle">
                                <i class="fa fa-ellipsis-v" style="margin-right: 1px;"></i><i class="fa fa-ellipsis-v"></i>
                            </span>
                            <?= $toolbox->render_widget_control($widget, $sidebar); ?>
                        </li>
                    <? endforeach; ?>
                </ul>
                
                <div align="center">
                    <button onclick="show_widget_addition_form('left_sidebar'); return false;">
                        <i class="fa fa-plus"></i>
                        <?= $current_module->language->admin->actions->add ?>
                    </button>
                </div>
            </div>
        </section>
        
        <? $sidebar = "right_sidebar"; ?>
        <section class="col">
            <h2><?= $current_module->language->admin->right_sidebar ?></h2>
            <div class="framed_content" style="background-color: whitesmoke">
                
                <? $style = count($toolbox->placed_widgets[$sidebar]) == 0 ? "" : "display: none"; ?>
                <div class="no_widgets framed_content state_highlight" style="<?= $style ?>">
                    <i class="fa fa-info-circle"></i>
                    <?= $current_module->language->admin->none_placed ?>
                </div>
                
                <ul id="right_widgets" data-sidebar="right_sidebar" class="placed_widgets sortable">
                    <? foreach($toolbox->placed_widgets[$sidebar] as $widget):
                        $class = $widget->state == "enabled" ? "state_active" : "state_disabled";
                        $widget->custom_data = $toolbox->load_widget_data($widget); ?>
                        <li class="widget framed_content <?= $class ?>">
                            <span class="handle">
                                <i class="fa fa-ellipsis-v" style="margin-right: 1px;"></i><i class="fa fa-ellipsis-v"></i>
                            </span>
                            <?= $toolbox->render_widget_control($widget, $sidebar); ?>
                        </li>
                    <? endforeach; ?>
                </ul>
                
                <div align="center">
                    <button onclick="show_widget_addition_form('right_sidebar'); return false;">
                        <i class="fa fa-plus"></i>
                        <?= $current_module->language->admin->actions->add ?>
                    </button>
                </div>
            </div>
        </section>
        
    </div>
    
    <p align="center">
        <button type="submit" class="big_form_submit_button">
            <i class="fa fa-save"></i>
            <?= $language->save ?>
        </button>
    </p>
</form>

<div id="sidebar_widgets_target" style="display: none;"></div>
<div id="widget_edition_target" style="display: none;"></div>
