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

/**
	Dieses Temlate zeigt HTML.
*/
?>
<?php
	if($item->params->get('ptm_item_behavior', '') === 'html') {
		$itemText = $item->params->get('ptm_item_html_content','');
	}
	else {
		$itemText = $item->params->get('ptm_item_readmoretext','');
	}
	
	$readmoreLink		= '';
	$readmoreTitle 		= $item->params->get('ptm_item_readmore_title','') ? $item->params->get('ptm_item_readmore_title','') : $item->title;
	$readmoreInjected   = false;

	// -- Den Weiterlesen-Link zusammenbauen:
	if($item->flink !== '') :

		// -- Dem Link werden zusätzliche Klassen hinzugefügt:
		$ptmItemConfig->classes[] = 'readmore';

		// -- Link-Template:
		ob_start();
?>
		<a
			<?php echo $item->flink != '' ? ' href="' . $item->flink . ($ptmItemConfig->template != '' ? '?tmpl=' . $ptmItemConfig->template : '') . '"' : ' tabindex="0"'; ?> 
			class="<?php echo implode(' ', $ptmItemConfig->classes);?>" 
			<?php if($item->browserNav == 1):		?> target="_blank"<?php endif;?>
			<?php if($item->browserNav == 2):		?> onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;"<?php endif;?>
			<?php if($item->anchor_title != ''): 	?> title="<?php echo $item->anchor_title;?>"<?php endif;?>
			<?php echo $ptmItemConfig->customAttribs;?>
		>
				<?php echo $readmoreTitle;?> <i></i>
		</a>
<?php
		$readmoreLink = ob_get_contents();
		ob_end_clean();
	endif;

    // -- Diese Zeichenketten (die Array-Keys) im Text ersetzen (durch die Werte).
	$searchAndReplace   = array(
		"readmore_url" 		=> $item->flink,
		"readmore_title" 	=> $readmoreTitle,
		"language_title"	=> ModProtomenuHelper::getLanguageInfo()->title_native,
		"language_code" 	=> strtolower(ModProtomenuHelper::getLanguageInfo()->lang_code),
		"uri_root" 			=> JUri::root()
	);

	/**
		Template Ausgabe:
	*/
?>
<div class="ptmenu-item-custom"
<?php
	if($item->deeper) :
		foreach($ptmItemConfig->dataAttribs as $name => $value):
?>
			data-<?php echo $name;?>="<?php echo $value;?>"
<?php
		endforeach;
	endif;
?>
>
	<?php
		// -- Im Text den Platzhalter für den Weiterlesen-Link ersetzen:
		if(!$item->params->get('ptm_item_disable_readmore',0)) :
			$find = preg_quote("{readmore}", "/");
			if(preg_match("#" . $find . "#", $itemText)) :
				$itemText = preg_replace("#" . $find . "#", $readmoreLink, $itemText);
				$readmoreInjected = true;
			endif;
		endif;

		// -- Alle anderen Platzhalter im Text ersetzen:
		foreach($searchAndReplace as $needle => $replace) :
			$itemText = preg_replace("#" . preg_quote("{" . $needle . "}", "/") . "#", $replace, $itemText);
		endforeach;
	?>

	<?php echo $itemText;?>

	<?php
        // -- Wenn der Weiterlesen-Link nicht eingefügt wurde, und nicht Abgeschaltet ist, wird er jetzt eingefügt:
		if(!$readmoreInjected && !$item->params->get('ptm_item_disable_readmore',0)) :
			echo $readmoreLink;
		endif;
	?>
</div>
