<?php
declare(strict_types=1);

namespace App\Auth;

interface Actor
{
    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * @return string[]
     */
    public function getRolesList(): array;

    /**
     * @return array
     */
    public function toArray(): array;
}
