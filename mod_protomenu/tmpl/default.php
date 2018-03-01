<?php
/*
	mod_ptmenu default.php

*/
defined('_JEXEC') or die;

jimport( 'joomla.application.module.helper' );

// Note. It is important to remove spaces between elements. – siehe auch Google: "white-space dependent rendering", oder: http://stackoverflow.com/questions/5256533/a-space-between-inline-block-list-items
?>
<nav id="ptmenu-<?php echo $module->id;?>" class="ptmenu <?php echo $class_sfx;?>">
	<div class="nav-wrapper">
		<ul class="nav-first">
<?php

$keepActiveOpen = $params->get('keepactiveopen',false); // Kinder von li.active werden angezeigt.

foreach ($list as $i => &$item)
{
	$iparams 	= $item->params;
	$class 		= 'item-' . $item->id;

	// -- CSS Klassen

	// Aktiv ?
	if (($item->id == $active_id) OR ($item->type == 'alias' AND $item->params->get('aliasoptions') == $active_id)) $class .= ' current';

	$parentActive = false; // Für keepActiveOpen...

	if (in_array($item->id, $path)){
		$class .= ' active';
		$parentActive = true;
	}
	elseif ($item->type == 'alias')
	{
		$aliasToId = $item->params->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class .= ' active';
			$parentActive = true;
		}
		elseif (in_array($aliasToId, $path))
		{
			$class .= ' alias-parent-active';
		}
	}

	// Trennzeichen?
	if ($item->type == 'separator') $class .= ' divider';

	// Menüüberschrift?
	if ($item->type == 'heading') $class .= ' heading';

	// Hat Kindelemente?
	if ($item->deeper) $class .= ' deeper';

	// Ist Elternelement?
	if ($item->parent) $class .= ' parent';

	// Eigene CSS Klasse
	if( $item->anchor_css ) $class .= ' ' . $item->anchor_css;


	// -- Protomenu

	// Megamenü?
	if( $iparams->get('ptmbehavior',0) == 1 ) {
		$class .= ' mega';
	}
	// Modul anzeigen?
	if( $iparams->get('ptmbehavior', 0) == 2 || $params->get('behavior', 0) == 3 ) {
		$class .= ' module';
	}

	// -- Klassenliste einfügen
	if (!empty($class)) $class = 'class="' . trim($class) . '"';

	echo '<li id="parent-'.$module->id.'-'.$item->id.'" ' . $class . '>';

	switch( $iparams->get('ptmbehavior') ){
		case 2 : // loadposition
			echo ModProtomenuHelper::getModules('loadposition', $iparams->get('ptmloadmodposition','') );
		break;
		case 3 : // loadmodule
			echo ModProtomenuHelper::getModules('loadmodule', $iparams->get('ptmloadmodule','') );
		break;
		default : // normal
			switch ($item->type){
				case 'separator':
				case 'url':
				case 'component':
				case 'heading':
					require JModuleHelper::getLayoutPath('mod_protomenu', 'default_' . $item->type);
				break;
				default:
					require JModuleHelper::getLayoutPath('mod_protomenu', 'default_url');
			}
	}

	// The next item is deeper.
	if ($item->deeper)
	{
		$act = $parentActive && $keepActiveOpen ? ' open in' : '';
		echo '<div id="'.$module->id.'-'.$item->id.'" class="nav-child nav-level-'.($item->level + 1).$act.'">';
		echo '<div>';
		echo '<ul class="nav-sub">';
	}
	elseif ($item->shallower)
	{
		// The next item is shallower.
		echo '</li>';
		echo str_repeat('</ul></div></div></li>', $item->level_diff);
	}
	else
	{
		// The next item is on the same level.
		echo '</li>';
	}
}
?>
		</ul>
	</div>
</nav>
