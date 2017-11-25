<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * Copyright (C) Effdocs, LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * This source file is proprietary and confidential
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Startpage module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Helper
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Helper_Data
 *
 * Usable methods
 */
class Omedrec_Trello_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Array of lists sorted by status code
     *
     * @var [status => Omedrec_Trello_Model_List]
     */
    protected $statusLists;

    /**
     * Get link to trello list that representing given status
     *
     * @param $statusCode
     *
     * @return bool|Omedrec_Trello_Model_List
     */
    public function getStatusListId($statusCode)
    {
        if ($statusCode instanceof Mage_Sales_Model_Order_Status) {
            $statusCode = $statusCode->getStatus();
        }

        $list = $this->getStatusLists()[$statusCode];

        return $list instanceof Omedrec_Trello_Model_List ? $list : false;
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
            /** @var Omedrec_Trello_Model_Resource_List_Collection $lists */
            $statusLists = Mage::getModel('trello/list')->getCollection();

            $this->statusLists = array();

            /** @var Omedrec_Trello_Model_List $list */
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