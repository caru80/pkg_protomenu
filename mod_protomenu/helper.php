<?php
/**
 * @package        HEAD. Protomenü 2
 * @version        2.1.0
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

	public static function getModules( $cmd, $type ){
		$html = "";
		switch( $cmd ){
			case 'loadposition' :
				$modules = JModuleHelper::getModules($type);
				if( count($modules) ){
					foreach( $modules as $i => $module ){
						$html .= ModProtomenuHelper::renderModule($module);
					}
				}
			break;
			case 'loadmodule' :
				$type = explode(',', $type); // Bspw.: [0] => mod_login, [1] => Mein Modul
				$module = JModuleHelper::getModule($type[0], $type[1]);
				if( $module ){
					$html = ModProtomenuHelper::renderModule($module);
				}
			break;
		}
		return $html;
	}

	public static function renderModule( &$module ){

		$attribs 	= array("style" => ""); // Module-Chrome evtl. noch einbauen
		$params 	= new JRegistry($module->params);

		// Modultitel
		$title = '';
		if( $module->showtitle ){
			$htag 	= $params->get('header_tag','h3');
			$title  = '<'.$htag.' class="nav-module-title' . ($params->get('header_class','') != '' ? ' ' . $params->get('header_class') : '') . '">' . $module->title . '</'.$htag.'>';
		}

		// Modultag
		$mtag = $params->get('module_tag','div');
		// Modulklasse
		$mclass = $params->get('moduleclass_sfx','') != '' ? ' '.$params->get('moduleclass_sfx','') : '';

		$html  = '<'.$mtag.' class="moduletable nav-module module-'. $module->id . $mclass .'">';
		$html .= $title;
		$html .= JModuleHelper::renderModule($module, $attribs);
		$html .= '</'.$mtag.'>';
		return $html;
	}
}
