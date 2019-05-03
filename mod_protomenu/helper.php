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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

// Importiere ModMenuHelper
// JLoader::import('mod_menu.helper', JPATH_SITE . DIRECTORY_SEPARATOR . 'modules');
JLoader::register('ModMenuHelper', JPATH_SITE . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'mod_menu' . DIRECTORY_SEPARATOR . 'helper.php');

// -- Protomenu helper
class ModProtomenuHelper extends ModMenuHelper {
	/**
	 * Get a list of the menu items. Modifiziert um Menüeinträge aus definierten Modulen und Menüeinträgen auszuschließen.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module options.
	 *
	 * @return  array
	 *
	 * @since   3.0.0
	 */
	public static function getList(&$params, &$module = false)
	{
		$app = Factory::getApplication();
		$menu = $app->getMenu();

		// Get active menu item
		$base 	= self::getBase($params);

		$levels = Factory::getUser()->getAuthorisedViewLevels();
		asort($levels);
		$key 	= 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
		$cache 	= Factory::getCache('mod_menu', '');
		// 
		// Für Joomla 4: 
		// $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('output', ['defaultgroup' => 'mod_menu']);
		//

		if ($cache->contains($key))
		{
			$items = $cache->get($key);
		}
		else
		{
			$path           = $base->tree;
			$start          = (int) $params->get('startLevel', 1);
			$end            = (int) $params->get('endLevel', 0);
			$showAll        = $params->get('showAllChildren', 1);
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
                    
                    // -- CRu. von definierten Menüeinträgen ausschließen
                    if(!empty($item->params->get('ptm_item_hideinmenuitem')) 
                        && in_array($app->input->get('Itemid', 0, 'INT'), $item->params->get('ptm_item_hideinmenuitem')))
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

					// Cru.: $item->active ist seitens Open Source Matters immer false... wird auch nirgendwo benutzt. 
					// In Joomla 4 wurde es bis jetzt nicht geändert.
					// Habe das geändert, und das wird nun von den Templates benutzt
					// $item->active = false;
					$navPath = self::getActive($params)->tree;

					$item->active = false;
					if (in_array($item->id, $navPath))
					{
						$item->active = true;
					}
					else if ($item->type === 'alias' 
						&& in_array($item->params->get('aliasoptions'), $navPath))
					{
						$item->active = true;

						// So werden z.B. URL oder Menüüberschriften aktiv, die ein Parent von einem Alias sind, welches gerade aktiv ist.
						if (isset($items[$lastitem]) && $items[$lastitem]->id == $item->parent_id)
						{
							$items[$lastitem]->active = true;
						}
					}

					$item->flink = $item->link;
					
					// Ist dies der aktuelle Menüeintrag?
					$item->current_active = false;
					$active_id = self::getActive($params)->id;
					if (($item->id === $active_id)
						|| ($item->type === 'alias' && $item->params->get('aliasoptions') === $active_id)) 
					{
						$item->current_active = true;
					}

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
						$item->flink = Route::_($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = Route::_($item->flink);
					}

					// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					// when the cause of that is found the argument should be removed
					$item->title          = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css     = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title   = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_rel     = htmlspecialchars($item->params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image     = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
					$item->menu_image_css = htmlspecialchars($item->params->get('menu_image_css', ''), ENT_COMPAT, 'UTF-8', false);
				
					// Protomenü Item-Verhalten
					$item->protomenu = new stdClass();
					$item->protomenu->staticItem 	= false;
					$item->protomenu->megaMenu 		= false;
					$item->protomenu->grid 			= false;
					$item->protomenu->loadModule 	= false;
					$item->protomenu->loadPosition 	= false;

					switch($item->params->get('ptm_item_behavior', ''))
					{
						case 'static' :
							$item->protomenu->staticItem = true;
						break;

						case 'megamenu' :
							$item->protomenu->megaMenu = true;
							$item->protomenu->grid 	= (bool)$item->params->get('ptm_item_enable_grid', 0);
						break;

						case 'modules' :
							$item->protomenu->loadModule 	= true;
							$item->protomenu->moduleIds 	= $item->params->get('ptm_load_modules', array());
						break;

						case 'moduleposition' :
							$item->protomenu->loadPosition 		= true;
							$item->protomenu->modulePosition 	= htmlspecialchars($item->params->get('ptm_load_module_position',''), ENT_COMPAT, 'UTF-8', false);
						break;
					}
					
					// Protomenü: QueryString und Fragmentbezeichner
					$item->protomenu->queryfragment 	= OutputFilter::ampReplace(htmlspecialchars($item->params->get('ptm_item_queryfragment',''), ENT_COMPAT, 'UTF-8', false));
					// Protomenü: Eigene Attribute
					$item->protomenu->linkattribs 		= htmlspecialchars($item->params->get('ptm_item_attributes',''), ENT_NOQUOTES, 'UTF-8', false);
					// Protomenü: Titel überschreibeb
					$item->protomenu->readmore_title 	= htmlspecialchars($item->params->get('ptm_item_readmore_title',''), ENT_COMPAT, 'UTF-8', false);
					// Protomenu: Eigener optionaler Text
					$item->protomenu->item_description 	= $item->params->get('ptm_item_description','');
					// Protomenu: CSS Klassen für Container von Kindelementen
					$item->protomenu->child_class = htmlspecialchars($item->params->get('ptm_child_class',''), ENT_COMPAT, 'UTF-8', false);
					$item->protomenu->child_outer_class = htmlspecialchars($item->params->get('ptm_child_outer_class',''), ENT_COMPAT, 'UTF-8', false);
					$item->protomenu->child_inner_class = htmlspecialchars($item->params->get('ptm_child_inner_class',''), ENT_COMPAT, 'UTF-8', false);
					// Protomenu: Kind-Container standardmäßig geöffnet?
					$item->protomenu->defaultopen = (bool)$item->params->get('ptm_child_defaultopen', false);

					$lastitem     = $i;
				} // endforeach

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start ?: 1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start ?: 1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ?: 1));
				}
				
			} // endif

			$cache->store($items, $key);
		} 

		return $items;
	}


	/**
	 * Bildet den Menübaum in ab, und gibt dieses Zurück. Das Array enthält alle Objekte der Menüeinträge. Kindelemente werden innerhalb der Eigenschaft „children” (array) eines Menüeintrags-Objekts abgebildet.
	 * 
	 * @param   array  $list   Die von ModProtomenuHelper::getList generierte Liste der Menüeinträge.
	 * @param   int  $level   Die Ebene im Menübaum
	 * @param 	object  $parent   Übergeordneter Menüeintrag von $level
	 * 
	 * @return   array  Ein mehrdimensonales Array, welches den Menübaum enthält.
	 * 
	 * @since   3.2
	 */
	public static function getMenuTree($list, $level = 1, $parent = null) 
	{
		$levelList = array();
	
		foreach($list as $item)
		{
			if($parent !== null)
			{
				if($item->parent_id === $parent && (int)$item->level === $level)
				{
					$levelList[] = $item;
					if($item->deeper)
					{
						$item->children = self::getMenuTree($list, (int)$item->level + 1, $item->id);
					}
				}
			}
			else if((int)$item->level === $level)
			{
				$levelList[] = $item;
				if($item->deeper)
				{
					$item->children = self::getMenuTree($list, (int)$item->level + 1, $item->id);
				}
			}
		}
	
		return $levelList;
	}

	/**
	 * 
	 *  @since   3.3
	 */
	public static function getItemClassList(&$item, &$params, &$module = false)
	{
		$classList = array(
			'item',
			'item-' . $item->id
		);
		
		// Ist der aktive Menüeintrag?
		if($item->current_active)
		{
			$classList[] = 'current';
			$classList[] = 'active';
		}
		else if ($item->active)		// Alle Menüeinträge im aktuellen Navigationspfad als Aktiv markieren
		{
			$classList[] = 'active';
		}
		
		// Ist ein statisches Elternelement?
		if($item->protomenu->staticItem)
		{
			$classList[] = 'static';
		}
	   
		// Trennzeichen?
		if ($item->type === 'separator')
		{
			$classList[] = 'divider';
		}
			
		// Menü-Überschrift?
		if ($item->type === 'heading')
		{
			$classList[] = 'heading';
		}

		// Hat Kindelemente?
		if ($item->deeper)
		{
			$classList[] = 'deeper';
		}

		// Ist Elternelement?
		if ($item->parent)
		{
			$classList[] = 'parent';
		}

		// Megamenü?
		if($item->protomenu->megaMenu)
		{
			$classList[] = 'mega';
		}

		// Werden Module angezeigt?
		if($item->protomenu->loadModule || $item->protomenu->loadPosition)
		{
			$classList[] = 'module';
		}

		if ($item->deeper && !$item->protomenu->staticItem)
		{
			if ($item->active && (bool)$params->get('keepactiveopen', false))
			{
				$classList[] = 'open';
				$item->protomenu->child_class .= ' open';
			}
			else if($item->protomenu->defaultopen)
			{
				$siblingActive = false;
				if((bool)$params->get('keepactiveopen'))
				{
					foreach(self::getList($params, $module) as $sibling)
					{
						if($sibling->level == $item->level 
							&& $sibling->id !== $item->id
							&& $sibling->active)
						{
							$siblingActive = true;
						}
					}
				}
				if(!$siblingActive) 
				{
					$classList[] = 'open';
					$item->protomenu->child_class .= ' open';
				}
			}
		}

		// Eigene Klassen für Listenelement
		$classList[] = $item->params->get('ptm_listitem_classes', '');

		return $classList;
	}


	/**
	 * Hole die Module anhand der übergebenen Parameter
	 * 
	 * @param   string  $task   Ein String der definiert was zu tun ist. Dies kann entweder "loadmodule" oder "loadposition" sein.
	 * @param   \Joomla\CMS\Registry\Registry  $itemParams   Ein Joomla Registry Objekt, welches die Menüitem-Parameter enthält.
	 * 
	 * @return   string  Das gerenderte HTML-Markup der angeforderten Module oder ein leerer String, wenn keine Module gerendert wurden.
	 * 
	 * @since   3.0.0
	 */
	public static function getModules($task, &$itemParams, &$thismodule) 
	{
		$html = "";

		switch($task)
		{
			case 'loadposition' :
				
				$modules = ModuleHelper::getModules($itemParams->get('ptm_load_module_position',''));

				if (count($modules))
				{
					foreach ( $modules as $module )
					{
						$html .= self::renderModule($module, $itemParams);
					}
				}
			break;
			
			case 'loadmodule' :
				
				$modules = $itemParams->get('ptm_load_modules',array());

				if (!count($modules)) break;

                $db = Factory::getDbo();
                
                // -- Zugriffsrechte respektieren
                $levels = Factory::getUser()->getAuthorisedViewLevels(); // Ids der Joomla Zugriffsebenen, denen der aktuelle User angehört.
				asort($levels);

                // -- Veröffentlichungszeitraum respektieren
                $now        = Factory::getDate()->toSql(); // Aktuelles Datum als SQL DateTime String
                $nullDate   = $db->getNullDate(); // Null-Datum als SQL DateTime String

                foreach ($modules as $modid) 
                {
					if($thismodule->id === $modid) continue; // Nicht das selbe Modul einfügen (Endlosschleife!)

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

                    if ($result = $db->loadObject()) 
                    {
                        $html .= self::renderModule($result, $itemParams);
                    }
                } // endforeach

			break;
        }
        
		return $html;
	}

	public static function renderModule(&$module, &$itemParams) 
	{
        $params = new Registry($module->params);
        
        if ($itemParams->get('ptm_modules_chrome_style',0)) 
        {
            $params->set('style', $itemParams->get('ptm_modules_chrome_style'));
        }

		$module->params = $params->toString();

		$html = ModuleHelper::renderModule($module);
		return $html;
	}


	public static function getLanguageInfo() 
	{
		$lang		= Factory::getLanguage();
		$languages	= LanguageHelper::getLanguages();

		foreach ($languages as $item)
		{
			if ($item->lang_code === $lang->getTag())
			{
				return $item;
			}
		}
		return false;
	}
}
