<?php


class Emagedev_Trello_Block_Adminhtml_System_Config_Form_Status
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('trello/system/config/status.phtml');
    }

    /**
     * Get current hook status
     *
     * @return bool
     */
    public function ok()
    {
        return (bool)Mage::getStoreConfigFlag('trello_api/webhook/status');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
}