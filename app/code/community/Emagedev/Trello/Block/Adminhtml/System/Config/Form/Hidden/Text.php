<?php


class Emagedev_Trello_Block_Adminhtml_System_Config_Form_Hidden_Text
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $element;

    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('trello/system/config/hidden_text.phtml');
    }

    /**
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->element;
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
        $this->element = $element;

        return $this->_toHtml();
    }
}