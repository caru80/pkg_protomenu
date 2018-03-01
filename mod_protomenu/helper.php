<?php
/**
 * 	Helper für mod_protomenu
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
