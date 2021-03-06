<?php
/**
 *
 * @category   Baobaz
 * @package    Baobaz_OrdersCancel
 * @author     Laurent Clouet <laurent35240@gmail.com>
 * @date       08/08/12
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Baobaz_OrdersCancel_Model_Observer {
    private $log_file;
    private $status_to_cancel;
    private $expiration_time;

    /**
     * Cancel orders which are getting moldy
     * @param $schedule
     */
    public function cancelOldOrders($schedule) {
        //configuration
        $this->log_file = Mage::getStoreConfig('sales/orderscancel/log_file');
        $this->status_to_cancel = Mage::getStoreConfig('sales/orderscancel/status_to_cancel');
        $days = (int)Mage::getStoreConfig('sales/orderscancel/expiration_days');
        $hours = (int)Mage::getStoreConfig('sales/orderscancel/expiration_hours');
        $minutes = (int)Mage::getStoreConfig('sales/orderscancel/expiration_minutes');
        $this->expiration_time = (($days * 24 + $hours) * 60 + $minutes) * 60;

        $status = explode(',', $this->status_to_cancel);
       
        //getting all orders needed to be process
        $order_model = Mage::getModel('sales/order');
        /** @var $orders Mage_Sales_Model_Resource_Order_Collection */
        $orders = $order_model->getCollection();
        $orders->addAttributeToFilter('status', array('in' => $status));
        $orders->addAttributeToFilter('created_at', array('lt' => date('Y-m-d H:i:s', time() - $this->expiration_time)));

        $nb_orders = count($orders);

        if($nb_orders > 0) {
            Mage::log('Beginning of cancelling '. $nb_orders .' old order(s)', null, $this->log_file);
            foreach ($orders as $order) {
                /** @var $order Mage_Sales_Model_Order */
                try{
                    $order = Mage::getModel('sales/order')->load($order->getId());
                    if ($order->canCancel()) {
                        $order->cancel();
                        //Adding comment for differentiating order automatically or manually canceled
                        $order->addStatusHistoryComment('Order automatically canceled because older than '. $days .' day(s) '. $hours .' hour(s) and '. $minutes .' minute(s)', $order->getStatus());
                        $order->save();
                        Mage::log('Order ' . $order->getRealOrderId() . ' canceled', null, $this->log_file);
                    }
                    else {
                        Mage::log('Order '.$order->getRealOrderId().' couldn\'t be canceled', null, $this->log_file);
                    }
                }
                catch(Exception $e){
                    $message = 'Error while canceling order ' . $order->getRealOrderId() . ': ' . $e->getCode() . '' . $e->getMessage();
                    Mage::log($message, null, $this->log_file);
                    continue;
                }
            }
            Mage::log('End of cancelling '. $nb_orders .' old order(s)', null, $this->log_file);
        }
    }
}


?>
