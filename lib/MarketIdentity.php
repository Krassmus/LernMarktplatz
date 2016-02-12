<?php

require_once __DIR__.'/../vendor/Math/BigInteger.php';
require_once __DIR__.'/../vendor/Crypt/Random.php';
require_once __DIR__.'/../vendor/Crypt/Hash.php';
require_once __DIR__.'/../vendor/Crypt/RSA.php';

class MarketIdentity extends SimpleORMap {

    public function createSignature($text) {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($this['private_key']);
        return $rsa->sign($text);
    }

    public function verifySignature($text, $signature) {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($this['public_key']);
        return $rsa->verify($text, $signature);
    }

    public function store() {
        if (!$this['public_key']) {
            $this->createKeys();
        }
        return parent::store();
    }

    protected function createKeys() {
        $rsa = new Crypt_RSA();
        $keypair = $rsa->createKey();
        $this['private_key'] = preg_replace("/\r/", "", $keypair['privatekey']);
        $this['public_key'] = preg_replace("/\r/", "", $keypair['publickey']);
    }

}