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

jimport('joomla.application.module.helper');

// Note. It is important to remove spaces between elements. – siehe auch Google: "white-space dependent rendering", oder: http://stackoverflow.com/questions/5256533/a-space-between-inline-block-list-items
?>
<nav id="ptmenu-<?php echo $module->id;?>" class="ptmenu <?php echo $class_sfx;?>">
	<div class="nav-wrapper">
		<ul class="nav-first" data-ptm-root>
			<?php
				/**
					Rendere die Menüliste
				*/
				require JModuleHelper::getLayoutPath('mod_protomenu', 'default_list');
			?>
		</ul>
	</div>
</nav>
