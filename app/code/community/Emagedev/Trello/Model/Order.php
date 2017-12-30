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
 * the Emagedev Trello module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_List
 *
 * Order - Trello Card connection
 *
 * @method $this setOrderId(int $orderId)
 * @method int getOrderId()
 * @method $this setCardId(string $cardId)
 * @method string getCardId()
 * @method $this setArchived(bool $archived)
 * @method bool getArchived()
 */
class Emagedev_Trello_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/order');
    }

    /**
     * Sometimes
     *
     * @return Mage_Core_Model_Abstract
     */
    public function _beforeSave()
    {
        if (is_null($this->getCardId()) || $this->getCardId() === '') {
            Mage::throwException('Fetch card_id before saving Trello card model');
        }

        return parent::_beforeSave();
    }
}
