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

use Joomla\CMS\Helper\ModuleHelper;
// Note. It is important to remove spaces between elements. – siehe auch Google: "white-space dependent rendering", oder: http://stackoverflow.com/questions/5256533/a-space-between-inline-block-list-items
?>
<nav id="ptmenu-<?php echo $module->id;?>" class="ptmenu <?php echo $class_sfx;?>">
	<div class="nav-wrapper">
		<?php
			/**
				Rendere die Menüliste
			*/
			require ModuleHelper::getLayoutPath('mod_protomenu', 'default_' . $params->get('menu_rendermode', 'list'));
		?>
	</div>
</nav>
