<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Tax;

use PHPUnit\Framework\TestCase;
use Shopware\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Cart\Tax\TaxDetector;
use Shopware\Context\Struct\ShopContext;
use Shopware\Country\Struct\Country;
use Shopware\CustomerGroup\Struct\CustomerGroup;

class TaxDetectorTest extends TestCase
{
    public function testUseGrossPrices(): void
    {
        $context = $this->createMock(ShopContext::class);
        $customerGroup = $this->createMock(CustomerGroup::class);
        $customerGroup->expects($this->once())->method('displayGrossPrices')->will($this->returnValue(true));
        $context->expects($this->once())->method('getCurrentCustomerGroup')->will($this->returnValue($customerGroup));

        $detector = new TaxDetector();
        $this->assertTrue($detector->useGross($context));
    }

    public function testDoNotUseGrossPrices(): void
    {
        $context = $this->createMock(ShopContext::class);
        $customerGroup = $this->createMock(CustomerGroup::class);
        $customerGroup->expects($this->once())->method('displayGrossPrices')->will($this->returnValue(false));
        $context->expects($this->once())->method('getCurrentCustomerGroup')->will($this->returnValue($customerGroup));

        $detector = new TaxDetector();
        $this->assertFalse($detector->useGross($context));
    }

    public function testIsNetDelivery(): void
    {
        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setTaxFree(true);

        $context->expects($this->once())->method('getShippingLocation')->will($this->returnValue(
            ShippingLocation::createFromCountry($country)
        ));

        $detector = new TaxDetector();
        $this->assertTrue($detector->isNetDelivery($context));
    }

    public function testIsNotNetDelivery(): void
    {
        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setTaxFree(false);

        $context->expects($this->once())->method('getShippingLocation')->will($this->returnValue(
            ShippingLocation::createFromCountry($country)
        ));

        $detector = new TaxDetector();
        $this->assertFalse($detector->isNetDelivery($context));
    }
}
