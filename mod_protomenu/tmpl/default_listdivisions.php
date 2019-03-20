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

$_sublayout = isset($layoutConf) ? $layoutConf->sublayout : 'default_';

/*
	Zum redern des Menüs in <div> wird die Funktion renderMenuDivisions benötigt, diese „baut” das Menü, und ruft sich dabei mehrfach selbst auf (Rekursion).
	Die Funktion renderMenuDivisions darf nur einmal existieren!
*/
if(!function_exists('renderMenuDivisions')) {
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'default_divrenderer.php');
}

echo renderMenuDivisions(ModProtomenuHelper::getMenuTree($list), 1, null, $params, $module, $_sublayout);
?>