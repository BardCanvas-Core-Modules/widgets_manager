<?php
/**
 * Widgets preparation
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 *             
 * @var settings $settings
 * @var template $template
 */

use hng2_base\settings;
use hng2_base\template;

$template->prepare_widgets("modules:widgets_manager.ls_layout", "left_sidebar_group_template.tpl", "left_sidebar");
$template->prepare_widgets("modules:widgets_manager.rs_layout", "right_sidebar_item_template.tpl", "right_sidebar");
