<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.

$classList = $item->anchor_css ? 'nav-item ' . $item->anchor_css : 'nav-item';
if( $item->deeper ) $classList .= ' trigger-'.$module->id.'-'.$item->id;
if($parentActive && $keepActiveOpen && $item->parent) $classList .= ' open';

$class = ' class="'.$classList.'"';

$title 	= $item->anchor_title ? ' title="' . $item->anchor_title . '" ' : '';
$text 	= $item->anchor_title ? '<span class="item-subtitle">' . $item->anchor_title . '</span>' : '';
$switch = $item->deeper ? '<span class="item-switch"><i></i></span>' : '';

if ($item->menu_image) {
	$item->params->get('menu_text', 1) ?
	$linktype = '<span class="item-image"><img src="' . $item->menu_image . '" alt="' . $item->title . '" /></span><span class="item-label">' . $item->title .'<i></i></span>' . $text :
	$linktype = '<span class="item-image"><img src="' . $item->menu_image . '" alt="' . $item->title . '" /></span>';
}
else{
	$linktype = '<span class="item-label">' . $item->title . '<i></i></span>' . $text;
}

switch ($item->browserNav)
{
	default:
	case 0:
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" <?php echo $title; ?> <?php echo $iparams->get('itemattributes','');?>><?php echo $switch . $linktype; ?></a><?php
		break;
	case 1:
		// _blank
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" target="_blank" <?php echo $title; ?> <?php echo $iparams->get('itemattributes','');?>><?php echo $switch . $linktype; ?></a><?php
		break;
	case 2:
	// Use JavaScript "window.open"
?><a <?php echo $class; ?> href="<?php echo $item->flink; ?>" <?php echo $iparams->get('itemattributes','');?> onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;" <?php echo $title; ?>><?php echo $switch . $linktype; ?></a>
<?php
		break;
}
