<?php
/**
 * Widgets manager index
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 * 
 * @var account  $account
 * @var template $template
 */

use hng2_base\account;
use hng2_base\template;
use hng2_modules\widgets_manager\toolbox;

include "../config.php";
include "../includes/bootstrap.inc";
if( ! $account->_is_admin ) throw_fake_404();

$template->page_contents_include = "contents/index.inc";
$template->set_page_title($current_module->language->admin->page_title);
include "{$template->abspath}/admin.php";
