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
$class = 'class="'.$classList.'"';


$title 	= $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
$text 	= $item->anchor_title ? '<span class="item-subtitle">' . $item->anchor_title . '</span>' : '';
$switch = $item->deeper ? '<span class="item-switch"><i></i></span>' : '';

if ($item->menu_image){
	$item->params->get('menu_text', 1) ?
	$linktype = '<span class="item-image"><img src="' . $item->menu_image . '" alt="' . $item->title . '" /></span><span class="item-label">' . $item->title .'<i></i></span>' . $text :
	$linktype = '<span class="item-image"><img src="' . $item->menu_image . '" alt="' . $item->title . '" /></span>';
}
else{
	$linktype = '<span class="item-label">' . $item->title . '<i></i></span>' . $text;
}


// $linktype = '<span class="item-label">' . $item->title . '<i></i></span>' . $text;



$flink = $item->flink;
$flink = JFilterOutput::ampReplace(htmlspecialchars($flink));


if( $item->deeper && !$options["mouseover"] )
{
	$href = ' tabindex="0"';
}
else
{
	$href = ' href="'.$flink.'"';
}


switch ( $item->browserNav ) :
	default:
	case 0:
?>
<a <?php echo $class; echo $href; echo $title; ?> <?php echo $iparams->get('itemattributes','');?>><?php echo $switch . $linktype; ?></a>
<?php
		break;
	case 1:
		// _blank
?>
<a <?php echo $class; echo $href; echo $title; ?> target="_blank" <?php echo $iparams->get('itemattributes','');?>><?php echo $switch . $linktype; ?></a>
<?php
		break;
	case 2:
		// Use JavaScript "window.open"
		$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,' . $params->get('window_open');
?>
<a <?php echo $class; echo $href; echo $title; ?> <?php echo $iparams->get('itemattributes','');?> onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $switch . $linktype; ?></a>
<?php
		break;
endswitch;
