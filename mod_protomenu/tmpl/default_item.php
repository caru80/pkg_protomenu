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

// Note. It is important to remove spaces between elements.
?>

<?php
	switch($item->params->get('ptm_item_behavior','')):

        // -- HTML und Weiterlesen-Text
        case 'readmoretext' :
        case 'html' :
			require JModuleHelper::getLayoutPath('mod_protomenu', 'default_html');
		break;
        
        // -- Module
        case 'modules' :
        case 'moduleposition' :
            require JModuleHelper::getLayoutPath('mod_protomenu', 'default_module');
        break;

		default :

            $ptmItemConfig->classes[] = 'nav-item';

			switch($item->type) :
				
				case 'separator':
				case 'url':
				case 'component':
                case 'heading':
					require JModuleHelper::getLayoutPath('mod_protomenu', 'default_' . $item->type);
				break;
				
				default:
					require JModuleHelper::getLayoutPath('mod_protomenu', 'default_url');
			
			endswitch;

	endswitch;
?>