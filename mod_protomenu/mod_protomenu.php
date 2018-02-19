<?php
defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$list		= ModProtomenuHelper::getList($params);
$base		= ModProtomenuHelper::getBase($params);
$active		= ModProtomenuHelper::getActive($params);
$active_id 	= $active->id;
$path		= $base->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));

$doc = JFactory::getDocument();


if (count($list))
{
	$options = array(
		'seperateswitch' 	=> $params->get('seperateswitch',0),
		'mouseover'			=> $params->get('mouseover',0),
		'clickAnywhere'		=> $params->get('anywhereclose',0)
		);

	$optstr = '';
	foreach( $options as $o => $v )
	{
		$optstr .= $optstr == '' ? $o.':'.$v : ','.$o.':'.$v;
	}
	$optstr = '{'.$optstr.'}';


	$doc->addScript( JUri::base( true ).'/media/protomenu/js/jquery.protomenu.min.js' );
	$runscript = ';(function($){$(document).ready(function(){$(\'#ptmenu-'.$module->id.'\').protomenu('.$optstr.');})})(jQuery);';
	$doc->addScriptDeclaration($runscript);

	require JModuleHelper::getLayoutPath('mod_protomenu', $params->get('layout', 'default'));
}
