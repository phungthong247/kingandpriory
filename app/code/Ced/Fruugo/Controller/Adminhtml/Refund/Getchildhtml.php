<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Fruugo\Controller\Adminhtml\Refund;

class Getchildhtml extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Getchildhtml constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
    }

    /**
     * Index Action
     * @return Object
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('mer_id')) {
            $helper = $this->_objectManager->create('Ced\Fruugo\Helper\Fruugo');
            $msg['success']="";
            $msg['error']="";
            $magentoOrderId="";
            $orderData='';
            $shipmentData='';
            $merchantOrderId=$this->getRequest()->getParam('mer_id');
            $merchantOrderId=trim($merchantOrderId);
            if ($merchantOrderId) {
                $return = true;//$this->checkReturnAlreadyGenerated($merchantOrderId);
                if ($return === false) {
                    return $return;
                }
            }
            if (!isset($merchantOrderId)) {
                $msg['error']="Please enter merchant order id :".$merchantOrderId;
                return $this->getResponse()->setBody( json_encode($msg) );
            }
            $purchaseOrderId = '';
            $collection="";
            try{
                $collection=$this->_objectManager->get('Ced\Fruugo\Model\FruugoOrders')->getCollection();
                $collection->addFieldToFilter( 'purchase_order_id', $merchantOrderId );
                if ($collection->getSize()>0) {
                    foreach ($collection as $coll) {
                        $magentoOrderId = $coll->getData('magento_order_id');
                        $orderData = $coll->getData('order_data');
                        $shipmentData = $coll->getData('shipment_data');
                        $purchaseOrderId = $coll->getData('purchase_order_id');
                        break;
                    }
                } else {
                    $collection=$this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')->getCollection();
                    $collection->addFieldToFilter( 'merchant_order_id', $merchantOrderId );
                    if ($collection->getSize()>0) {
                        foreach ($collection as $coll) {
                            $magentoOrderId=$coll->getData('magento_order_id');
                            /*$coll->setData('order_data',json_encode($this->_objectManager->create('Ced\Fruugo\Helper\Data')->getOrder('4576738287280')['elements']['order'][0]));*/
                            $orderData=$coll->getData('order_data');
                            $shipmentData=$coll->getData('shipment_data');
                            $purchaseOrderId = $coll->getData('purchase_order_id');
                            break;
                        }
                    }
                }

                $updatedRefundqtyData=$helper->getUpdatedRefundQty($merchantOrderId);
                //echo "<pre>";print_r($updatedRefundqtyData);die('fg');

                $refundcollection=$this->_objectManager->create('Ced\Fruugo\Model\FruugoRefund')->getCollection()
                    ->addFieldToFilter('refund_purchaseOrderId', $merchantOrderId );
                $refund_qty= [];
                //if ($refundcollection->getSize()>0) {
                    /*foreach ($refundcollection as $coll) {
                        $refund_data = json_decode($coll->getData('saved_data'));
                    }*/
                    /*$msg['error']="Refund Already Generated for this PO ".$merchantOrderId;
                    return $this->getResponse()->setBody( json_encode($msg) );
                }*/

                if ($magentoOrderId == "" || $orderData == '') {
                    $msg['error']="Order not found.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }

                $order_decoded_data = "";
                $itemsData = [] ;
                //$shipmentData = $this->_objectManager->create('Ced\Fruugo\Helper\Data')->getOrder($purchaseOrderId);
                //$shipmentData = isset($shipmentData['elements']['order']['0']) ? $shipmentData['elements']['order']['0'] : '';
                /*$shipmentData=json_decode(str_replace('ns3:', '', $shipmentData['shippedData']),true)['order'];*/
                $shipmentData = json_decode($shipmentData,true);
                //echo "<pre>";print_r($shipmentData);die('fg');
                if (!empty($shipmentData['shipments'])) {
                    foreach($shipmentData['shipments'] as $shipment) {
                        foreach ($shipment['shipment_items'] as $shipItems) {
                            $itemsData[] = $shipItems;
                        }
                    }
                } else {

                    $msg['error']="No order Data found . Please try after some time.";
                    return $this->getResponse()->setBody( json_encode($msg) );

                }

                if (count($itemsData)<=0) {
                    $msg['error']="Items Data not found for selected Order.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }
                $order ="";
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magentoOrderId);
                if (!$order->getId()) {

                    $msg['error']="Order data not found.Please enter correct Order Id.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }

                if ($order->getStatus()!='complete') {
                    $msg['error']="Can't generate refunds for incompleted orders.This order is incomplete.";
                    return $this->getResponse()->setBody( json_encode($msg) );
                }
                $return_flag=false;
                $error_msg='';
                $j=0;
                foreach ($itemsData as $item) {
                    $merchant_sku="";
                    $merchant_sku = $item['merchant_sku'];
                    $check = [];
                    $check=$helper->getRefundedQtyInfo($order, $merchant_sku);

                    if ($check['error']=='1') {
                        $error_msg=$error_msg."Error for Order Item with sku : ".$merchant_sku."-> ";
                        $error_msg=$error_msg.$check['error_msg'];
                        continue;
                    }
                    $j++;
                }
                if ($j==0) {
                    $msg['error']=$error_msg;
                    return $this->getResponse()->setBody( json_encode($msg) );
                }
                $resultPage = $this->resultPageFactory->create();
                $html=$resultPage->getLayout()
                    ->createBlock('Ced\Fruugo\Block\Adminhtml\Refund')->setTemplate("refund/refundhtml.phtml")
                    ->setData('items_data', $itemsData)
                    ->setData('helper', $helper)
                    ->setData('order', $order)
                    ->setData('merchant_order_id', $merchantOrderId)
                    ->setData('refundtotalqty', $updatedRefundqtyData)
                    ->setData('objectManager', $this->_objectManager)
                    ->setData('purchaseOrderId', $purchaseOrderId)
                    ->toHtml();
                $msg['success']=$html;
                $this->getResponse()->setBody(
                    json_encode($msg)
                );
                return false;
            }catch(\Exception $e) {
                $msg['error']=$e->getMessage();
                return $this->getResponse()->setBody( json_encode($msg) );

            }


        } else {
            $msg['error']="Merchant Order Id not found.Please enter again.";
            return $this->getResponse()->setBody( json_encode($msg) );

        }
    }

    /**
     * CheckReturnAlreadyGenerated
     * @param string $merchantOrderId
     * @return bool
     */
    public function checkReturnAlreadyGenerated($merchantOrderId)
    {
        $collection = $this->_objectManager->create('Ced\Fruugo\Model\OrderReturn')->getCollection()
            ->addFieldToSelect('merchant_order_id')
            ->addFieldToFilter('merchant_order_id', $merchantOrderId);

        if ($collection->getSize() > 0) {
            $msg['error']="Return already generated for merchant order id :".$merchantOrderId;
            return $this->getResponse()->setBody( json_encode($msg) );
        } else {
            return true;
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }

}
