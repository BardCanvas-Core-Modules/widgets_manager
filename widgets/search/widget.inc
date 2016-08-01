<?php
/**
 * Search widget for right sidebar
 * Called as include() from within a function
 * TODO: Add option to show a category selector, so layout should be readjusted
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
 * @return string
 */

use hng2_base\module;
use hng2_base\template;

global $config, $language;

ob_start(); ?>
    
    <div class="sidebar_search" style="padding: 10px;">
        <form class="search_form" method="get" action="<?= $config->full_root_path ?>/">
            <div class="form_wrapper">
                <input type="text" name="s" placeholder="<?= htmlspecialchars($language->search_placeholder) ?>">
                <span class="submit_icon fa fa-search fa-fw pseudo_link" onclick="$(this).closest('form').submit()"></span>
            </div>
        </form>
    </div>

<? return ob_get_clean();
