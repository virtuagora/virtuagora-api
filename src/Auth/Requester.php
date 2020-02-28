<?php
declare(strict_types=1);

namespace App\Auth;

class Requester implements Account, Relationable
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string|null
     */
    public $display_name;

    /**
     * @var string[]
     */
    protected $roles;

    /**
     * @var array
     */
    protected $extra;

    /**
     * @param string      $type
     * @param int|null    $id
     * @param string|null $name
     * @param string[]    $roles
     * @param array       $extra
     */
    public function __construct(
        string $type,
        ?int $id = null,
        ?string $name = null,
        array $roles = [],
        array $extra = []
    ) {
        $this->type = $type;
        $this->id = $id;
        $this->display_name = $name;
        $this->roles = $roles;
        $this->extra = $extra;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getRolesList(): array
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_merge($this->extra, [
            'id' => $this->id,
            'type' => $this->type,
            'display_name' => $this->display_name,
            'roles_list' => $this->roles,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationsWith(AgentInterface $agent): array
    {
        return (isset($this->id) && $this->id == $agent->id) ? ['self'] : [];
    }
}
