<?php
/**
 * Widgets manager index
 *
 * @package    HNG2
 * @subpackage widgets_manager
 * @author     Alejandro Caballero - lava.caballero@gmail.com
 *
 * @var \SimpleXMLElement $language
 */
?>

<h1 class="clearfix">
    <button class="pull-right" onclick="location.href = '<?= $_SERVER["PHP_SELF"] ?>?wasuuup=<?= md5(mt_rand(1, 65535)) ?>'">
        <span class="fa fa-refresh"></span>
        <?= $language->reload ?>
    </button>
    <?= $current_module->language->admin->page_title ?>
</h1>

<div class="framed_content state_highlight">
    <span class="fa fa-info-circle"></span>
    Nothing here yet :)
</div>
