<?php
declare(strict_types=1);

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Util\Paginator;

use Illuminate\Database\Connection;
use App\Model\Account;

use Psr\Log\LoggerInterface;
use App\Auth\Requester;

class AccountRepository
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Connection $db
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function retrieveOneLocal(
        Requester $requester,
        string $username,
        string $password
    ): EntityData {
        $response = new EntityData('success');
        $account = Account::with('agent')
            ->where('account_type_id', 'local')
            ->where('username', $username)
            ->first();
        if (isset($account)) {
            $hash = $account->secret;
            if (password_verify($password, $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $account->secret = password_hash($password, PASSWORD_DEFAULT);
                    $account->save();
                }
                // TODO check if user is banned
                $response->setModel($account);
            } else {
                $response->setState('wrongPassword');
            }
        } else {
            $response->setState('notFound');
        }
        return $response;
    }
}