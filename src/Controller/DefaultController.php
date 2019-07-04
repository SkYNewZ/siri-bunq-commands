<?php


namespace App\Controller;


use App\Service\BunqApiContext;
use bunq\Context\ApiContext;
use bunq\Exception\BunqException;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Object\Pointer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * Error constants.
     */
    const ERROR_COULD_NOT_DETERMINE_ALIAS_OF_TYPE_IBAN = 'Could not find alias with type IBAN for monetary account "%s';

    /**
     * @var ApiContext $apiContext
     */
    private $apiContext;

    /**
     * @var MonetaryAccountBank[] $monetaryAccountList
     */
    private $monetaryAccountList = [];

    /**
     * DefaultController constructor.
     */
    public function __construct()
    {
        $this->apiContext = BunqApiContext::getInstance();
    }

    /**
     * @Route("/iban/{account}")
     * @param $account
     * @return JsonResponse
     * @throws BunqException
     */
    public function getIbanFromAccount($account): JsonResponse
    {
        foreach ($this->getAccountList() as $monetaryAccountBank) {
            if (strpos($monetaryAccountBank->getDescription(), $account) !== false) {
                return new JsonResponse($this->getIbanAliasForBankAccount($monetaryAccountBank));
            }
        }
        throw new NotFoundHttpException('Account not found');
    }

    /**
     * @Route("/balance/{account}")
     * @param $account
     * @return JsonResponse
     */
    public function getBalanceFromAccount($account): JsonResponse
    {
        foreach ($this->getAccountList() as $monetaryAccountBank) {
            if (strpos($monetaryAccountBank->getDescription(), $account) !== false) {
                return new JsonResponse($monetaryAccountBank->getBalance());
            }
        }
        throw new NotFoundHttpException('Account not found');
    }

    /**
     * @return array|MonetaryAccountBank[]
     */
    private function getAccountList(): array
    {
        if (count($this->monetaryAccountList) === 0) {
            $this->monetaryAccountList = MonetaryAccountBank::listing()->getValue();
        }

        return $this->monetaryAccountList;
    }

    /**
     * @param MonetaryAccountBank $bankAccount
     *
     * @return Pointer
     * @throws BunqException
     */
    private function getIbanAliasForBankAccount(MonetaryAccountBank $bankAccount): Pointer
    {
        foreach ($bankAccount->getAlias() as $alias) {
            if ($alias->getType() === 'IBAN') {
                return $alias;
            }
        }
        throw new BunqException(
            vsprintf(self::ERROR_COULD_NOT_DETERMINE_ALIAS_OF_TYPE_IBAN, [$bankAccount->getDescription()])
        );
    }

}