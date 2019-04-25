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

$_sublayout = isset($layoutConf) ? $layoutConf->sublayout : 'default_';
$startlevel = (int)$params->get('startLevel', 1);

?>
<ul class="nav-first" data-ptm-root>
<?php
foreach ($list as $i => &$item)
{
	// Klassenliste für das Containerelement (<li>)
	$classList = ModProtomenuHelper::getItemClassList($item, $params, $module);
	$classList = trim(implode(' ', $classList));
	
/*
	Ausgabe Template:
*/
	
	// Listenelement öffnen:
	echo <<<TMPL
<li class="$classList" data-ptm-item="$module->id-$item->id">

TMPL;
	// Den Menüeintrag zusammenbauen
	require ModuleHelper::getLayoutPath('mod_protomenu', $_sublayout . 'item');
	
	if ($item->deeper) // Das nächste Item sitzt tiefer, ein Untermenü bauen.
	{	
		//$childLevel  = $item->level + 1;
		$childLevel  = ((int)$item->level - $startlevel) + 2; // Start-Level Parameter Fix
		$staticChild = $item->protomenu->staticItem ? ' data-ptm-static-child' : '';

        // Untermenü Header. Navigationspfad und Schließen-Knopf
        $childHeader = '';
		if($params->get('show_submenu_header', 0) && !$item->protomenu->staticItem) 
		{
            ob_start();
            require ModuleHelper::getLayoutPath('mod_protomenu', $_sublayout . 'childheader');
            $childHeader = ob_get_contents();
            ob_end_clean();
		}

		$childClasses = (object) array(
			"child" 		=> $item->protomenu->child_class,
			"child_outer" 	=> $item->protomenu->child_outer_class,
			"child_inner" 	=> $item->protomenu->child_inner_class
		);
		$childClasses->child .= $item->protomenu->megaMenu ? ' mega' : '';
		$childClasses->child .= $item->protomenu->staticItem ? ' static' : '';


		echo <<<TMPL
<div class="nav-child nav-level-$childLevel $childClasses->child" data-ptm-child="$module->id-$item->id" data-ptm-level="$childLevel" $staticChild>
	$childHeader
	<div class="nav-child-outer $childClasses->child_outer">
		<div class="nav-child-inner $childClasses->child_inner">
			<ul class="nav-sub nav-level-$childLevel" data-ptm-sub="$module->id-$item->id">
TMPL;
	}
	elseif ($item->shallower) // Das nächste Item sitzt höher. Untermenü schließen, und Listeneintrag schließen.
	{
		echo '</li>';
		echo str_repeat('</ul></div></div></div></li>', $item->level_diff);
	}
	else // Das nächste Item ist auf der gleichen Ebene. Listeneintrag schließen
	{
		echo '</li>';
	}
}
?>
</ul>