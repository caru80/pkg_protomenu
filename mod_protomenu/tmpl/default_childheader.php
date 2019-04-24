<?php
/**
 * @package        HEAD. Protomenü
 * @version        4.0
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 - 2019 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright    Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
?>
<div class="nav-child-header">
<?php
	// -- Navigationspfad:
	if ($params->get('submenu_show_tree', 0)) :
        $tree = <<<TREE
<span class="tree-item home">
    <a tabindex="0" class="close-ptmenu-$module->id"><span></span><i></i></a>
    <script>
        (function($) {
            $('.close-ptmenu-$module->id').on('click', function() {
                $('#ptmenu-$module->id').protomenu().closeRootLevel();
            });
        })(jQuery);
    </script>
</span>
TREE;

		foreach ($item->tree as $t => $itemId) :
			$treeItem 	= Factory::getApplication()->getMenu()->getItem($itemId);
			$itemTitle 	= $treeItem->params->get('ptm_item_readmore_title','') != '' ? $treeItem->params->get('ptm_item_readmore_title','') : $treeItem->title;

			if ($treeItem->id != $item->id 
				&& $treeItem->deeper 
				&& $treeItem->params->get('ptm_item_behavior', '') !== 'static' ) :

				$id = isset($item->tree[$t+1]) ? Factory::getApplication()->getMenu()->getItem($item->tree[$t+1])->id : '';

				$tree .= <<<TREE
<span class="tree-item index-$t" data-ptm-item="$module->id-$id">
	<a tabindex="0" class="tree-item-trigger">$itemTitle</a><i class="separator"></i>
</span>
TREE;
			else:
				$tree .= <<<TREE
<span class="tree-item index-$t">
	<span class="title" class="tree-item-label">$itemTitle</span><i class="separator"></i>
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
	if ($params->get('submenu_close_button',0)) :

		$btn_label = $params->get('submenu_close_button_label','');
?>
		<span class="nav-child-close" data-ptm-item="<?php echo $module->id . '-' . $item->id;?>">
			<a tabindex="0" title="<?php echo $btn_label;?>">
				<i></i><span><?php echo $btn_label;?></span>
			</a>
		</span>
<?php
	endif;
?>
</div>
