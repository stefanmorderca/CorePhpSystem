<?php

interface iAuth {

    public function isValid();

    public function logout();

    public function login($username, $password);

    public function addUser($username, $password);

    public function getError();

    public function getUsername();

    public function getUserId();

    public function changePassword($pass);

    public function checkPassword($password);
}
