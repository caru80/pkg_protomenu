<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.2.0
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

use Joomla\CMS\Helper\ModuleHelper;

$_sublayout = isset($layoutConf) ? $layoutConf->sublayout : 'default_';

?>
<ul class="nav-first" data-ptm-root>
<?php
foreach ($list as $i => &$item)
{
	// -- Protomenü Menüitem Konfiguration – Für den Anker (.nav-item)
	$ptmItemConfig = (object) [
		"classes"       => array($item->anchor_css),
		"dataAttribs"   => array()
	];
	
	// -- Löst dieser Menüeintrag ein Untermenü aus?
	$ptmItemConfig->dataAttribs["ptm-trigger"] = '';
	if($item->deeper && !$item->protomenu->staticItem)
	{
		$ptmItemConfig->dataAttribs["ptm-trigger"] = $module->id . '-' . $item->id;
		
		// Soll dieser Menüeintrag als „geöffnet” markiert werden, wenn dieser Menüeintrag oder eines seiner Kinder aktiv ist?
		if($item->active && (bool)$params->get('keepactiveopen', false))
		{
			$ptmItemConfig->classes[] = 'open';
		}
	}


	// Klassenliste für das <li>
	$classList = ModProtomenuHelper::getItemClassList($item);
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
		$isVisible 	 = $item->active && $params->get('keepactiveopen', false) ? ' open' : '';
		$childLevel  = $item->level + 1;
		$staticChild = $item->protomenu->staticItem ? ' data-ptm-static-child' : '';

		// Grid eingeschaltet?
		$grid_container = $item->protomenu->grid ? $item->params->get('ptm_item_grid_containerclass', 'container') : '';
		$grid_row 		= $item->protomenu->grid ? $item->params->get('ptm_item_grid_rowclass', 'row') : '';

        // Untermenü Header. Navigationspfad und Schließen-Knopf
        $childHeader = '';
		if($params->get('show_submenu_header', 0) && !$item->protomenu->staticItem) 
		{
            ob_start();
            require ModuleHelper::getLayoutPath('mod_protomenu', $_sublayout . 'childheader');
            $childHeader = ob_get_contents();
            ob_end_clean();
		}

		echo <<<TMPL
<div class="nav-child nav-level-$childLevel $isVisible" data-ptm-child="$module->id-$item->id" data-ptm-level="$childLevel" $staticChild>
	$childHeader
	<div class="nav-child-inner $grid_container">
		<ul class="nav-sub nav-level-$childLevel $grid_row" data-ptm-sub="$module->id-$item->id">
TMPL;
	}
	elseif ($item->shallower) // Das nächste Item sitzt höher. Untermenü schließen, und Listeneintrag schließen.
	{
		echo '</li>';
		echo str_repeat('</ul></div></div></li>', $item->level_diff);
	}
	else // Das nächste Item ist auf der gleichen Ebene. Listeneintrag schließen
	{
		echo '</li>';
	}
}
?>
</ul>