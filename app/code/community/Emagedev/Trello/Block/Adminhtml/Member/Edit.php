<?php
/**
 * J.R. Dunn Jewelers. extension for Magento
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
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @copyright  Copyright (C) 2018 J.R. Dunn Jewelers.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Block
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Block_Adminhtml_Member_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init grid container
     */
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_member';
        $this->_blockGroup = 'trello';
    }

    /**
     * Get container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $data = Mage::registry('current_trello_member');
        if ($data) {
            return $this->__('Edit Item #%d', $this->escapeHtml($data->getId()));
        } else {
            return $this->__('New Item');
        }
    }
}
