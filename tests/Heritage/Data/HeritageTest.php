<?php

namespace App\Tests\Data;

use PHPUnit\Framework\TestCase;
use App\Heritage\Exception\PropertyPriceMustBePositiveException;
use App\Heritage\Exception\LandExtensionM2PriceMustBePositiveException;
use App\Heritage\Exception\MoneyAmountMustBePositiveException;
use App\Heritage\Exception\NumPropertiesMustBePositiveException;
use App\Heritage\Exception\LandExtensionMustBePositiveException;
use App\Heritage\Data\Heritage;

class HeritageTest extends TestCase
{
    public function testNoNegativeMoneyAmount()
    {
        $this->expectException(MoneyAmountMustBePositiveException::class);
        $heritage = new Heritage(-1000, 2, 30);
    }


    public function testNoNegativeNumProperties()
    {
        $this->expectException(NumPropertiesMustBePositiveException::class);
        $heritage = new Heritage(1000, -2, 30);
    }


    public function testNoNegativeLandExtension()
    {
        $this->expectException(LandExtensionMustBePositiveException::class);
        $heritage = new Heritage(1000, 2, -30);
    }


    public function testCreateHeritage()
    {
        $heritage = new Heritage(1000, 2, 30);

        $this->assertInstanceOf(Heritage::class, $heritage);
        $this->assertSame(1000, $heritage->getMoneyAmount());
        $this->assertSame(2, $heritage->getNumProperties());
        $this->assertSame(30, $heritage->getLandExtension());
    }


    public function testNoNegativePropertyPrice()
    {
        $this->expectException(PropertyPriceMustBePositiveException::class);

        $heritage = new Heritage(1000, 2, 30);
        $heritage->calculateTotal(
            propertyPrice: -1000000,
            landExtensionM2Price: 300,
        );
    }


    public function testNoNegativeLandExtensionM2Price()
    {
        $this->expectException(LandExtensionM2PriceMustBePositiveException::class);

        $heritage = new Heritage(1000, 2, 30);
        $heritage->calculateTotal(
            propertyPrice: 1000000,
            landExtensionM2Price: -300,
        );
    }


    public function testTotalCalculation()
    {
        $heritage = new Heritage(1000, 2, 30);
        
        $total = $heritage->calculateTotal(
            propertyPrice: 1000000,
            landExtensionM2Price: 300,
        );

        $this->assertSame(2010000, $total);
    }
}
