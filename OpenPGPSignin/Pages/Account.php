<?php

    namespace IdnoPlugins\OpenPGPSignin\Pages {

        /**
         * Default class to serve account settings
         */
        class Account extends \Idno\Common\Page
        {
            
            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                $t = \Idno\Core\site()->template();
                
                $public_key = $this->getInput('public_key');
                $private_key = $this->getInput('private_key');
		
		$this->setAsset('OpenPGPSigninAccount.js', \Idno\Core\site()->config()->url . 'IdnoPlugins/OpenPGPSignin/Assets/OpenPGPSigninAccount.js', 'javascript');
                
                $body = $t->__(['public_key' => $public_key, 'private_key' => $private_key])->draw('openpgpsignin/account/account');
                $t->__(['title' => 'PGP Keys', 'body' => $body])->drawPage();
            }

            function postContent() {
                $this->gatekeeper(); // Logged-in users only
                
                $public_key = $this->getInput('public_key');
                $private_key = $this->getInput('private_key');
                
		$session = \Idno\Core\site()->session();
		$user = $session->currentUser();
		
                if (empty($public_key) && empty($private_key))
                {	
			// No key, generate
		    
		    
		    
			    // TODO
		    
		    

		}
		
		if ($public_key && $private_key) {
		    
		    // Save key on keyring
		    
		    
			// TODO
		    
		    
		    
		    // Save public key against user
		    $user->pgp_public_key = $public_key;
		    $user->pgp_private_key = $private_key;
		}

                $this->forward('/account/openpgpsignin/');
            }

        }

    }
