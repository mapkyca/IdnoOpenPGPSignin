<?php

    namespace IdnoPlugins\OpenPGPSignin\Pages {

        /**
         * Provides a way to login via a public key
         */
        class Login extends \Idno\Common\Page
        {
            
            function getContent()
            {
		$this->gatekeeper();
		$t = \Idno\Core\site()->template();
		
		$this->setAsset('OpenPGPSigninForward.js', \Idno\Core\site()->config()->url . 'IdnoPlugins/OpenPGPSignin/Assets/OpenPGPSigninForward.js', 'javascript');
		
		$returnURL = $this->getInput('u');
		
		$session = \Idno\Core\site()->session();
		$user = $session->currentUser();
		
		if (!$returnURL) throw new \Exception ('You need to send a return URL!');
		if (!$user) throw new \Exception ('No user, this shouldn\'t happen');
		
		// Ok, we have a user and a return URL, so lets encrypt and sign the url, and forward back passing the message back as the variable "key" with "u"
		$gpg = new \gnupg();
		
		if (!$gpg->addsignkey($user->pgp_privatekey_fingerprint, '')) throw new \Exception('There was a problem adding the signing key, have you set your keypair?','');
		
		$signature = $gpg->sign($returnURL);
		if (!$signature) throw new \Exception('There was a problem signing: ' . $gpg -> geterror());
		
		
		// Render it and trigger a submit back
		$body = $t->__(['signature' => $signature, 'user' => $user->getUrl(), 'return_url' => $returnURL])->draw('openpgpsignin/account/login');
                $t->__(['title' => 'PGP Keys', 'body' => $body])->drawPage();
		
            }

            function postContent() {
                
		
		
		// TODO: Log in via public key
		
		
		
		
	    }

        }

    }
