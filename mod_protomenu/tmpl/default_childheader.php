<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.2
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright    Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>

<div class="nav-child-header">
<?php
	// -- Navigationspfad:
	if($params->get('submenu_show_tree',0)) :
        $tree = <<<TREE
<span class="tree-item home">
    <a tabindex="0" class="close-ptmenu-$module->id"><span></span></psan><i></i></a>
    <script>
        (function($){
            $('.close-ptmenu-$module->id').on('click', function() {
                $('#ptmenu-$module->id').protomenu().closeRootLevel();
            });
        })(jQuery);
    </script>
</span>
TREE;

		foreach($item->tree as $t => $itemId) :
			$treeItem 	= \Joomla\CMS\Factory::getApplication()->getMenu()->getItem($itemId);
			$itemTitle 	= $treeItem->params->get('ptm_readmore_title','') != '' ? $treeItem->params->get('ptm_readmore_title','') : $treeItem->title;

			if($treeItem->id != $item->id && $treeItem->deeper && $treeItem->params->get('ptm_item_behavior', '') !== 'static' ) :

				$id = isset($item->tree[$t+1]) ? \Joomla\CMS\Factory::getApplication()->getMenu()->getItem($item->tree[$t+1])->id : '';

				$tree .= <<<TREE
<span class="tree-item index-$t">
	<a tabindex="0" data-ptm-trigger="$module->id-$id" class="trigger">$itemTitle</a><i class="separator"></i>
</span>
TREE;
			else:
				$tree .= <<<TREE
<span class="tree-item index-$t">
	<span class="title">$itemTitle</span><i class="separator"></i>
</span>
TREE;
			endif;
		endforeach;
?>
		<div class="nav-child-tree">
			<?php echo $tree;?>
		</div>
<?php
	endif;
?>

<?php
	// -- Schließen-Knopf:
	if($params->get('submenu_close_button',0)):
		$triggerData = "";
		foreach($ptmItemConfig->dataAttribs as $key => $value) :
			$triggerData .= " data-$name=\"$value\"";
		endforeach;
?>
		<a tabindex="0" class="nav-child-close" <?php echo $triggerData;?>>
			<i></i><span><?php echo $params->get('submenu_close_button_label','');?></span>
		</a>
<?php
	endif;
?>
</div>
