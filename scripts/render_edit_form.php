<?php
/**
 * Widget editor form renderer
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 * 
 * $_POST params when "return" isn't set:
 * @param string "sidebar"
 * @param string "id"
 * @param string "seed"
 * @param string "state"
 * @param string "data" base64 encoded placed_widget serialized record
 * 
 * $_POST params when "return" is "true":
 * @param string "id"
 * @param string "seed"
 * @param string "state"
 * @param string "module_key"
 * @param string "module_name"
 * @param string "title"
 * @param string "user_scope"
 * @param string "page_scope"
 * @param array  "page_tags"
 * 
 * @return string error|OK:<markup>
 */

use hng2_modules\widgets_manager\module_widget;
use hng2_modules\widgets_manager\placed_widget;
use hng2_modules\widgets_manager\toolbox;

include "../../config.php";
include "../../includes/bootstrap.inc";
include "../../settings/shared_functions.inc";
if( ! $account->_is_admin ) throw_fake_404();

$toolbox = new toolbox();

if( $_POST["return"] == "true" )
{
    $widget = new placed_widget();
    $widget->set_from_post();
    
    header("Content-Type: text/plain; charset=utf-8");
    die( "OK:" . $toolbox->render_widget_control($widget, $_POST["sidebar"], true) );
}

$data = (object) array(
    "sidebar" => trim(stripslashes($_POST["sidebar"])),
    "id"      => trim(stripslashes($_POST["id"])),
    "seed"    => trim(stripslashes($_POST["seed"])),
    "state"   => trim(stripslashes($_POST["state"])),
);

/** @var placed_widget $placed_widget */
$placed_widget = unserialize(base64_decode(trim(stripslashes($_POST["data"]))));

list($module, $id) = explode(".", $data->id);

/** @var module_widget $parent_widget */
$parent_widget = $toolbox->available_widgets[$data->sidebar][$id];

header("Content-Type: text/html; charset=utf-8");
?>OK:

