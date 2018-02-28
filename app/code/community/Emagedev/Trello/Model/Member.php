<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Welcome module to newer versions in the future.
 * If you wish to customize the Omedrec Welcome module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Member
 *
 * @method $this setAdminUserId(int $userId)
 * @method int getAdminUserId()
 * @method $this setTrelloMemberId(string $memberId)
 * @method string getTrelloMemberId()
 * @method $this setFullName(string $fullName)
 * @method string getFullName()
 */
class Emagedev_Trello_Model_Member extends Mage_Core_Model_Abstract
{
    protected $adminUser;

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/member');
    }

    public function getAdminUser()
    {
        if (is_null($this->adminUser)) {
            $userId = $this->getAdminUserId();

            if ($userId) {
                $adminUser = Mage::getModel('admin/user')->load($userId);

                if ($adminUser && $adminUser->getId())
                $this->adminUser = $adminUser;
            }
        }

        return $this->adminUser;
    }

    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'trello_member_id');
    }
}
