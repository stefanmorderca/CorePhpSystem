<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UnauthorizedAccessAttemptListner
 *
 * @author mkozak
 */
interface AuthListner {

    public function onUnauthorizedAccess();

    public function onSuccessfulLogin();

    public function onFailedLogin();
    
    public function onLogout();
}
