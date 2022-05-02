<?php

namespace App\Heritage\Data;

use App\Heritage\Exception\MoneyAmountMustBePositiveException;
use App\Heritage\Exception\NumPropertiesMustBePositiveException;
use App\Heritage\Exception\LandExtensionMustBePositiveException;
use App\Heritage\Exception\PropertyPriceMustBePositiveException;
use App\Heritage\Exception\LandExtensionM2PriceMustBePositiveException;


/**
 * Contains the heritage data.
 */
class Heritage
{
    /** @var int $moneyAmount Money amount. */
    private int $moneyAmount;

    /** @var int $numProperties Number of properties. */
    private int $numProperties;

    /** @var int $landExtension Land extension (m²). */
    private int $landExtension;


    /**
     * Constructor.
     * 
     * @param int $moneyAmount   Money amount.
     * @param int $numProperties Number of properties.
     * @param int $landExtension Land extension.
     * 
     * @return void
     */
    public function __construct(int $moneyAmount, int $numProperties, int $landExtension)
    {
        $this->checkMoneyAmount($moneyAmount);
        $this->checkNumProperties($numProperties);
        $this->checkLandExtension($landExtension);

        $this->moneyAmount = $moneyAmount;
        $this->numProperties = $numProperties;
        $this->landExtension = $landExtension;
    }


    /**
     * Checks that the money amount is not negative.
     * 
     * @param int $moneyAmount Money amount.
     * 
     * @return void
     */
    private function checkMoneyAmount(int $moneyAmount)
    {
        if (0 > $moneyAmount) {
            throw new MoneyAmountMustBePositiveException();
        }
    }


    /**
     * Checks that the number of properties is not negative.
     * 
     * @param int $numProperties Number of properties.
     * 
     * @return void
     */    
    private function checkNumProperties(int $numProperties)
    {
        if (0 > $numProperties) {
            throw new NumPropertiesMustBePositiveException();
        }
    }


    /**
     * Checks that the land extension is not negative.
     * 
     * @param int $landExtension Land extension.
     * 
     * @return void
     */ 
    private function checkLandExtension(int $landExtension)
    {
        if (0 > $landExtension) {
            throw new LandExtensionMustBePositiveException();
        }
    }


    /**
     * Gets the money amount.
     * 
     * @return int Money amount.
     */
    public function getMoneyAmount() : int
    {
        return $this->moneyAmount;
    }


    /**
     * Gets the number of properties.
     * 
     * @return int Number of properties.
     */
    public function getNumProperties() : int
    {
        return $this->numProperties;
    }


    /**
     * Gets the land extension.
     * 
     * @return int Land extension.
     */
    public function getLandExtension() : int
    {
        return $this->landExtension;
    }


    /**
     * Calculates the total money amount of the heritage.
     * 
     * @param int $propertyPrice        Price per property.
     * @param int $landExtensionM2Price Price per m² extension land.
     * 
     * @return int Total amount of the heritage.
     */
    public function calculateTotal(int $propertyPrice, int $landExtensionM2Price) : int
    {
        $this->checkPropertyPrice($propertyPrice);
        $this->checkLandExtensionM2Price($landExtensionM2Price);

        return
            $this->moneyAmount
            +
            $this->numProperties * $propertyPrice
            +
            $this->landExtension * $landExtensionM2Price
        ;
    }


    /**
     * Checks that the property price is not negative.
     * 
     * @param int $propertyPrice Price per property.
     * 
     * @return void
     */
    private function checkPropertyPrice(int $propertyPrice)
    {
        if (0 > $propertyPrice) {
            throw new PropertyPriceMustBePositiveException();
        }
    }


    /**
     * Checks that the m² extension price is not negative.
     * 
     * @param int $landExtensionM2Price Price per property.
     * 
     * @return void
     */
    private function checkLandExtensionM2Price(int $landExtensionM2Price)
    {
        if (0 > $landExtensionM2Price) {
            throw new LandExtensionM2PriceMustBePositiveException();
        }
    }
}