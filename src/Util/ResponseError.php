<?php

namespace App\Util;

use JsonSerializable;

class ResponseError implements JsonSerializable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param string        $type
     * @param string|null   $description
     * @param array         $fields
     */
    public function __construct(
        string $type,
        string $description,
        array $parameters = []
    ) {
        $this->type = $type;
        $this->description = $description;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;
        return $this;
    }

    // TODO getters/setters for parameters

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $payload = [
            'type' => $this->type,
            'description' => $this->description,
            'parameters' => $this->parameters,
        ];

        return $payload;
    }
}
