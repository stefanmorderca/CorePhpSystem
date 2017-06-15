<?php

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
