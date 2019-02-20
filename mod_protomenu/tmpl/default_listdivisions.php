<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.1.1
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

function getMenuTree($list, $level = 1, $parent = null) {

	$levelList = array();

	foreach($list as $i => $item)
	{
		if($parent !== null)
		{
			if($item->parent_id === $parent && (int)$item->level === $level)
			{
				$levelList[] = $item;
				if($item->deeper)
				{
					$item->children = getMenuTree($list, (int)$item->level + 1, $item->id);
				}
			}
		}
		else if((int)$item->level === $level)
		{
			$levelList[] = $item;
			if($item->deeper)
			{
				$item->children = getMenuTree($list, (int)$item->level + 1, $item->id);
			}
		}
	}

	return $levelList;
}


function renderMenu($list, $level = 1, $parent = null, $active_id = 0, $path = array(), $params, $module) 
{
	
	if($parent)
	{
		//$isVisible  = $parentActive && $keepActiveOpen ? ' open in' : '';
		//$childLevel = $item->level + 1;
		$parentStatic = $parent->params->get('ptm_item_behavior','') === 'static' ? true : false;

		// -- Grid?
		$containerClass = $parent->params->get('ptm_item_behavior','') === 'megamenu' && $parent->params->get('ptm_item_enable_grid',0) ? $parent->params->get('ptm_item_grid_containerclass', 'container') : '';
		$rowClass       = $parent->params->get('ptm_item_behavior','') === 'megamenu' && $parent->params->get('ptm_item_enable_grid',0) ? $parent->params->get('ptm_item_grid_rowclass', 'row') : '';

        // -- Untermenü Header. Navigationspfad und Schließen-Knopf
        $childHeader = "";
		if($params->get('show_submenu_header', 0) && ! $parentStatic) 
		{
			$item = $parent;
			$ptmItemConfig = (object) [
				"classes"       => array($item->anchor_css),
				"dataAttribs"   => array(),
				"customAttribs" => (string) $item->params->get('ptm_item_attributes',''),
				"template" 		=> (string) $item->params->get('ptm_item_template','')
			];
			if(!$parentStatic) { // -- Ist nicht auf Statisch eingestellt, und soll auslösen.
				$ptmItemConfig->dataAttribs["ptm-trigger"] = $module->id . '-' . $item->id;
			}
			ob_start();
            require JModuleHelper::getLayoutPath('mod_protomenu','default_childheader');
            $childHeader = ob_get_contents();
			ob_end_clean();
		}


		$staticChild = $parentStatic ? ' data-ptm-static-child' : '';
		
		/*
			„Dirty-Patch” 3.1.1 – Aktive bleiben geöffnet
		*/
		$childClasses = '';
		if(count($path) && in_array($parent->id, $path) && $params->get('keepactiveopen', false) && $staticChild === '') {
			$childClasses = 'open id-' . $parent->id;
		}

		$html = <<<HTML
			<div class="nav-child nav-level-$level $childClasses" data-ptm-child="$module->id-$parent->id" data-ptm-level="$level"$staticChild>
				$childHeader
				<div class="nav-child-outer $containerClass">
					<div class="nav-child-inner $rowClass">
HTML;
	}
	else
	{
		$html = <<<HTML
			<div data-ptm-root class="nav-first">
				<div class="nav-first-outer">
					<div class="nav-first-inner">
HTML;
	}
	
	$temp = '';
	foreach($list as $i => $item)
	{
		$classList = array(
			'item',
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
				$classList[] = 'static';
			break;
	
			default : 
				$parentStatic = false;
		}
	
		// $parentStatic = $item->params->get('ptm_item_behavior','') === 'static' ? true : false;
		
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
		if( $item->anchor_css ) $classList[] = $item->anchor_css;
	
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
		];

		// Query String und URI Fragmentbezeichner
		if((string) $item->params->get('ptm_item_queryfragment',''))
		{
			$ptmItemConfig->queryfragment = (string) $item->params->get('ptm_item_queryfragment','');
		}
		else if((string) $item->params->get('ptm_item_template','') != '')
		{
			$ptmItemConfig->queryfragment = (string) $item->params->get('ptm_item_template','');
		}
		
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

		$html .= '<span class="' . trim(implode(' ', $classList)) . '">';
		ob_start();
		require JModuleHelper::getLayoutPath('mod_protomenu', 'default_item');
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= '</span>';

		if(count($item->children) && !$parentStatic)
		{
			$temp .= renderMenu($item->children, (int)$item->level + 1, $item,  $active_id, $path, $params, $module);
		}
		else if(count($item->children) && $parentStatic) 
		{
			$html .= renderMenu($item->children, (int)$item->level + 1, $item,  $active_id, $path, $params, $module);
		}
	}
	$html .= '</div></div></div>' . $temp;

	return $html;
}

echo renderMenu(getMenuTree($list), 1, null, $active_id, $path, $params, $module);
?>