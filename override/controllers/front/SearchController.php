<?php

class SearchController extends SearchControllerCore
{
	
	
    public function initContent()
    {
    	
        parent::initContent();
        //Hook::exec('ActionSearch',array('search' => $this->context->smarty));
    }
}