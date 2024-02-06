<?php

namespace Smadi0x86wsl\Backend\Model;

class User {
    private $id;
    private $username;
    private $password;
    private $email;
    private $isEmailVerified;
    private $isPremium;
    private $subscriptionType;
    private $subscriptionEndDate;

    public function __construct($id, $username, $password, $email, $isEmailVerified = false, $isPremium = false, $subscriptionType = null, $subscriptionEndDate = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
        $this->isEmailVerified = $isEmailVerified;
        $this->isPremium = $isPremium;
        $this->subscriptionType = $subscriptionType;
        $this->subscriptionEndDate = $subscriptionEndDate;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function isEmailVerified() {
        return $this->isEmailVerified;
    }

    public function setEmailVerified($isEmailVerified) {
        $this->isEmailVerified = $isEmailVerified;
    }

    public function isPremium() {
        return $this->isPremium;
    }

    public function setPremium($isPremium) {
        $this->isPremium = $isPremium;
    }

    public function getSubscriptionType() {
        return $this->subscriptionType;
    }

    public function setSubscriptionType($subscriptionType) {
        $this->subscriptionType = $subscriptionType;
    }

    public function getSubscriptionEndDate() {
        return $this->subscriptionEndDate;
    }

    public function setSubscriptionEndDate($subscriptionEndDate) {
        $this->subscriptionEndDate = $subscriptionEndDate;
    }
}
