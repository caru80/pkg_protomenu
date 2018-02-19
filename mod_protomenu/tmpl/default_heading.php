<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';

$linktype = '<span class="item-label">'.$item->title.'</span>';



$text = $item->anchor_title ? '<span class="item-subtitle">' . $item->anchor_title . '</span>' : '';

?>
<span class="nav-header <?php echo $item->anchor_css; ?>" <?php echo $title; ?>><?php echo $linktype; ?></span><?php echo $text;?>
