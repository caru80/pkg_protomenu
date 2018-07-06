<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.4
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

// -- Sollen die Kinder von allen <li class="active"> nach dem neuladen der Seite geöffnet werden.
$keepActiveOpen = $params->get('keepactiveopen', false);

foreach ($list as $i => &$item)
{
	/* CSS Klassen */

	$classList = array(
		'item-' . $item->id
	);

	// -- Aktiver Eintrag?
	if (($item->id === $active_id)
		|| ($item->type === 'alias' && $item->params->get('aliasoptions') === $active_id)) 
	{
		$classList[] = 'current';
	}

	$parentActive = false; // -- Müssen wir die Kinder dieses Menüeintrags nach dem neuladen der Seite anzeigen?

	// -- Statisches Item mit Untermenü?
	switch( $item->params->get('ptm_item_behavior','')) 
	{
		case 'static' :
		//case 'modules' :
		//case 'moduleposition' :
			$parentStatic = true;
		break;

		default : 
			$parentStatic = false;
	}

	$parentStatic = $item->params->get('ptm_item_behavior','') === 'static' ? true : false;
	
	// -- Alle Menüeinträge im aktuellen Navigationspfad als Aktiv markieren
	if (in_array($item->id, $path)) 
	{
		$classList[] = 'active';
		$parentActive = true;
	}
	elseif ($item->type == 'alias')
	{
		$aliasToId = $item->params->get('aliasoptions');

		if (count($path) > 0 && $aliasToId === $path[count($path) - 1])
		{
			$classList[] = 'active';
			$parentActive = true;
		}
		elseif (in_array($aliasToId, $path))
		{
			$classList[] = 'alias-parent-active';
		}
	}

	// Trennzeichen?
	if ($item->type === 'separator') $classList[] = 'divider';

	// Menü-Überschrift?
	if ($item->type === 'heading') $classList[] = 'heading';

	// Hat Kindelemente?
	if ($item->deeper) $classList[] = 'deeper';

	// Ist Elternelement?
	if ($item->parent) $classList[] = 'parent';

	// Eigene CSS Klasse – wird auch dem Anker hinzugefügt.
	// if( $item->anchor_css ) $classList[] = $item->anchor_css;

	// -- Eigene Klassen für Listenelement
	$classList[] = $item->params->get('ptm_listitem_classes','');

	/* Protomenü */

	// -- Megamenü?
	if($item->params->get('ptm_item_behavior',0) === 'megamenu') $classList[] = 'mega';
	
	// -- Werden Module angezeigt?
	if($item->params->get('ptm_item_behavior', 0) === 'modules' 
		|| $params->get('ptm_item_behavior', 0) === 'moduleposition') 
	{
		$classList[] = 'module';
	}


	// -- Protomenü Menüitem Konfiguration – Für den Anker (.nav-item)
	$ptmItemConfig = (object) [
		"classes"       => array($item->anchor_css),
		"dataAttribs"   => array(),
		"customAttribs" => (string) $item->params->get('ptm_item_attributes',''),
		"template" 		=> (string) $item->params->get('ptm_item_template','')
	];
	
	// -- Löst dieser Menüeintrag ein Untermenü aus?
	$ptmItemConfig->dataAttribs["ptm-trigger"] = '';
	if($item->deeper)
	{
		if(!$parentStatic) { // -- Ist nicht auf Statisch eingestellt, und soll auslösen.
			$ptmItemConfig->dataAttribs["ptm-trigger"] = $module->id . '-' . $item->id;
		}
	}

	if($parentActive && $keepActiveOpen && $item->parent) {
		$ptmItemConfig->classes[] = 'open';
	}
	


	/**
		Template Ausgabe:
	*/
	$classList = trim(implode(' ', $classList));
	
	// -- Listen-Eintrag öffnen:
	echo <<<TMPL
<li class="$classList" data-ptm-item="$module->id-$item->id">
TMPL;
	
	// -- Den Menüeintrag zusammenbauen:
	require JModuleHelper::getLayoutPath('mod_protomenu', 'default_item');

	
	if ($item->deeper) // -- Das nächste Item sitzt tiefer.
	{	
		$isVisible 	 = $parentActive && $keepActiveOpen ? ' open in' : '';
		$childLevel  = $item->level + 1;
		$staticChild = $parentStatic ? ' data-ptm-static-child' : '';

		// -- Grid?
		$containerClass = $item->params->get('ptm_item_behavior','') === 'megamenu' && $item->params->get('ptm_item_enable_grid',0) ? ' class="' . $item->params->get('ptm_item_grid_containerclass', 'container') . '"' : '';
		$rowClass       = $item->params->get('ptm_item_behavior','') === 'megamenu' && $item->params->get('ptm_item_enable_grid',0) ? $item->params->get('ptm_item_grid_rowclass', 'row') : '';

        // -- Untermenü Header. Navigationspfad und Schließen-Knopf
        $childHeader = "";
		if($params->get('show_submenu_header', 0) && ! $parentStatic) 
		{
            ob_start();
            require JModuleHelper::getLayoutPath('mod_protomenu','default_childheader');
            $childHeader = ob_get_contents();
            ob_end_clean();
		}

		echo <<<TMPL
<div class="nav-child nav-level-$childLevel $isVisible" data-ptm-child="$module->id-$item->id" data-ptm-level="$childLevel" $staticChild>
	$childHeader
	<div$containerClass>
		<ul class="nav-sub nav-level-$childLevel $rowClass" data-ptm-sub="$module->id-$item->id">
TMPL;
	}
	elseif ($item->shallower) // -- Das nächste Item sitzt höher.
	{
		echo '</li>';
		echo str_repeat('</ul></div></div></li>', $item->level_diff);
	}
	else // -- Das nächste Item ist auf der gleichen Ebene.
	{
		echo '</li>';
	}
}
?>