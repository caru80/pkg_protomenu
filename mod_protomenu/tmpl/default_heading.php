<?php
/**
 * @package        HEAD. Protomenü 2
 * @version        2.1.0
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

$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';

$linktype = '<span class="item-label">'.$item->title.'</span>';

$text = $item->anchor_title ? '<span class="item-subtitle">' . $item->anchor_title . '</span>' : '';

// -- Erweiterte Einstellungen
$enh_attribs = $item->params->get('ptm_item_attributes',''); // Zusätzliche Attribute
?>
<span class="nav-header <?php echo $item->anchor_css; ?> <?php echo $enh_attribs;?>" <?php echo $title; ?>><?php echo $linktype; ?></span><?php echo $text;?>