<?php
/**
 * @package        HEAD. Protomenü
 * @version        3.0.5
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
defined( '_JEXEC' ) or die();

jimport('joomla.plugin.plugin');

class plgContentProtomenu extends JPlugin
{
	protected $autoloadLanguage = true;

	public function __construct( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}


	public function onContentPrepareForm($form, $data){
		$app 	= JFactory::getApplication();
		$option = $app->input->get('option');

		if( $app->isAdmin() && $option === 'com_menus'){
			JForm::addFormPath(__DIR__ . '/forms');
			$form->loadFile('item', false);
		}
		return true;
	}
}
