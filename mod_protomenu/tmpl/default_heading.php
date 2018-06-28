<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.1
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

$ptmItemConfig->classes[] = 'nav-header';
?>
<span
	class="<?php echo implode(' ', $ptmItemConfig->classes);?>" 
	<?php
		foreach($ptmItemConfig->dataAttribs as $name => $value):
	?>
			data-<?php echo $name;?>="<?php echo $value;?>"
	<?php
		endforeach;
	?> 
	<?php if($item->anchor_title != ''):        ?> title="<?php echo $item->anchor_title;?>"<?php endif;?>
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
		if($item->menu_image === '' || ($item->menu_image && $item->params->get('menu_text', 1))) :
	?>
			<span class="item-label">
				<?php echo $item->title;?>
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
		if($params->get('seperateswitch', 0) && $item->deeper && $item->params->get('ptm_item_behavior','') !== 'static'):
	?>
			<span class="item-switch" data-ptm-switcher><i></i></span>
	<?php
		endif;
	?>
</span>