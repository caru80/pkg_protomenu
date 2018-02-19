<?php
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
