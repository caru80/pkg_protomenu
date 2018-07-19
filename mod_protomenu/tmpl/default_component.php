<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.5
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

// Note. It is important to remove spaces between elements.
?>
<a
	<?php echo $item->flink != '' ? ' href="' . $item->flink . ($ptmItemConfig->template != '' ? '?tmpl=' . $ptmItemConfig->template : '') . '"' : ' tabindex="0"'; ?> 
	class="<?php echo implode(' ', $ptmItemConfig->classes);?>" 
	<?php
		foreach($ptmItemConfig->dataAttribs as $name => $value):
	?>
			data-<?php echo $name;?>="<?php echo $value;?>"
	<?php
		endforeach;
	?> 
	<?php if($item->browserNav === 1):		?> target="_blank"<?php endif;?>
	<?php if($item->browserNav === 2):		?> onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;"<?php endif;?>
	<?php if($item->anchor_title != ''): 	?> title="<?php echo $item->anchor_title;?>"<?php endif;?>
	<?php echo $ptmItemConfig->customAttribs;?>
>
	<?php 
		// -- Bild
		if($item->menu_image) :
	?>
			<span class="item-image">
				<img src="<?php echo $item->menu_image;?>" alt="<?php echo $item->title;?>" />
			</span>
	<?php 
		endif;
	?>

	<?php
		// -- Beschriftung
		if($item->menu_image === '' || ($item->menu_image && $item->params->get('menu_text', 1))) :
	?>
			<span class="item-label">
				<?php echo $item->title;?><?php if($item->deeper && !$parentStatic): ?><i class="item-arrow"></i><?php endif;?>
			</span>
	<?php
		endif;
	?>
	
	<?php
		// -- Beschreibung/Text
		if($item->params->get('ptm_item_description','') !== ''):
	?>
			<span class="item-description">
				<?php echo $item->params->get('ptm_item_description','');?>
			</span>
	<?php
		endif;
	?>

	<?php
		// -- Umschalter trennen?
		if($params->get('seperateswitch', 0) && $item->deeper && !$parentStatic):
	?>
			<span class="item-switch" data-ptm-switcher><i></i></span>
	<?php
		endif;
	?>
</a>