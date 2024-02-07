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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Refund;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Fruugo::refund');
        $resultPage->getConfig()->getTitle()->prepend(__('Fruugo Refund'));
        $postData = $this->getRequest()->getParams();
        $dataHelper = $this->_objectManager->create('Ced\Fruugo\Helper\Data');
        $orderHelper = $this->_objectManager->create('Ced\Fruugo\Helper\Order');
        $order ="";
        $skudetails=$this->getRequest()->getParam('sku_details');

        $model = $this->_objectManager->create('Ced\Fruugo\Model\FruugoRefund');
        $orderModel = $this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')
            ->load($postData['refund_orderid'], 'purchase_order_id');
        $magentoOrderId = $orderModel->getMagentoOrderId();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magentoOrderId);
        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('Order not Exists for this refund.'));
            return $this->_redirect('*/*/refundgrid');
        }
        if (empty($skudetails)) {
            $this->messageManager->addErrorMessage(__('Please select any Item of Order to 
				refund.'));
            return $this->_redirect('*/*/refundgrid');
        }
        $item_refunded = '';
        if (!empty($postData['sku_details'])) {
            $cancelledArray = [];
            $response = [];
            foreach ($postData['sku_details'] as $sku) {
                $this->validateDetails($sku);
                $orderData['purchaseOrderId'] = $postData['refund_orderid'];
                //$lineNumbers = explode(',', $sku['lineNumber']);
                $refundQuantity = $sku['refund_quantity'];
                //$lineNumbers = array_chunk($lineNumbers, $refundQuantity);
                /*if (isset($lineNumbers[1])) {
                    $refundLineNumbers = $lineNumbers[1];
                } else {
                    $refundLineNumbers = $lineNumbers[0];
                }*/
                /*$orderData['amount'] = '-'.(float)trim($sku['return_actual_principal']);
                $orderData['taxAmount'] = '-'.(float)trim($sku['return_tax']);
                $orderData['taxName'] = 'Item Price Tax';
                $orderData['shipping'] = '-'.(float)trim($sku['return_shipping_cost']);
                $orderData['shippingTax'] = '-'.$sku['return_shipping_tax'];
                $orderData['refundReason'] = 'DamagedItem';
                $orderData['refunReasonShipping'] = 'TaxExempt';
                $orderData['refundComments'] = $sku['return_refundfeedback'];*/
                $skutemp = $sku['merchant_sku'];
                $item_refunded .= '&item='.$sku['fruugo_prodID'].','.$sku['fruugo_skuID'].','.$sku['refund_quantity'];
                /*foreach ($refundLineNumbers as $refundLineNumber) {
                    $orderData['lineNumber'] = $refundLineNumber;*/
                    //$response[] = $dataHelper->refundOrder($orderData['purchaseOrderId'], $orderData);
                    /*$collection = $this->_objectManager->create(
                        'Magento\Sales\Model\Order\Item')->getCollection()->addFieldToFilter(
                        'sku', $skutemp);
                    foreach ($collection as $data) {
                        $cancelledArray[$data->getItemId()] = $refundQuantity;
                        $id=$data->getItemId();
                        break;
                    }
                    $itemmodel=$this->_objectManager->create(
                        'Magento\Sales\Model\Order\Item')->load($id);
                    $itemmodel->setData(
                        'qty_refunded', $sku['refund_quantity']);
                    $itemmodel->save();*/
                foreach($order->getAllItems() as $orderItem)
                {
                    if($orderItem->getSku() == $skutemp ) {
                        $cancelledArray[$orderItem->getId()] = $sku['refund_quantity'];
                    }
                }
                //}
            }
            //echo "<pre>";print_r($cancelledArray);die('g');
            $refund_items = "orderId=" . $postData['refund_orderid'] . $item_refunded;
            if(!empty($postData['refund_reason'])) {
                $refund_items .= "&returnReason=".$postData['refund_reason'];
            }
            if(!empty($postData['msg_to_customer'])) {
                $refund_items .= "&messageToCustomer=".$postData['msg_to_customer'];
            }
            if(!empty($postData['msg_to_fruugo'])) {
                $refund_items .= "&messageToFruugo=".$postData['msg_to_fruugo'];
            }
            $response[] = $dataHelper->postRequest('orders/return', $refund_items);
            if (true) {
                $model->setData('magento_order_id', $magentoOrderId)
                    ->setStatus('Complete')
                    ->setData('refund_purchaseOrderId', $postData['refund_orderid'] )
                    ->setData('refund_status', 'Success' )
                    ->setData('refund_reason', $postData['refund_reason'] )
                    ->setData('msg_to_customer', $postData['msg_to_customer'] )
                    ->setData('msg_to_fruugo', $postData['msg_to_fruugo'] )
                    ->setSavedData(json_encode($postData,true));
                $model->save();
                $orderHelper->generateCreditMemo($order, $cancelledArray);
                $this->messageManager->addSuccessMessage(__('Refund Generated Successfully'));
                return $this->_redirect('fruugo/refund/refundgrid');
            } else {
                $this->messageManager->addErrorMessage(__('Error Generating Refund'));
                return $this->_redirect('fruugo/refund/refundgrid');
            }
        }
        $this->messageManager->addErrorMessage(__('No Data Provided'));
        $this->_redirect('fruugo/refund/refundgrid');

        return $resultPage;
    }


    /**
     * Validate details
     * @param string $detail
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function validateDetails($detail)
    {
        /*if ($detail['return_quantity']=="" || $detail['return_quantity']<0) {
            $this->messageManager->addErrorMessage("Please enter Qty Returned for sku : 
				".$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        if ($detail['refund_quantity']=="") {
            $this->messageManager->addErrorMessage("Please enter Qty Refunded for sku : 
				".$detail['merchant_sku']);
            return $this->_redirect('*/*/refundgrid');
        }

        /*if ($detail['return_principal']<0) {
            $this->messageManager->addErrorMessage("Please enter  correct Refund Amount 
				for sku : ".$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        /*if ($detail['return_shipping_cost']<0) {
            $this->messageManager->addErrorMessage(
                "Please enter  correct Refund Shipping Cost for sku : "
                .$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        /*if ($detail['return_shipping_tax']<0) {
            $this->messageManager->addErrorMessage(
                "Please enter  correct Refund Shipping Tax for sku : "
                .$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        /*if ($detail['return_tax']<0) {
            $this->messageManager->addErrorMessage("Please enter  correct Refund Tax for 
				sku : ".$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        /*if ($detail['return_refundreason']=="") {
            $this->messageManager->addErrorMessage("Please enter Refund reason for sku :
				".$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
        /*if ($detail['return_refundfeedback']=="") {
            $this->messageManager->addErrorMessage("Please enter Refund reason for sku :
			 ".$detail['merchant_sku']);*/
            //return $this->_redirect('*/*/refundgrid');
        //}
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }
}