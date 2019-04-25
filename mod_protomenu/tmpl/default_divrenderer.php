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

function renderMenuDivisions($list, $level = 1, $parent = null, $params, $module, $sublayout) 
{
	if($parent)
	{
		// -- Untermenü-Header. Navigationspfad und Schließen-Knopf.
		$childHeader = "";
		if($params->get('show_submenu_header', 0) && !$parent->protomenu->staticItem) 
		{
			$item =& $parent; // $item zur Verwendung im Template default_childheader deklarieren

			// Den Submenu-Header rendern:
			ob_start();
			require ModuleHelper::getLayoutPath('mod_protomenu', $sublayout . 'childheader');
			$childHeader = ob_get_contents();
			ob_end_clean();
		}

		$staticChild = $parent->protomenu->staticItem ? ' data-ptm-static-child' : '';

		$childClasses = (object) array(
			"child" => $parent->protomenu->child_class,
			"child_outer" => $parent->protomenu->child_outer_class,
			"child_inner" => $parent->protomenu->child_inner_class
		);
		$childClasses->child .= $parent->protomenu->megaMenu ? ' mega' : '';
		$childClasses->child .= $parent->protomenu->staticItem ? ' static' : '';

		// Kindebene (Untermenü bauen):
		$html = <<<HTML
			<div class="nav-child nav-level-$level $childClasses->child" data-ptm-child="$module->id-$parent->id" data-ptm-level="$level"$staticChild>
				$childHeader
				<div class="nav-child-outer $childClasses->child_outer">
					<div class="nav-child-inner $childClasses->child_inner">
HTML;
	}
	else
	{	
		// Wurzelebene (1. Ebene):
		$html = <<<HTML
			<div class="nav-first" data-ptm-root>
				<div class="nav-first-outer">
					<div class="nav-first-inner">
HTML;
	}
	
	$temp = '';
	$childWrap = false;


	foreach($list as $item)
	{
		// Klassenliste für das Container-Element vom Link (vom .nav-item).
		$classList = ModProtomenuHelper::getItemClassList($item, $params, $module);


		// Menüeintrag zusammenbauen (Container-Element und Link)
		$html .= '<div class="' . trim(implode(' ', $classList)) . '" data-ptm-item="' . $module->id . '-' . $item->id . '">';
		ob_start();
		require ModuleHelper::getLayoutPath('mod_protomenu', $sublayout . 'item');
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= '</div>';


		if(count($item->children) && !$item->protomenu->staticItem)
		{
			if(!$childWrap)
			{
				$temp 		.= '<div class="nav-children">';
				$childWrap 	 = true;
			}
			$temp .= renderMenuDivisions($item->children, (int)$level + 1, $item, $params, $module, $sublayout);
		}
		else if(count($item->children)) 
		{
			$html .= renderMenuDivisions($item->children, (int)$level + 1, $item, $params, $module, $sublayout);
		}
	}
	$html .= '</div></div></div>' . $temp;
	if($childWrap)
	{
		$html .= '</div>';
	}
	return $html;
}
?>