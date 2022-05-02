<?php

namespace App\Heritage\Interface;

interface HeritageInterface
{
    public function getMoneyAmount() : int;
    public function getNumProperties() : int;
    public function getLandExtension() : int;
}