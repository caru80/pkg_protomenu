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

/**
	Dieses Temlate zeigt Module.
*/

// -- Aufgabe ermitteln:
$task = '';
if($item->protomenu->loadModule)
{
	$task = 'loadmodule';
}
else
{
	$task = 'loadposition';
}
// -- Die Module rendern:
$modulesHtml = ModProtomenuHelper::getModules($task, $item->params, $module); 

/**
	Template Ausgabe:
*/
?>
<div class="item-modules <?php echo $item->anchor_css;?>">
	<?php echo $modulesHtml;?>
</div>