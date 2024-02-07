<?php
namespace Rokanthemes\SlideBanner\Setup;


use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface{


    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '1.0.1', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'rokanthemes_slide' ),
				'mobile_image',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'  => 'mobile image',
					'after' => 'slide_image'
				]
			);
		}
		$installer->endSetup();
	}
}