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

/**
	Dieses Temlate zeigt Module.
*/

// -- Aufgabe ermitteln:
$task = '';
switch($item->params->get('ptm_item_behavior', '')) {

	case 'modules' :
		$task = 'loadmodule';
	break;

	case 'moduleposition' :
		$task = 'loadposition';
	break;
}

// -- Die Module rendern:
$modulesHtml = ModProtomenuHelper::getModules($task, $item->params); 


/**
	Template Ausgabe:
*/
?>
<div class="ptmenu-item-modules">
	<?php echo $modulesHtml;?>
</div>