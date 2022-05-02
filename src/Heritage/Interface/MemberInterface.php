<?php

namespace App\Heritage\Interface;

interface MemberInterface
{
    public function getName() : string;
    public function getChildren() : array;
    public function getBirthdayDate() : \DateTime;
    public function getHeritage() : HeritageInterface;
}