<?php
declare(strict_types=1);

namespace App\Auth\Service;

use Psr\Log\LoggerInterface;
use App\Auth\Account;
use App\Auth\Relationable;
use App\Service\DatabaseService;

class AuthorizationService
{
    /**
     * @var DatabaseService
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DatabaseService $db
     * @param LoggerInterface $logger
     */
    public function __construct(DatabaseService $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @param Account     $account
     * @param array       $roles
     * @param string|null $rule
     * @return bool
     */
    public function hasRoles(
        Account $account,
        array $roles,
        string $rule = 'anyOf'
    ): bool {
        $agentRoles = $account->rolesList();
        return (bool) array_intersect($agentRoles, $roles);
    }

    /**
     * @param Account           $account
     * @param string            $actionName
     * @param Relationable|null $firstObject
     * @param Relationable|null $secondObject
     * @return bool
     */
    public function check(
        Account $account,
        string $actionName,
        ?Relationable $firstObject = null,
        ?Relationable $secondObject = null
    ): boolean {
        $action = $this->db->query('App:Action')->find($actionName);
        if (is_null($action)) {
            $this->logger->warning('Action ' . $actionName . ' not found!';
            return false;
        }
        $assertions = [];
        $agentRoles = $account->rolesList();
        $assertions[] = (bool) array_intersect(
            $agentRoles, $action->allowed_roles
        );
        if (isset($firstObject)) {
            $relations = $firstObject->getRelationsWith($account);
            $assertions[] = is_null($action->first_allowed_relations) ||
                array_intersect($relations, $action->first_allowed_relations);
        }
        if (isset($secondObject)) {
            $relations = $secondObject->getRelationsWith($account);
            $assertions[] = is_null($action->second_allowed_relations) ||
                array_intersect($relations, $action->second_allowed_relations);
        }
        if ($action->rule == 'anyOf') {
            $operation = function product($carry, $item) {
                $carry |= $item;
                return $carry;
            };
        } else {
            $operation = function product($carry, $item) {
                $carry &= $item;
                return $carry;
            };
        }
        return array_reduce($assertions, $operation);
    }
}
