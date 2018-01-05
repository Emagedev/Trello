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
 * @subpackage Helper
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Helper_Data
 *
 * Usable methods
 */
class Emagedev_Trello_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Array of lists sorted by status code
     *
     * @var [status => Emagedev_Trello_Model_List]
     */
    protected $statusLists;

    /**
     * Get link to trello list that representing given status
     *
     * @param $statusCode
     *
     * @return bool|Emagedev_Trello_Model_List
     */
    public function getStatusListId($statusCode)
    {
        if ($statusCode instanceof Mage_Sales_Model_Order_Status) {
            $statusCode = $statusCode->getStatus();
        }

        if (array_key_exists($statusCode, $this->getStatusLists())) {
            $list = $this->getStatusLists()[$statusCode];
        } else {
            return false;
        }

        return $list instanceof Emagedev_Trello_Model_List ? $list : false;
    }

    /**
     * Get status board id
     *
     * @return string
     */
    public function getBoardId()
    {
        return Mage::getStoreConfig('trello_api/order_status/board_id');
    }

    /**
     * Get all board lists related to status
     *
     * @return array
     */
    public function getStatusLists()
    {
        if (is_null($this->statusLists)) {
            /** @var Emagedev_Trello_Model_Resource_List_Collection $lists */
            $statusLists = Mage::getModel('trello/list')->getCollection();

            $this->statusLists = array();

            /** @var Emagedev_Trello_Model_List $list */
            foreach ($statusLists as $list) {
                $this->statusLists[$list->getStatus()] = $list;
            }
        }

        return $this->statusLists;
    }

    /**
     * Drop cached status lists
     *
     * @return $this
     */
    public function dropListCache()
    {
        $this->statusLists = null;
        return $this;
    }
}