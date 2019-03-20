<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.1.0
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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

require_once(__DIR__ . '/helper.php');

$list		= ModProtomenuHelper::getList($params, $module);
$class_sfx  = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

// -- Statisches oder Dynamisches Menü
if((bool) $params->get('menu_behavior',1)) {

	$doc = Factory::getDocument();

	$options = array(
		'seperateswitch' 	=> $params->get('seperateswitch', 0, 'INT'),
		'clickAnywhere'		=> $params->get('anywhereclose', 0, 'INT')
		);

	// -- Plugins
	$jsPlugins = array();
	if($params->get('plugin-backdrop',0))   $jsPlugins[] = $params->get('plugin-backdrop',0);
	if($params->get('plugin-html5video',0)) $jsPlugins[] = $params->get('plugin-html5video',0);

	// -- Plugins vorhanden?
	if(count($jsPlugins)) 
	{
		$options['plugins'] = $jsPlugins;
	}

	// Mouseover möglich und eingeschaltet?
	if($params->get('menu_rendermode', 'list', 'string') === 'list' 
		&& (int)$params->get('mouseover', 0, 'int') === 1)
	{
		$options['mouseover'] = 1;
	}

	// Optionen für Protomenü-JavaScript
	$jsOptions = json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	
	// Protomenü Init-Script
	$initScript = <<<SCRIPT
;(function($){
	$(function(){
		$('#ptmenu-$module->id').protomenu($jsOptions);
	});
})(jQuery);
SCRIPT;

	$doc->addScript(Uri::base(true).'/media/mod_protomenu/js/jquery.protomenu.min.js');
	$doc->addScriptDeclaration($initScript);
}

if(count($list))
{
	require ModuleHelper::getLayoutPath('mod_protomenu', $params->get('layout', 'default'));
}
