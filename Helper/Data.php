<?php


class Omedrec_Trello_Helper_Data extends Mage_Core_Helper_Data
{
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

        /** @var Omedrec_Trello_Model_List $list */
        foreach ($this->getStatusLists() as $list) {
            if ($list->getStatus() == $statusCode) {
                return $list;
            }
        }

        return false;
    }

    /**
     * @return Omedrec_Trello_Model_Resource_List_Collection
     */
    public function getStatusLists()
    {
        if (is_null($this->statusLists)) {
            /** @var Omedrec_Trello_Model_Resource_List_Collection $lists */
            $this->statusLists = Mage::getModel('trello/list')->getCollection();
        }

        return $this->statusLists;
    }
}