<form method="post" id="widget_edition_form" action="<?= $_SERVER["PHP_SELF"] ?>">
    <input type="hidden" name="return"      value="true">
    <input type="hidden" name="sidebar"     value="<?= $data->sidebar ?>">
    <input type="hidden" name="id"          value="<?= $placed_widget->id ?>">
    <input type="hidden" name="seed"        value="<?= $placed_widget->seed ?>">
    <input type="hidden" name="state"       value="<?= $data->state ?>">
    <input type="hidden" name="module_key"  value="<?= $placed_widget->module_key ?>">
    <input type="hidden" name="module_name" value="<?= htmlspecialchars($placed_widget->module_name) ?>">
    
    <?
    $info_handler = "{$current_module->name}_widget_editor_basics";

    $style_on = $style_off = "";
    if( is_object($parent_widget->editable_specs) )
    {
        $style_on     = $account->engine_prefs[$info_handler] == "hidden" ? ""               : "display: none;";
        $style_off    = $account->engine_prefs[$info_handler] == "hidden" ? "display: none;" : "";
    }
    ?>
    
    <? if( is_object($parent_widget->editable_specs) ): ?>
    <section>
        <h2>
            <span class="pseudo_link" onclick="toggle_info_section('<?= $info_handler ?>'); $(this).find('.fa').toggle();"><i
                  class="fa fa-angle-down fa-fw fa-border" style="<?= $style_off ?>"></i><i
                  class="fa fa-angle-up fa-fw fa-border"   style="<?= $style_on  ?>"></i></span>
            <?= $current_module->language->admin->editor->basic_details ?>
        </h2>
        <div class="info_handler framed_content" id="<?= $info_handler ?>" style="<?= $style_off ?>">
    <? endif; ?>
            
            <div class="field">
                <div class="caption"><?= $current_module->language->admin->editor->handler ?></div>
                <div class="input">
                    <span class="framed_content inlined"><?= $placed_widget->module_name ?>: <?= $parent_widget->title ?></span>
                </div>
            </div>
            
            <div class="field">
                <div class="caption"><?= $current_module->language->admin->editor->title ?></div>
                <div class="input">
                    <input type="text" name="title" value="<?= htmlspecialchars($placed_widget->title) ?>">
                </div>
            </div>
            
            <div class="field">
                <div class="caption"><?= $current_module->language->admin->editor->user_scope ?></div>
                <div class="input">
                    <? foreach($current_module->language->admin->user_scopes->children() as $child):
                        /** @var \SimpleXMLElement $child */
                        $value = $child->getName(); ?>
                        <label>
                            <input type="radio" name="user_scope" <? if( $placed_widget->user_scope == $value ) echo "checked"; ?>
                                   value="<?= $value ?>">
                            <?= trim($child) ?>
                        </label><br>
                    <? endforeach ?>
                </div>
            </div>
            
            <div class="field">
                <div class="caption"><?= $current_module->language->admin->editor->page_scope ?></div>
                
                <div class="input">
                    <? foreach($current_module->language->admin->alt_page_scopes->children() as $child):
                        /** @var \SimpleXMLElement $child */
                        $value = $child->getName(); ?>
                        <label>
                            <input type="radio" name="page_scope" <? if( $placed_widget->page_scope == $value ) echo "checked"; ?>
                                   value="<?= $value ?>">
                            <?= trim($child) ?>
                        </label><br>
                    <? endforeach ?>
                </div>
                
                <div class="input">
                    <?= $current_module->language->admin->editor->pages ?>
                </div>
                
                <div class="input">
                    <? foreach($current_module->language->page_tags->children() as $child):
                        /** @var \SimpleXMLElement $child */
                        $value = $child->getName(); ?>
                        <label>
                            <input type="checkbox" name="page_tags[]" <? if( in_array($value, $placed_widget->page_tags) ) echo "checked"; ?>
                                   value="<?= $value ?>">
                            <?= trim($child) ?>
                        </label><br>
                    <? endforeach ?>
                </div>
                <?
                $missing_page_tags = array();
                foreach($placed_widget->page_tags as $tag)
                    if( ! isset($current_module->language->page_tags->{$tag}) )
                        $missing_page_tags[] = $tag;
                ?>
                <div class="input">
                    <?= $current_module->language->admin->editor->other_pages_by_tag->caption ?>
                    <i><?= $current_module->language->admin->editor->other_pages_by_tag->info ?></i><br>
                </div>
                
                <div class="missing_page_tags">
                    <? foreach($missing_page_tags as $tag): ?>
                        <div class="input missing_page_tag_entry">
                            <input type="text" name="page_tags[]" value="<?= $tag ?>">
                            <button onclick="$(this).closest('div').fadeOut('fast', function() { $(this).remove(); }); return false;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    <? endforeach; ?>
                </div>
                
                <div class="input">
                    <button onclick="add_page_tag_input_box(); return false;">
                        <i class="fa fa-plus"></i>
                        <?= $current_module->language->admin->editor->other_pages_by_tag->add ?>
                    </button>
                </div>
            </div>
    
    <? if( is_object($parent_widget->editable_specs) ): ?>        
        </div>
    </section>
    
    <br>
    <section>
        <h2><?= $current_module->language->admin->editor->custom_settings ?></h2>
        <div class="framed_content">
            <? foreach($parent_widget->editable_specs->specs as $specs): ?>
                <div class="field">
                    <div class="caption"><?= $specs->title ?></div>
                    <?
                    $description = unindent($specs->description);
                    if( ! empty($description) ) echo "
                            <div class='input info_handler specs_info state_highlihgt'>
                                {$description}
                            </div>
                        ";
                    ?>
                    <div class="input">
                        <?
                        $specs_name  = trim($specs["key"]);
                        $specs_type  = trim($specs->type);
                        $specs_value = $placed_widget->custom_data[$specs_name];
                        render_settings_editor($specs_type, $specs_name, $specs_value, $specs, "custom_data");
                        ?>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    </section>
    <? endif; ?>
    
</form>

