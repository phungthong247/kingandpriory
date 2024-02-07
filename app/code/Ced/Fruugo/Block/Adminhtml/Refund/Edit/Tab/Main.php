<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Fruugo
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Fruugo\Block\Adminhtml\Refund\Edit\Tab;

/**
 * Return edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $_systemStore;


    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $_wysiwygConfig;


    /**
     * Main constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {

        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
        ;
    }

    /**
     * @return $this
     */
    public function _prepareForm()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Ced\Fruugo\Model\FruugoRefund')->load($this->getRequest()->getParam('id'));//$this->_coreRegistry->registry('ced_refund_data');
//        var_dump($this->getRequest()->getParam('id'));die;
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        //$form->setHtmlIdPrefix('fruugo');

        $fieldset = $form->addFieldset('fruugo_Refund', ['legend' => __('View Return')]);


        /* $fieldset->addField('refund_id', 'hidden', ['name' => 'id']); */

        $html3="";
        $html3='<script type="text/javascript">'.
            'function checkamount(ele) {'.
            'var id=ele.id;'.
            'var new_id=id.slice(12);'.
            'var qty = document.getElementById("qty_refunded"+new_id).value;var avail_qty = document.getElementById("available_to_refund_qty"+new_id).value;'.
            'if (qty != "n/a") {var amt=(document.getElementById("actual_amount"+new_id).value)*qty;} else {var amt=document.getElementById("actual_amount"+new_id).value;}'.
            'if (ele.value == 0 || ele.value == "") {'.
            'document.getElementById("amount"+new_id).value=0;'.
            'ele.value=0;'.
            'document.getElementById("return_refundfeedback"+new_id).disabled = true;'.
            'document.getElementById("return_refundreason"+new_id).disabled = true;'.
            '}else if (qty > avail_qty) {alert("Total available quantity to refund : "+avail_qty);document.getElementById("qty_refunded"+new_id).value = avail_qty;} else {'.
            'document.getElementById("amount"+new_id).value=amt;'.
            'document.getElementById("qty_returned"+new_id).value=qty;'.
            'document.getElementById("return_refundfeedback"+new_id).disabled = false;'.
            'document.getElementById("return_refundreason"+new_id).disabled = false;'.
            '}'.
            '}'.
            '</script>';

        $html2="";
        $html2='<script type="text/javascript">function showreturndiv(f,b,n) {
                                document.getElementById(b).style.display = "block";
                                document.getElementById(n).style.display = "none";
                                container=document.getElementById(f);
                               container.style.display = "block";
                                var tagNames = ["INPUT", "SELECT", "TEXTAREA" ,"BUTTON"];
                                      for(var i = 0; i<tagNames.length; i++) {
                                        var elems = container.getElementsByTagName(tagNames[i]);
                                        for(var j = 0; j<elems.length; j++) {
                                          elems[j].disabled = false;
                                        }
                                }
                          }function hidereturndiv(f,b,n) {
                                document.getElementById(b).style.display = "block";
                                document.getElementById(n).style.display = "none";
                                container=document.getElementById(f);
                               container.style.display = "none";
                                var tagNames = ["INPUT", "SELECT", "TEXTAREA" ,"BUTTON"];
                                      for(var i = 0; i<tagNames.length; i++) {
                                        var elems = container.getElementsByTagName(tagNames[i]);
                                        for(var j = 0; j<elems.length; j++) {
                                          elems[j].disabled = true;
                                        }
                                }
                          }</script>';
        $div="<div class='catch_child' id='catch_child'></div>";
        $html1="";
        $html1='<script type="text/javascript">'.
            'document.addEventListener("DOMContentLoaded",function() {'.
            'var h1="'.$div.'";'.
            'document.getElementById("fruugo_Refund").innerHTML=document.getElementById("fruugo_Refund").innerHTML+h1;'

            .'});'
            .'</script>';
        $url="";
        $url=$this->getUrl('*/*/getchildhtml');
        $html="";
        $html='<script type="text/javascript">'.
            'function loadchildren() { '.
            /* 'var element = document.getElementByClass("admin__page-nav-item-messages");'.
            'element.parentNode.removeChild(element);'. */
            'var val=document.getElementById("refund_orderid").value;'.
            'if (val.length<=0) {'.
            'return;'.
            '}'.
            'var url="'.$url.'mer_id/"+val;'.
            'new Ajax.Request(url, {'.
            'method: "post",'.
            'onSuccess: function(transport) {'.
            'var html = transport.responseText.evalJSON();'.
            'if (html.success) {'.
            'document.getElementById("catch_child").innerHTML=html.success;'.
            '} else {'.
            'document.getElementById("catch_child").innerHTML="";'.
            'alert(html.error);'.
            '}'
            .' },
                      onFailure: function() {'.
            'alert("Something Went Wrong.Please try again.");'.
            '},'.
            '});'.

            '}</script>';
        if(empty($this->getRequest()->getParam('id'))) {
            $fieldset->addField(
                'refund_orderid',
                'text',
                [
                    'name' => 'refund_orderid',
                    'label' => __('Enter Merchant Order ID or  Reference Order ID'),
                    'title' => __('Enter Merchant Order ID or  Reference Order ID'),
                    'required' => true,
                    'note' => 'Please fill Merchant Order Id to be refund.',
                    'disabled' => $isElementDisabled
                ]
            )->setAfterElementHtml($html.$html1.$html2.$html3);
        }


        // for refund edit form view
        if ($model->getData('refund_purchaseOrderId'))
        {
            $helper=$objectManager->create('Ced\Fruugo\Helper\Data');
            $fruugoHelper=$objectManager->create('Ced\Fruugo\Helper\Fruugo');
            $feedback_arr=$fruugoHelper->feedbackOptArray();
            $reason_arr=$fruugoHelper->refundreasonOptionArr();
            //$refund_unserilized = $helper->getOrder($model->getData('refund_purchaseOrderId'))['elements']['order'][0];
            $refund_data = json_decode($model->getData('saved_data'),true);
            $i=0;
            $fieldset->addField(
                'purchase_orderID',
                'text',
                [
                    'name' =>  'purchase_orderID',
                    'label' => __('Order ID'),
                    'title' => __('Order Id'),
                    'value' => $refund_data['purchase_orderID'],
                    'required' => true,
                    'note' => 'Purchase Order Id',
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'msg_to_customer',
                'textarea',
                [
                    'name' =>  'msg_to_customer',
                    'label' => __('Message To Customer'),
                    'title' => __('Message To Customer'),
                    'values' => __($refund_data['msg_to_customer']),
                    'required' => true,
                    'note' => 'Message To Customer',
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'msg_to_fruugo',
                'textarea',
                [
                    'name' =>  'msg_to_fruugo',
                    'label' => __('Message To Fruugo'),
                    'title' => __('Message To Fruugo'),
                    'values' => __($refund_data['msg_to_fruugo']),
                    'required' => true,
                    'note' => 'Message To Fruugo',
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'refund_reason',
                'select',
                [
                    'name' =>  'refund_reason',
                    'label' => __('Refund Reason'),
                    'title' => __('Refund Reason'),
                    'values' => $reason_arr,
                    'value' => __($refund_data['refund_reason']),
                    'required' => true,
                    'note' => 'Refund Reason',
                    'disabled' => $isElementDisabled
                ]
            );
            foreach ($refund_data['sku_details'] as $key => $data)
            {
                //$data=$refund_unserilized["sku_details"][$key];

                $fieldset1 = $fieldset->addFieldset(
                    "sku_return_".$i, ['legend'=> __('sku : '.$data['merchant_sku'])]);
                /*$fieldset1->addField(
                    'refund_id'.$key,
                    'text',
                    [
                        'name' => 'refund_id'.$key,
                        'label' => __('Refund Id'),
                        'title' => __('Refund Id'),
                        'required' => true,
                        'note' => 'Please fill refund Id to be refund.',
                        'disabled' => $isElementDisabled,
                        'readonly'=> true
                    ]
                );*/

                $fieldset1->addField(
                    'sku'.$key,
                    'text',
                    [
                        'name' =>  'sku'.$key,
                        'label' => __('sku'),
                        'title' => __('sku'),
                        'required' => true,
                        'note' => 'sku',
                        'disabled' => $isElementDisabled
                    ]
                );
                /*$fieldset1->addField(
                    'order_item_id'.$key,
                    'text',
                    [
                        'name' =>  'order_item_id'.$key,
                        'label' => __('Item Order Id'),
                        'title' => __('Item Order Id'),
                        'required' => true,
                        'note' => 'Order Id to be refund.',
                        'disabled' => $isElementDisabled
                    ]
                );*/
                $fieldset1->addField(
                    'fruugo_product_id'.$key,
                    'text',
                    [
                        'name' =>  'fruugo_product_id'.$key,
                        'label' => __('Fruugo Prodyct Id'),
                        'title' => __('Fruugo Prodyct Id'),
                        'required' => true,
                        'note' => 'Order Id to be refund.',
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'fruugo_sku_id'.$key,
                    'text',
                    [
                        'name' =>  'fruugo_sku_id'.$key,
                        'label' => __('Fruugo Sku Id'),
                        'title' => __('Fruugo Sku Id'),
                        'required' => true,
                        'note' => 'Order Id to be refund.',
                        'disabled' => $isElementDisabled
                    ]
                );
                /*$fieldset1->addField(
                    'quantity_returned'.$key,
                    'text',
                    [
                        'name' => 'quantity_returned'.$key,
                        'label' => __('Quantity Returned'),
                        'title' => __('Quantity Returned'),
                        'required' => true,
                        'note' => 'Quantity Available For Refund.',
                        'disabled' => $isElementDisabled
                    ]
                );*/
                $fieldset1->addField(
                    'available_refund_quantity'.$key,
                    'text',
                    [
                        'name' =>  'available_refund_quantity'.$key,
                        'label' => __('Quantity availbel to be refunded'),
                        'title' => __('Quantity to be refunded'),
                        'required' => true,
                        'note' => 'Quantity Available For Refund.',
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'refund_quantity'.$key,
                    'text',
                    [
                        'name' =>  'refund_quantity'.$key,
                        'label' => __('Quantity refunded'),
                        'title' => __('Quantity refunded'),
                        'required' => true,
                        'note' => 'Quantity Available For Refund.',
                        'disabled' => $isElementDisabled
                    ]
                );
                /*$fieldset1->addField(
                    'refund_amount'.$key,
                    'text',
                    [
                        'name' =>  'refund_amount'.$key,
                        'label' => __('Refund Amount'),
                        'title' => __('Quantity Available For refund'),
                        'required' => true,
                        'note' => 'Quantity Available For Refund.',
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'refund_tax'.$key,
                    'text',
                    [
                        'name' =>  'refund_tax$key'.$key,
                        'label' => __('Refund Tax'),
                        'title' => __('Quantity Available For refund'),
                        'required' => true,
                        'note' => 'Quantity Available For Refund.',
                        'disabled' => $isElementDisabled
                    ]
                );

                $fieldset1->addField(
                    'refund_shipping_cost'.$key,
                    'text',
                    [
                        'name' =>  'refund_shipping_cost'.$key,
                        'label' => __('Refund Shipping Cost'),
                        'title' => __('Refund Shipping Cost'),
                        'required' => true,
                        'note' => 'Refund Shipping Cost',
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'refund_shipping_tax'.$key,
                    'text',
                    [
                        'name' => 'refund_shipping_tax'.$key,
                        'label' => __('Refund Shipping Tax'),
                        'title' => __('Refund Shipping Tax'),
                        'required' => true,
                        'note' => 'Refund Shipping Tax',
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'return_refundfeedback'.$key,
                    'select',
                    [
                        'name' => 'return_refundfeedback'.$key,
                        'label' => __('Refund Feedback'),
                        'title' => __('Refund Feedback'),
                        'required' => true,
                        'note' => 'Refund Feedback',
                        'values'=>$feedback_arr,
                        'disabled' => $isElementDisabled
                    ]
                );
                $fieldset1->addField(
                    'return_refundreason'.$key,
                    'select',
                    [
                        'name' => 'return_refundreason'.$key,
                        'label' => __('Refund Reason'),
                        'title' => __('Refund Reason'),
                        'required' => true,
                        'values'=>$reason_arr,
                        'note' => 'Refund Reason',
                        'disabled' => $isElementDisabled
                    ]
                );*/


                $model->setData('sku'.$key,$data['merchant_sku'])
                    ->setData('refund_quantity'.$key, $data['refund_quantity'])
                    ->setData('available_refund_quantity'.$key,$data['available_to_refund_qty'])
                    //->setData('quantity_returned'.$key,1)
                    /*->setData('order_item_id'.$key,$model->getData('magento_order_id'))*/
                    ->setData('fruugo_product_id'.$key,$data['fruugo_prodID'])
                    ->setData('purchase_orderID', $model->getData('refund_purchaseOrderId'))
                    ->setData('msg_to_customer', $model->getData('msg_to_customer'))
                    ->setData('msg_to_fruugo', $model->getData('msg_to_fruugo'))
                    ->setData('refund_reason', $model->getData('refund_reason'))
                    ->setData('fruugo_sku_id'.$key,$data['fruugo_skuID']);
                    /*->setData('refund_amount'.$key,$data['refund']['refundCharges']['refundCharge'][0]['charge']['chargeAmount']['amount'])
                    ->setData('refund_tax'.$key,$data['refund']['refundCharges']['refundCharge'][1]['charge']['tax']['taxAmount']['amount'])
                    ->setData('refund_shipping_cost'.$key,0.00)
                    ->setData('refund_shipping_tax'.$key,0.00)
                    ->setData('return_refundfeedback'.$key,$data['refund']['refundComments'])
                    ->setData('return_refundreason'.$key,$data["refund"]['refundCharges']['refundCharge'][0]['refundReason'])*/
                    //->setData('refund_id'.$key,$model->getRefundId());

                $form->setValues($model->getData());
                $i++;
            }
        }else
        {
            $fieldset->addField('get_info',
                'button',
                [
                    'name' => 'fetch-order-info',
                    'onclick' => "loadchildren()",
                    'value' => __('Fetch Order Info'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Create New Refund');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Create New Refund');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
