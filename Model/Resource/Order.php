<?php
/**
 * Effdocs LLC extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Trello module to newer versions in the future.
 * If you wish to customize the Omedrec Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @copyright  Copyright (C) 2017 Copyright (C) Effdocs, LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Omedrec_Trello_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Init the table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('trello/order', 'link_id');
    }

    /**
     * Join order cards an filter not archived
     *
     * @param Mage_Sales_Model_Resource_Order_Collection $orderCollection
     *
     * @return $this
     */
    public function filterOrdersWithActiveCards($orderCollection)
    {
        $orderCollection
            ->getSelect()
            ->joinInner(
                array('order_card' => Mage::getSingleton('core/resource')->getTableName('trello/order')),
                'order_card.order_id = main_table.entity_id AND archived = 0'
            );

        return $this;
    }
}