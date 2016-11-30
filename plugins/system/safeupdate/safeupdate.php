<?php
/**
 * @package    Joomla Safe Update
 * @subpackage safeupdate
 * @copyright  Copyright (c)2009-2016 Chupurnov Valeriy
 * @license    GNU General Public License version 3, or later
 *
 * @since      3.3
 */
defined('_JEXEC') or die();

if (!version_compare(PHP_VERSION, '5.3.0', '>=')) {
	return;
}


if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_safe_update')) {
	return;
}

JLoader::import('joomla.application.plugin');

class plgSystemSafeUpdate extends JPlugin {
	public function onAfterInitialise() {
		// Make sure this is the back-end
		$app = JFactory::getApplication();

		if (!in_array($app->getName(), array('administrator', 'admin'))) {
			return;
		}

		// Get the input variables
		$ji        = new JInput();
		$component = $ji->getCmd('option', '');
		$task      = $ji->getCmd('task', '');
		$view      = $ji->getCmd('view', '');
		$layout      = $ji->getCmd('layout', '');
		$is_safe_up  = $ji->getInt('is_safe_up', 0);

		if (($component == 'com_joomlaupdate') && ($task == 'update.install') && !$is_safe_up) {

			// Get the return URL (Joomla uses a different token)
			$jtoken = JFactory::getSession()->getFormToken();
			$return_url = JUri::base() . 'index.php?option=com_joomlaupdate&task=update.install&is_backed_up=1&is_safe_up=1&'.$jtoken.'=1';

			// Get the redirect URL
			$token        = JFactory::getSession()->getToken();
			$redirect_url = JUri::base() . 'index.php?option=com_safe_update&returnurl=' . base64_encode($return_url) . "&$token=1";

			// Perform the redirection
			$app = JFactory::getApplication();
			$app->redirect($redirect_url);
		} elseif (($component == 'com_joomlaupdate') && ($view == 'default') && $layout === 'complete' &&  !$is_safe_up) {
            // Get the return URL (Joomla uses a different token)
			$jtoken = JFactory::getSession()->getFormToken();
			$return_url = JUri::base() . 'index.php?option=com_joomlaupdate&view=default&layout=complete&is_safe_up=1&'.$jtoken.'=1';

			// Get the redirect URL
			$token        = JFactory::getSession()->getToken();
			$redirect_url = JUri::base() . 'index.php?option=com_safe_update&task=run.restore&returnurl=' . base64_encode($return_url) . "&$token=1";

			// Perform the redirection
			$app = JFactory::getApplication();
			$app->redirect($redirect_url);
        }
	}
}
