<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.1
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

// -- mod_menu helper
require_once JPATH_BASE . '/modules/mod_menu/helper.php';

// -- Protomenu helper
class ModProtomenuHelper extends ModMenuHelper {


	/**
	 * Get a list of the menu items. Modifiziert um Menüeinträge aus definierten Modulen auszuschließen.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public static function getList(&$params, &$module = false)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// Get active menu item
		$base = self::getBase($params);
		$user = JFactory::getUser();
		$levels = $user->getAuthorisedViewLevels();
		asort($levels);
		$key = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
		$cache = JFactory::getCache('mod_menu', '');

		if ($cache->contains($key))
		{
			$items = $cache->get($key);
		}
		else
		{
			$path           = $base->tree;
			$start          = (int) $params->get('startLevel');
			$end            = (int) $params->get('endLevel');
			$showAll        = $params->get('showAllChildren');
			$items          = $menu->getItems('menutype', $params->get('menutype'));
			$hidden_parents = array();
			$lastitem       = 0;

			if ($items)
			{
				foreach ($items as $i => $item)
				{
					$item->parent = false;

					if (isset($items[$lastitem]) && $items[$lastitem]->id == $item->parent_id && $item->params->get('menu_show', 1) == 1)
					{
						$items[$lastitem]->parent = true;
					}

					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start - 2], $path)))
					{
						unset($items[$i]);
						continue;
					}

					// -- CRu. von definierten Modulen ausschließen
					if(in_array($module->id, $item->params->get('ptm_item_hideinmodule',array())))
					{
						$hidden_parents[] = $item->id;
						unset($items[$i]);
						continue;
					}

					// Exclude item with menu item option set to exclude from menu modules
					if (($item->params->get('menu_show', 1) == 0) || in_array($item->parent_id, $hidden_parents))
					{
						$hidden_parents[] = $item->id;
						unset($items[$i]);
						continue;
					}

					$item->deeper     = false;
					$item->shallower  = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem]))
					{
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$lastitem     = $i;
					$item->active = false;
					$item->flink  = $item->link;

					// Reverted back for CMS version 2.5.6
					switch ($item->type)
					{
						case 'separator':
							break;

						case 'heading':
							// No further action needed.
							break;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal Joomla link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							$item->flink = 'index.php?Itemid=' . $item->id;
							break;
					}

					if ((strpos($item->flink, 'index.php?') !== false) && strcasecmp(substr($item->flink, 0, 4), 'http'))
					{
						$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = JRoute::_($item->flink);
					}

					// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					// when the cause of that is found the argument should be removed
					$item->title          = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css     = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title   = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_rel     = htmlspecialchars($item->params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image     = $item->params->get('menu_image', '') ?
						htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
					$item->menu_image_css = htmlspecialchars($item->params->get('menu_image_css', ''), ENT_COMPAT, 'UTF-8', false);
				}

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start ?: 1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start ?: 1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ?: 1));
				}
			}

			$cache->store($items, $key);
		}

		return $items;
	}



	/**
	 * Hole die Module anhand der übergebenen Parameter
	 * 
	 * @param   string  $task   Ein String der definiert was zu tun ist. Dies kann entweder "loadmodule" oder "loadposition sein.
	 * @param   \Joomla\CMS\Registry\Registry  $itemParams   Ein Joomla Registry Objekt, welches die Menüitem-Parameter enthält.
	 * 
	 * @return   string  Das gerenderte HTML-Markup der angeforderten Module, oder ein leerer String, wenn keine Module gerendert wurden.
	 * 
	 * @since   3.0.0
	 */
	public static function getModules($task, &$itemParams) 
	{
		$html = "";

		switch($task)
		{
			case 'loadposition' :
				
				$modules = \Joomla\CMS\Helper\ModuleHelper::getModules($itemParams->get('ptm_load_module_position',''));

				if(count($modules))
				{
					foreach( $modules as $i => $module )
					{
						$html .= ModProtomenuHelper::renderModule($module, $itemParams);
					}
				}
			break;
			
			case 'loadmodule' :
				
				$modules = $itemParams->get('ptm_load_modules',array());

				if(!count($modules)) break;

                $db = \Joomla\CMS\Factory::getDbo();
                
                // -- Zugriffsrechte respektieren
                $levels     = \Joomla\CMS\Factory::getUser()->getAuthorisedViewLevels(); // Ids der Joomla Zugriffsebenen, denen dieser User angehört.
                
                // -- Veröffentlichungszeitraum respektieren
                $now        = \Joomla\CMS\Factory::getDate()->toSql(); // Aktuelles Datum als SQL DateTime String
                $nullDate   = $db->getNullDate(); // Null-Datum als SQL DateTime String

                foreach($modules as $i => $modid) 
                {
                    $q = $db->getQuery(true);

                    $cond = array(
                        $db->quoteName('id') . ' = ' . $modid,
                        $db->quoteName('published') . ' >= 1',
                        $db->quoteName('access') . ' IN (' . implode(',', $levels) .')',
                        '(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($now) . ')',
                        '(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($now) . ')'
                    );

                    $q->select('*')
                        ->from($db->quoteName('#__modules'))
                        ->where($cond, 'AND')
                        ->limit(1);

                    $db->setQuery($q);

                    if($result = $db->loadObject()) 
                    {
                        $html .= ModProtomenuHelper::renderModule($result, $itemParams);
                    }
                } // endforeach

			break;
        }
        
		return $html;
	}

	public static function renderModule(&$module, &$itemParams) 
	{
		$params = new \Joomla\Registry\Registry($module->params);
		if($itemParams->get('ptm_modules_chrome_style',0)) $params->set('style', $itemParams->get('ptm_modules_chrome_style'));

		$module->params = $params->toString();

		$html = \Joomla\CMS\Helper\ModuleHelper::renderModule($module);
		return $html;
	}
}
