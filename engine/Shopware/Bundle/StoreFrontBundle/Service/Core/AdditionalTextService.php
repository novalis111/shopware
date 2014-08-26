<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AdditionalTextService implements AdditionalTextServiceInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @param ConfiguratorServiceInterface $configuratorService
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        ConfiguratorServiceInterface $configuratorService,
        \Shopware_Components_Config $config
    ) {
        $this->configuratorService = $configuratorService;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function buildAdditionalText(ListProduct $product, ShopContextInterface $context)
    {
        $products = $this->buildAdditionalTextLists(array($product), $context);

        return array_shift($products);
    }

    /**
     * @inheritdoc
     */
    public function buildAdditionalTextLists($products, ShopContextInterface $context)
    {
        $required = array();
        foreach ($products as &$product) {
            if (!$product->getAdditional()) {
                $required[] = $product;
            }
        }

        if (empty($required)) {
            return $products;
        }

        $configurations = $this->configuratorService->getProductsConfigurations(
            $required,
            $context
        );

        /**@var $required ListProduct[]*/
        foreach ($required as &$product) {
            if (!array_key_exists($product->getNumber(), $configurations)) {
                continue;
            }

            $config = $configurations[$product->getNumber()];

            $product->setAdditional($this->buildTextDynamic($config));
        }

        return $products;
    }

    /**
     * @param Group[] $configurations
     * @return string
     */
    private function buildTextDynamic($configurations)
    {
        $text = array();
        foreach ($configurations as $group) {
            foreach ($group->getOptions() as $option) {
                $text[] = $option->getName();
            }
        }
        return implode(' ', $text);
    }

}
