<?php

namespace App\Contracts;

interface HasIconContract
{
    public function setIconId(?int $id): void;

    public function getIconId(): ?int;

    public function getIcon(): string;
}
