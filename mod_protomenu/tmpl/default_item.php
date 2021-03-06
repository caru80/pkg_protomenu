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
?>
<?php
	switch($item->params->get('ptm_item_behavior','')):

        // -- HTML und Weiterlesen-Text
        case 'readmoretext' :
        case 'html' :
			require ModuleHelper::getLayoutPath('mod_protomenu', 'default_html');
		break;
        
        // -- Module
        case 'modules' :
        case 'moduleposition' :
			require ModuleHelper::getLayoutPath('mod_protomenu', 'default_module');
        break;

		default :

			switch($item->type) :
				
				case 'separator':
				case 'url':
				case 'component':
                case 'heading':
					require ModuleHelper::getLayoutPath('mod_protomenu', 'default_' . $item->type);
				break;
				
				default:
					require ModuleHelper::getLayoutPath('mod_protomenu', 'default_url');
	
			endswitch;

	endswitch;
?>