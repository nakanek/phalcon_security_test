<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {
        $this->logger->debug('call indexAction');
    }

    public function tokencheckAction()
    {
        $this->view->sessionToken = $this->security->getSessionToken();
        $this->view->token = $this->request->getQuery('token', null, null); 
    }

}

