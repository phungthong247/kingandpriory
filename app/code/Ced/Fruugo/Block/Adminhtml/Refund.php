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
namespace Ced\Fruugo\Block\Adminhtml;

class Refund extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    public $_template = 'refund/view.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->_getAddButtonOptions();
    }

    /**
     * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
     *
     * @return \Magento\Backend\Block\Widget\Container
     */
    public function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Refund\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * GgetAddButtonOptions
     * @return void
     */

    public function _getAddButtonOptions()
    {
        $splitButtonOptions = [
            'label' => __('Get Updated Refund Status'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrlStatus() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);
        $splitButtonOptions = [
            'label' => __('+ Add New Refund'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add_new', $splitButtonOptions);

    }

    /**
     * Create Url
     *
     * @return string
     */

    public function _getCreateUrl()
    {
        return $this->getUrl(
            'fruugo/*/new'
        );
    }

    /**
     * GET
     * @return string
     */

    public function _getCreateUrlStatus()
    {
        return $this->getUrl(
            'fruugo/*/update'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Parser Array
     *
     * @return array
     */
    function parserArray($shipmentData = [])
    {
        if (!empty($shipmentData)) {
            $arr = [];
            foreach ($shipmentData as $key => $value) {
                if (in_array($key, $arr)) {
                    continue;
                }
                $count = count($shipmentData);
                $sku = $value['item']['sku'];

                $quantity = 1;

                $lineNumber = $value['lineNumber'];
                for ( $i = $key+1 ; $i < $count;$i++) {
                    if ($shipmentData[$i]['item']['sku'] == $sku ) {
                        $quantity++;
                        $lineNumber = $lineNumber.','.$shipmentData[$i]['lineNumber'];
                        unset($shipmentData[$i]);
                        array_push($arr, $i);
                        array_values($shipmentData);
                    }
                }
                $shipmentData[$key]['lineNumber'] = $lineNumber;
                $shipmentData[$key]['orderLineQuantity']['amount'] =  $quantity;
            }
            return $shipmentData;
        }
        return false;
    }

}
