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
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Model_List
 *
 * Trello List - Order Status connection
 *
 * @method $this setStatus(string $status)
 * @method string getStatus()
 * @method $this setListId(string $listId)
 * @method string getListId()
 */
class Omedrec_Trello_Model_List extends Mage_Core_Model_Abstract
{
    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/list');
    }
}
