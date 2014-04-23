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
		
		if ($public_key && $private_key) {
		    
		    // Save key on keyring
		    $gpg = new \gnupg();
		    
		    $pub = $gpg->import($public_key);
		    $pri = $gpg->import($private_key);
		    
		    error_log("PUBLIC: " . print_r($pub, true));
		    error_log("PRIVATE: " . print_r($pri, true));
			
		    // Save public key against user
		    $user->pgp_public_key = $public_key;
		    $user->pgp_private_key = $private_key;
		    
		    $user->save();
		}

                $this->forward('/account/pgpkeys/');
            }

        }

    }
