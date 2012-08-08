<?php
/**
 *
 * @category   Baobaz
 * @package    Baobaz_OrdersCancel
 * @author     Laurent Clouet <laurent35240@gmail.com>
 * @date       08/08/12
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Baobaz_OrdersCancel_Model_Order_Statuses
{
    /**
     * States for which we can cancel an order
     * @var array
     */
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
    );

    /**
     * Gives array of all order statuses that can be canceled
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $orderConfig Mage_Sales_Model_Order_Config */
        $orderConfig = Mage::getSingleton('sales/order_config');
        if ($this->_stateStatuses) {
            $statuses = $orderConfig->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = $orderConfig->getStatuses();
        }
        $options = array();
        $options[] = array(
               'value' => '',
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            );
        foreach ($statuses as $code=>$label) {
                $options[] = array(
                   'value' => $code,
                   'label' => $label
                );
        }
        return $options;
    }

}