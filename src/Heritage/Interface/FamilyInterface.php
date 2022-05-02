<?php

namespace App\Heritage\Interface;

interface FamilyInterface
{
    public function getRootMember(): ?MemberInterface;
}