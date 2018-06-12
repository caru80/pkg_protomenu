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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgcontentprotomenuInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param   string  $type  Which action is happening (install|uninstall|discover_install)
     * @param   object  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($type, $parent)
    {}

    /**
     * Called on installation
     *
     * @param   object  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    function install($parent)
    { }

    /**
     * Called on uninstallation
     *
     * @param   object  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    function uninstall($parent)
    { }

    /**
     * Called after install
     *
     * @param   string  $type  Which action is happening (install|uninstall|discover_install)
     * @param   object  $parent  The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($type, $parent)
    {
		$query = "UPDATE `#__extensions` SET `enabled`=1 WHERE `element` = 'protomenu' AND `folder` = 'content' ";
        return JFactory::getDbo()->setQuery($query)->query();
   }

}
