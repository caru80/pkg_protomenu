<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.2
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

function renderMenuDivisions($list, $level = 1, $parent = null, $params, $module, $sublayout) 
{
	if($parent)
	{
		// -- Untermenü-Header. Navigationspfad und Schließen-Knopf.
		$childHeader = "";
		if($params->get('show_submenu_header', 0) && ! $parent->protomenu->staticItem) 
		{
			$item =& $parent; // $item zur Verwendung im Template default_childheader deklarieren
			$ptmItemConfig = (object) [
				"dataAttribs"   => (!$item->protomenu->staticItem ? array("ptm-trigger" => $module->id . '-' . $item->id) : array())
			];

			// Den Header rendern:
			ob_start();
			require ModuleHelper::getLayoutPath('mod_protomenu', $sublayout . 'childheader');
			$childHeader = ob_get_contents();
			ob_end_clean();
		}

		$childClasses = '';
		$staticChild  = $parent->protomenu->staticItem ? ' data-ptm-static-child' : '';

		if($parent->active && !$parent->protomenu->staticItem && (bool)$params->get('keepactiveopen', false)) 
		{
			$childClasses = 'open';
		}

		// Grid?
		$grid_container = $parent->protomenu->grid ? $item->params->get('ptm_item_grid_containerclass', 'container') : '';
		$grid_row 		= $parent->protomenu->grid ? $item->params->get('ptm_item_grid_rowclass', 'row') : '';

		// Kindebene (Untermenü bauen):
		$html = <<<HTML
			<div class="nav-child nav-level-$level $childClasses" data-ptm-child="$module->id-$parent->id" data-ptm-level="$level"$staticChild>
				$childHeader
				<div class="nav-child-outer $grid_container">
					<div class="nav-child-inner $grid_row">
HTML;
	}
	else
	{	
		// Wurzelebene (1. Ebene):
		$html = <<<HTML
			<div data-ptm-root class="nav-first">
				<div class="nav-first-outer">
					<div class="nav-first-inner">
HTML;
	}
	
	$temp = '';
	foreach($list as $item)
	{
		// Klassenliste für das Container-Element vom Link (vom .nav-item).
		$classList = ModProtomenuHelper::getItemClassList($item);
	
		// Protomenü Menüitem Konfiguration – Für den Anker (.nav-item)
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

		// Menüeintrag zusammenbauen (Container-Element und Link)
		$html .= '<span class="' . trim(implode(' ', $classList)) . '">';
		ob_start();
		require ModuleHelper::getLayoutPath('mod_protomenu', $sublayout . 'item');
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= '</span>';


		if(count($item->children) && !$item->protomenu->staticItem)
		{
			$temp .= renderMenuDivisions($item->children, (int)$item->level + 1, $item, $params, $module, $sublayout);
		}
		else if(count($item->children)) 
		{
			$html .= renderMenuDivisions($item->children, (int)$item->level + 1, $item, $params, $module, $sublayout);
		}
	}
	$html .= '</div></div></div>' . $temp;

	return $html;
}
?>