<?php

namespace App\Heritage;

use App\Heritage\Interface\FamilyInterface;
use App\Heritage\Interface\MemberInterface;
use App\Heritage\Exception\EmptyNameException;
use App\Heritage\Exception\MemberNotFoundException;
use App\Heritage\Exception\MemberCannotBeDeadException;
use App\Heritage\Exception\PropertyPriceMustBePositiveException;
use App\Heritage\Exception\LandExtensionM2PriceMustBePositiveException;
use App\Heritage\Data\Heritage;


/**
 * Calculates the heritage of a family member.
 */
class HeritageCalculator
{
    /** @var int DEATH_AGE Age members die. */
    const DEATH_AGE = 100;

    /** @var int $propertyPrice Property price. */
    private int $propertyPrice;

    /** @var int $landExtensionM2Price Land extension m² price. */
    private int $landExtensionM2Price;


    /**
     * Constructor.
     * 
     * @param int $propertyPrice        Price per property.
     * @param int $landExtensionM2Price Price per m² extension land.
     * 
     * @return void
     */
    public function __construct(int $propertyPrice, int $landExtensionM2Price)
    {
        $this->checkPropertyPrice($propertyPrice);
        $this->checkLandExtensionM2Price($landExtensionM2Price);

        $this->propertyPrice = $propertyPrice;
        $this->landExtensionM2Price = $landExtensionM2Price;
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


    /**
     * Calculates the total member's amount at current date from a family.
     * 
     * @param string          $name        Member name.
     * @param FamilyInterface $family      Family structure.
     * @param \DateTime       $currentDate Current date.
     * 
     * @return int Total member's amount at current date.
     */
    public function getHeritageByName(
        string $name,
        FamilyInterface $family,
        \DateTime $currentDate,
    ) : int {
        $this->checkNameNotEmpty($name);

        $rootMember = $family->getRootMember();
        $this->checkMemberNotNull($rootMember);

        $memberHeritage = $this->getMemberHeritage(
            $name,
            $rootMember,
            $currentDate,
            new Heritage(0, 0, 0),
        );

        $this->checkHeritageNotNull($memberHeritage);

        return $memberHeritage->calculateTotal(
            $this->propertyPrice,
            $this->landExtensionM2Price,
        );
    }


    /**
     * Checks that the member variable is not null.
     * 
     * @param MemberInterface $member Member.
     * 
     * @return void
     */
    private function checkNameNotEmpty(string $name)
    {
        if (empty($name)) {
            throw new EmptyNameException();
        }
    }


    /**
     * Checks that the member variable is not null.
     * 
     * @param MemberInterface $member Member.
     * 
     * @return void
     */
    private function checkMemberNotNull(?MemberInterface $member)
    {
        if (null === $member) {
            throw new MemberNotFoundException();
        }
    }


    /**
     * Checks that the heritage variable is not null.
     * 
     * @param Heritage $heritage Heritage.
     * 
     * @return void
     */
    private function checkHeritageNotNull(?Heritage $heritage)
    {
        if (null === $heritage) {
            throw new MemberNotFoundException();
        }
    }


    /**
     * Calculates the total member's amount at current date.
     * 
     * @param string          $name             Member name.
     * @param MemberInterface $member           Member to examine.
     * @param \DateTime       $currentDate      Current date.
     * @param Heritage        $receivedHeritage Received Heritage.
     * 
     * @return Heritage Heritage of the searched member.
     */
    private function getMemberHeritage(
        string $name,
        MemberInterface $member,
        \DateTime $currentDate,
        Heritage $receivedHeritage,
    ) : ?Heritage {
        if ($name === $member->getName()) {
            return $this->getSelectedMemberHeritage(
                $member,
                $currentDate,
                $receivedHeritage
            );
        } elseif ($this->memberIsDead($member, $currentDate)) {
            return $this->getDeadMemberHeritage(
                $name,
                $member,
                $currentDate,
                $receivedHeritage
            );
        } else {
            return $this->getAliveMemberHeritage(
                $name,
                $member,
                $currentDate,
                $receivedHeritage
            );
        }
    }


    /**
     * Calculates the total member's amount at current date of a member we know it's
     * the one we are looking for.
     * 
     * @param MemberInterface $member           Member to examine.
     * @param \DateTime       $currentDate      Current date.
     * @param Heritage        $receivedHeritage Received Heritage.
     * 
     * @return Heritage Heritage of the searched member.
     */
    private function getSelectedMemberHeritage(
        MemberInterface $member,
        \DateTime $currentDate,
        Heritage $receivedHeritage,
    ) : Heritage {
        $this->checkMemberIsAlive($member, $currentDate);

        $memberChildren = $member->getChildren();
        $memberHeritage = $member->getHeritage();

        if ($memberChildren) {
            $newMoneyAmount =
                $memberHeritage->getMoneyAmount()
                +
                round($receivedHeritage->getMoneyAmount() / 2)
            ;
        } else {
            $newMoneyAmount =
                $memberHeritage->getMoneyAmount()
                +
                $receivedHeritage->getMoneyAmount()
            ;
        }

        $newLandExtension =
            $memberHeritage->getLandExtension()
            +
            $receivedHeritage->getLandExtension()
        ;

        $newNumProperties =
            $memberHeritage->getNumProperties()
            +
            $receivedHeritage->getNumProperties()
        ;

        return new Heritage(
            $newMoneyAmount,
            $newNumProperties,
            $newLandExtension,
        );
    }


    /**
     * Checks that the heritage variable is not dead.
     * 
     * @param MemberInterface $member Member.
     * @param \DateTime $currentDate Current date.
     * 
     * @return void
     */
    private function checkMemberIsAlive(MemberInterface $member, \DateTime $currentDate)
    {
        if ($this->memberIsDead($member, $currentDate)) {
            throw new MemberCannotBeDeadException();
        }
    }


    /**
     * Calculates the total member's amount at current date of a member we know it's
     * alive.
     * 
     * @param string          $name             Member name.
     * @param MemberInterface $member           Member to examine.
     * @param \DateTime       $currentDate      Current date.
     * @param Heritage        $receivedHeritage Received Heritage.
     * 
     * @return Heritage Heritage of the searched member.
     */
    private function getAliveMemberHeritage(
        string $name,
        MemberInterface $member,
        \DateTime $currentDate,
        Heritage $receivedHeritage,
    ) : ?Heritage {
        $memberChildren = $member->getChildren();

        if (empty($memberChildren)) {
            return null;
        }

        $moneyAmountToGive = round(
            round($receivedHeritage->getMoneyAmount() / 2)
            /
            count($memberChildren)
        );

        $childInheritance = new Heritage($moneyAmountToGive, 0, 0);

        foreach ($memberChildren as $childMember) {
            $heritage = $this->getMemberHeritage(
                $name,
                $childMember,
                $currentDate,
                $childInheritance,
            );

            if (null !== $heritage) {
                return $heritage;
            }
        }

        return null;
    }


    /**
     * Calculates the total member's amount at current date of a member we know it's
     * dead.
     * 
     * @param string          $name             Member name.
     * @param MemberInterface $member           Member to examine.
     * @param \DateTime       $currentDate      Current date.
     * @param Heritage        $receivedHeritage Received Heritage.
     * 
     * @return Heritage Heritage of the searched member.
     */
    private function getDeadMemberHeritage(
        string $name,
        MemberInterface $member,
        \DateTime $currentDate,
        Heritage $receivedHeritage,
    ) : ?Heritage {
        $memberHeritage = $member->getHeritage();
        $memberChildren = $member->getChildren();
        $numChildren = count($memberChildren);

        if (0 === $numChildren) {
            return null;
        }

        $currentNumProperties =
            $memberHeritage->getNumProperties()
            +
            $receivedHeritage->getNumProperties()
        ;

        $moneyAmountToGive = round(
            (
                $memberHeritage->getMoneyAmount()
                +
                $receivedHeritage->getMoneyAmount()
            )
            /
            $numChildren
        );

        usort($memberChildren, [$this, 'compareMembersByBirthdayAndName']);

        foreach ($memberChildren as $childNum => $childMember) {
            $numPropertiesToGive =
                floor(
                    ($currentNumProperties + $numChildren - 1 - $childNum) / (2 * $numChildren)
                )
                +
                floor(
                    ($currentNumProperties + $numChildren + $childNum) / (2 * $numChildren)
                )
            ;

            echo "c: $childNum"; exit;

            if ($childNum === 0) {
                $landExtensionToGive = 
                    $memberHeritage->getLandExtension()
                    +
                    $receivedHeritage->getLandExtension()
                ;
            } else {
                $landExtensionToGive = 0;
            }

            $childInheritance = new Heritage(
                $moneyAmountToGive,
                $numPropertiesToGive,
                $landExtensionToGive,
            );

            $heritage = $this->getMemberHeritage(
                $name,
                $childMember,
                $currentDate,
                $childInheritance,
            );

            if (null !== $heritage) {
                return $heritage;
            }
        }

        return null;
    }


    /**
     * Compares two members by its birthday date and by its name. Function to be used in usort.
     * 
     * @param MemberInterface $memberA Member A to examine.
     * @param MemberInterface $memberB Member B to examine.
     * 
     * @return int Positive is A>B, negative if B<A, 0 default.
     */
    private function compareMembersByBirthdayAndName(
        MemberInterface $memberA,
        MemberInterface $memberB,
    ) : int {
        if ($memberA->getBirthdayDate() > $memberB->getBirthdayDate()) {
            return 1;
        } elseif ($memberA->getBirthdayDate() < $memberB->getBirthdayDate()) {
            return -1;
        } else {
            return strcmp($memberA->getName(), $memberB->getName());
        }
    }

    /**
     * Checks if a member is dead.
     * 
     * @param MemberInterface $member      Member to check.
     * @param \DateTime       $currentDate Current date.
     * 
     * @return bool True if its dead.
     */
    private function memberIsDead(
        MemberInterface $member,
        \Datetime $currentDate,
    ) : bool {
        $birthdayDate = $member->getBirthdayDate();
        $interval = $currentDate->diff($birthdayDate);
        $age = $interval->format('%y');

        return $age >= self::DEATH_AGE;
    }
}
