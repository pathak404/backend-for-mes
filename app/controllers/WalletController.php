<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\wallet\Order;
use app\models\wallet\Transaction;
use app\models\wallet\Wallet;

/**
 * Class WalletController
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\controllers
 */
class WalletController extends Controller
{

    // order
    public function create_order(Request $request): string
    {
        self::verifyAuthorization();
        $order = new Order($request->getBody());
        if ($data = $order->create()) {
            return $this->onSuccess($data);
        }
        return $this->onError($order->errors);
    }

    public function get_order(Request $request): string
    {
        self::verifyAuthorization();
        $order = new Order($request->getBody());
        if ($data = $order->getOrder()) {
            return $this->onSuccess($data);
        } elseif (empty($order->errors)) {
            return $this->onSuccess([]);
        }
        return $this->onError($order->errors);
    }

    public function get_all_order(Request $request): string
    {
        self::verifyAuthorization();
        $order = new Order($request->getBody());
        if ($data = $order->getAll()) {
            return $this->onSuccess($data);
        } elseif (empty($order->errors)) {
            return $this->onSuccess([]);
        }
        return $this->onError($order->errors);
    }


    // add subscription
    public function add_transaction(Request $request): string
    {
        self::verifyAuthorization(true);
        $txn = new Transaction($request->getBody());
        if ($data = $txn->addTxn()) {
            return $this->onSuccess($data);
        }
        return $this->onError($txn->errors);
    }


    public function get_transactions(Request $request): string
    {
        self::verifyAuthorization(true);
        $txn = new Transaction($request->getBody());
        if ($data = $txn->getTxn()) {
            return $this->onSuccess($data);
        }
        return $this->onError($txn->errors);
    }

    public function get_all_transactions(Request $request): string
    {
        self::verifyAuthorization();
        $txn = new Transaction($request->getBody());
        if ($data = $txn->getAll()) {
            return $this->onSuccess($data);
        } elseif (empty($txn->errors)) {
            return $this->onSuccess([]);
        }
        return $this->onError($txn->errors);
    }


    public function get_wallet(Request $request): string
    {
        self::verifyAuthorization();
        $wallet = new Wallet($request->getBody());
        if ($data = $wallet->getWallet()) {
            return $this->onSuccess($data);
        }
        return $this->onError($wallet->errors);
    }

    public function get_all_wallet(Request $request): string
    {
        self::verifyAuthorization();
        $wallet = new Wallet($request->getBody());
        if ($data = $wallet->getAll()) {
            return $this->onSuccess($data);
        } elseif (empty($wallet->errors)) {
            return $this->onSuccess([]);
        }
        return $this->onError($wallet->errors);
    }

}

