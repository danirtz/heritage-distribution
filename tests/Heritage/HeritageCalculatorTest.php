<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Heritage\HeritageCalculator;
use App\Heritage\Interface\FamilyInterface;
use App\Heritage\Interface\MemberInterface;
use App\Heritage\Interface\HeritageInterface;
use App\Heritage\Exception\EmptyNameException;
use App\Heritage\Exception\MemberNotFoundException;
use App\Heritage\Exception\MemberCannotBeDeadException;
use App\Heritage\Exception\PropertyPriceMustBePositiveException;
use App\Heritage\Exception\LandExtensionM2PriceMustBePositiveException;


class HeritageCalculatorTest extends TestCase
{
    private ?HeritageCalculator $api;


    public function setUp() : void
    {
        $this->api = new HeritageCalculator(
            propertyPrice: 1000000,
            landExtensionM2Price: 300,
        );
    }


    public function tearDown() : void
    {
        $this->api = null;
    }


    public function testNoNegativePropertyPrice()
    {
        $this->expectException(PropertyPriceMustBePositiveException::class);

        new HeritageCalculator(
            propertyPrice: -1000000,
            landExtensionM2Price: 300,
        );
    }


    public function testNoNegativeLandExtensionM2Price()
    {
        $this->expectException(LandExtensionM2PriceMustBePositiveException::class);

        new HeritageCalculator(
            propertyPrice: 1000000,
            landExtensionM2Price: -300,
        );
    }


    public function testNoEmptyName()
    {
        $this->expectException(EmptyNameException::class);

        $name = '';
        $currentDate = $this->dateProvider();
        $family = $this->createMock(FamilyInterface::class);

        $this->api->getHeritageByName($name, $family, $currentDate);
    }


    public function testNoEmptyFamily()
    {
        $this->expectException(MemberNotFoundException::class);

        $name = 'A';
        $currentDate = $this->dateProvider();
        $family = $this->familyProvider();

        $this->api->getHeritageByName($name, $family, $currentDate);
    }


    public function testDeadMemberFound()
    {
        $this->expectException(MemberCannotBeDeadException::class);

        $name = 'A';
        $currentDate = $this->dateProvider(200);

        $family = $this->familyProvider(
            $this->memberProvider(
                'A',
                $this->dateProvider(-50),
                $this->heritageProvieder(1000, 2, 30),
                []
            )
        );

        $this->api->getHeritageByName($name, $family, $currentDate);
    }


    public function testRootFamilyMemberFound()
    {
        $name = 'A';
        $currentDate = $this->dateProvider();

        $family = $this->familyProvider(
            $this->memberProvider(
                'A',
                $this->dateProvider(-50),
                $this->heritageProvieder(1000, 2, 30),
                [],
            )
        );

        $total = $this->api->getHeritageByName($name, $family, $currentDate);
        $this->assertSame(2010000, $total);
    }


    public function testMoneyDistribution()
    {
        $family = $this->familyOnlyRootWithMoneyProvider();

        // Only root member is dead.
        $currentDate = $this->dateProvider(0);

        $total = $this->api->getHeritageByName('B', $family, $currentDate);
        $this->assertSame(250, $total);

        $total = $this->api->getHeritageByName('C', $family, $currentDate);
        $this->assertSame(500, $total);

        $total = $this->api->getHeritageByName('D', $family, $currentDate);
        $this->assertSame(83, $total);

        $total = $this->api->getHeritageByName('E', $family, $currentDate);
        $this->assertSame(83, $total);

        $total = $this->api->getHeritageByName('F', $family, $currentDate);
        $this->assertSame(83, $total);

        // B and C are also dead.
        $currentDate = $this->dateProvider(25);

        $total = $this->api->getHeritageByName('D', $family, $currentDate);
        $this->assertSame(167, $total);

        $total = $this->api->getHeritageByName('E', $family, $currentDate);
        $this->assertSame(167, $total);

        $total = $this->api->getHeritageByName('F', $family, $currentDate);
        $this->assertSame(167, $total);
    }


    public function testPropertiesDistribution()
    {
        $family = $this->familyOnlyRootWithPropertiesProvider();

        // Only root member is dead.
        $currentDate = $this->dateProvider(0);

        $total = $this->api->getHeritageByName('B', $family, $currentDate);
        $this->assertSame(2000000, $total);

        $total = $this->api->getHeritageByName('C', $family, $currentDate);
        $this->assertSame(2000000, $total);

        $total = $this->api->getHeritageByName('D', $family, $currentDate);
        $this->assertSame(3000000, $total);

        $total = $this->api->getHeritageByName('E', $family, $currentDate);
        $this->assertSame(3000000, $total);

        $total = $this->api->getHeritageByName('F', $family, $currentDate);
        $this->assertSame(0, $total);

        // B is also dead.
        $currentDate = $this->dateProvider(25);

        $total = $this->api->getHeritageByName('F', $family, $currentDate);
        $this->assertSame(2000000, $total);
    }


