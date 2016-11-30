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

jimport('joomla.application.component.controllerform');

/**
 * Safeupdate controller class.
 *
 * @since  1.6
 */
class Safe_updateControllerRun extends JControllerLegacy {
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct() {
		parent::__construct();
	}
    public function restore() {
        $compare = new CompareHashFiles();
        
        $compare
            ->setVersion(JVersion::RELEASE . '.' . JVersion::DEV_LEVEL)
            ->setJoomlaRoot(JPATH_ROOT);
        
        $view = $this->getView('finally', 'html');
        
        $session = JFactory::getSession();
        
        $file = $session->get('file');
        
        $compare->restore($file);
        $view->assign('diffs', $session->get('diffs'));
        $input = JFactory::getApplication()->input;
        $view->assign('returnurl', $input->get('returnurl'));

        $view->display();

		return $this;
    }
    public function comparecache() {
        $compare = new CompareHashFiles();
        $view = $this->getView('compare', 'html');
        
        $session = JFactory::getSession();

        $view->assign('diffs', $session->get('diffs'));
        $view->assign('file', $session->get('file'));
        $input = JFactory::getApplication()->input;
        $view->assign('returnurl', $input->get('returnurl'));

		$view->display();

		return $this;
    }

    public function compareandsave() {
        $compare = new CompareHashFiles();

        $compare
            ->setVersion(JVersion::RELEASE . '.' . JVersion::DEV_LEVEL)
            ->setJoomlaRoot(JPATH_ROOT)
            ->startCompare()
            ->saveChanged();

        $session = JFactory::getSession();
        $session->set('diffs', $compare->diff);
        $session->set('file', $compare->getTempFile(true));
        return $this;
    }

    public function compare() {

        $view = $this->getView('compare', 'html');

        $this->compareandsave();
        
        $session = JFactory::getSession();

        $view->assign('diffs', $session->get('diffs'));
        $view->assign('file', $session->get('file'));
        $view->assign('returnurl', false);

		$view->display();

		return $this;
    }
}
