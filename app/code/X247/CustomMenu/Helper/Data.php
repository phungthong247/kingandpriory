<?php
/**
* Copyright © 2016 TuanHatay. All rights reserved.
*/
namespace X247\CustomMenu\Helper;

class Data extends \Rokanthemes\CustomMenu\Helper\Data
{

    protected $_objectManager;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $objectManager, $storeManager);
    }
}