    public function testLandExtensionDistribution()
    {
        $family = $this->familyOnlyRootWithLandExtensionProvider();

        // Only root member is dead.
        $currentDate = $this->dateProvider(0);

        $total = $this->api->getHeritageByName('B', $family, $currentDate);
        $this->assertSame(30000, $total);

        $total = $this->api->getHeritageByName('C', $family, $currentDate);
        $this->assertSame(0, $total);

        $total = $this->api->getHeritageByName('D', $family, $currentDate);
        $this->assertSame(0, $total);

        $total = $this->api->getHeritageByName('E', $family, $currentDate);
        $this->assertSame(0, $total);

        // B is also dead.
        $currentDate = $this->dateProvider(25);

        $total = $this->api->getHeritageByName('E', $family, $currentDate);
        $this->assertSame(30000, $total);
    }


    private function dateProvider(int $years = 0) : \DateTime
    {
        $date = new \DateTime('2022-05-01');
        $interval = \DateInterval::createFromDateString("$years year");
        $date->add($interval);
        return $date;
    }


    private function familyProvider(MemberInterface $rootMember = null)
    {
        $family = $this->createStub(FamilyInterface::class);

        $family
            ->method('getRootMember')
            ->willReturn($rootMember)
        ;

        return $family;
    }


    private function memberProvider(
        string $name,
        \DateTime $birthdayDate,
        HeritageInterface $heritage,
        array $childrenMembers = [],
    ) {
        $member = $this->createStub(MemberInterface::class);

        $member
            ->method('getName')
            ->willReturn($name)
        ;

        $member
            ->method('getBirthdayDate')
            ->willReturn($birthdayDate)
        ;

        $member
            ->method('getHeritage')
            ->willReturn($heritage)
        ;

        $member
            ->method('getChildren')
            ->willReturn($childrenMembers)
        ;

        return $member;
    }


    private function heritageProvieder(
        int $moneyAmount,
        int $numProperties,
        int $landExtension,
    ) {
        $heritage = $this->createStub(HeritageInterface::class);

        $heritage
            ->method('getMoneyAmount')
            ->willReturn($moneyAmount)
        ;

        $heritage
            ->method('getNumProperties')
            ->willReturn($numProperties)
        ;

        $heritage
            ->method('getLandExtension')
            ->willReturn($landExtension)
        ;

        return $heritage;
    }


    private function familyOnlyRootWithMoneyProvider()
    {
        return $this->familyProvider(
            $this->memberProvider(
                'A',
                $this->dateProvider(-150),
                $this->heritageProvieder(1000, 0, 0),
                [
                    $this->memberProvider(
                        'B',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                        [
                            $this->memberProvider(
                                'D',
                                $this->dateProvider(-50),
                                $this->heritageProvieder(0, 0, 0),
                            ),
                            $this->memberProvider(
                                'E',
                                $this->dateProvider(-25),
                                $this->heritageProvieder(0, 0, 0),
                            ),
                            $this->memberProvider(
                                'F',
                                $this->dateProvider(-25),
                                $this->heritageProvieder(0, 0, 0),
                            ),
                        ],
                    ),
                    $this->memberProvider(
                        'C',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                    ),
                ],
            )
        );
    }


    private function familyOnlyRootWithPropertiesProvider()
    {
        return $this->familyProvider(
            $this->memberProvider(
                'A',
                $this->dateProvider(-150),
                $this->heritageProvieder(0, 10, 0),
                [
                    $this->memberProvider(
                        'C',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                    ),
                    $this->memberProvider(
                        'B',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                        [
                            $this->memberProvider(
                                'F',
                                $this->dateProvider(-50),
                                $this->heritageProvieder(0, 0, 0),
                            )
                        ],
                    ),
                    $this->memberProvider(
                        'D',
                        $this->dateProvider(-60),
                        $this->heritageProvieder(0, 0, 0),
                    ),
                    $this->memberProvider(
                        'E',
                        $this->dateProvider(-65),
                        $this->heritageProvieder(0, 0, 0),
                    ),
                ],
            )
        );
    }


    private function familyOnlyRootWithLandExtensionProvider()
    {
        return $this->familyProvider(
            $this->memberProvider(
                'A',
                $this->dateProvider(-150),
                $this->heritageProvieder(0, 0, 100),
                [
                    $this->memberProvider(
                        'C',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                    ),
                    $this->memberProvider(
                        'B',
                        $this->dateProvider(-75),
                        $this->heritageProvieder(0, 0, 0),
                        [
                            $this->memberProvider(
                                'E',
                                $this->dateProvider(-50),
                                $this->heritageProvieder(0, 0, 0),
                            )
                        ],
                    ),
                    $this->memberProvider(
                        'D',
                        $this->dateProvider(-60),
                        $this->heritageProvieder(0, 0, 0),
                    )
                ],
            )
        );
    }

}
