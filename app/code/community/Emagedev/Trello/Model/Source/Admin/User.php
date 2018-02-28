<?php


class Emagedev_Trello_Model_Source_Admin_User
{
    /** @var  array */
    protected $options;

    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        if (is_null($this->options)) {
            $options = array('label' => 'Not Connected');

            try {
                /** @var Mage_Admin_Model_Resource_User_Collection $collection */
                $collection = Mage::getModel('admin/user')->getCollection();
                $collection
                    ->addFieldToFilter('is_active', true);

                /** @var Mage_Admin_Model_User $user */
                foreach ($collection as $user) {
                    $options[] = array('value' => $user->getId(), 'label' => implode(' ', array($user->getFirstname(), $user->getLastname())) . ' (' . $user->getId() . ')');
                }

                $this->options = $options;
            } catch (Exception $e) {
                Mage::logException($e);
                return array();
            }
        }

        return $this->options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}