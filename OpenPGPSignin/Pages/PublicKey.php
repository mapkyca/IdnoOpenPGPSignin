<?php

namespace IdnoPlugins\OpenPGPSignin\Pages {

    /**
     * Display a public key
     */
    class PublicKey extends \Idno\Common\Page {

        function getContent() {
            
	    if (!empty($this->arguments[0])) {
		$user = \Idno\Entities\User::getByHandle($this->arguments[0]);
	    }
	    if (empty($user)) {
		$this->noContent();
	    }
	    
	    if ($key = $user->pgp_public_key) {
		header('Content-Type: text/plain');
		
		echo $key;
	    } else {
		$this->noContent();
	    }
	    
        }

    }

}
