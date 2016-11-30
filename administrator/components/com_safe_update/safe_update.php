<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Safe_update
 * @author     Valeriy <chupurnov@gmail.com>
 * @copyright  2016 Valeriy Chupurnov XDSoft.net
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_safe_update'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.version');

include_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/lib/class.Diff.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/CompareHashFiles.php');
JHtml::_('jquery.framework');

JLoader::registerPrefix('Safe_update', JPATH_COMPONENT_ADMINISTRATOR);


Jfactory::getDocument()->addScript(JURI::root() . 'administrator/components/com_safe_update/assets/js/safe_update.js');
Jfactory::getDocument()->addStyleSheet(JURI::root() . 'administrator/components/com_safe_update/assets/css/safe_update.css');

$controller = JControllerLegacy::getInstance('Safe_update');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
