<?php


class Emagedev_Trello_Block_Adminhtml_System_Config_Form_Update
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('trello/system/config/update.phtml');
    }

    public function getLastCheck()
    {
        return Mage::getStoreConfig('trello_api/webhook/check');
    }

    protected function _toHtml()
    {
        return '<p>' . $this->getLastCheck() . '</p>';
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