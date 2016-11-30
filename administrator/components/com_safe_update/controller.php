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

/**
 * Class Safe_updateController
 *
 * @since  1.6
 */
class Safe_UpdateController extends JControllerLegacy {
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return   JController This object to support chaining.
	 *
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false) {
		$_view = JFactory::getApplication()->input->getCmd('view', 'safeupdates');
        $view = $this->getView($_view, 'html');

        $input = JFactory::getApplication()->input;
		$input->set('view', $_view);
        
        
        if ($input->get('returnurl')) {
            $view->assign('returnurl', $input->get('returnurl'));
        } else {
            $view->assign('returnurl', false);
        }

		$view->display();

		return $this;
	}
}
