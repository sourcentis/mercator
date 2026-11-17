<?php

namespace App\Traits;

/**
 * @property int|null $icon_id
 */
trait HasIcon
{
    public function setIconId(?int $id): void
    {
        $this->icon_id = $id;
    }

    public function getIconId(): ?int
    {
        return $this->icon_id;
    }

    public function getIcon(): string
    {
        return $this->icon_id === null
            ? static::$icon
            : '/admin/documents/'.$this->icon_id;
    }
}
