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
		
		
		/*// Try and get owner fingerprint
		$encryptkeys = [$user->pgp_publickey_fingerprint]; // Always encrypt to myself, incase we are signing in to my own site
		
		$remotehost = parse_url($returnURL)['host'];
		foreach ($user->getFollowingArray() as $uuid => $data) {
		    
		    $followinghost = parse_url($data['url'])['host'];
		    if ($remotehost == $followinghost) {
			
			// If this following user is on the same site, then add their key (TODO: think of a cleaner way to do this)
			if ($u = \Idno\Common\Entity::getByUUID($uuid))
			{
			    if ($pk = $u->pgp_publickey_fingerprint)
				    $encryptkeys[] = $pk;
			}
		    }
		    
		}*/
		
		$session = \Idno\Core\site()->session();
		$user = $session->currentUser();
		
		if (!$returnURL) throw new \Exception ('You need to send a return URL!');
		if (!$user) throw new \Exception ('No user, this shouldn\'t happen');
		//if (!$owner_fingerprint) throw new \Exception('No fingerprint provided, don\'t know who I\'m talking to!');
		
		
		// Ok, we have a user and a return URL, so lets encrypt and sign the url, and forward back passing the message back as the variable "key" with "u"
		$gpg = new \gnupg();
		
		// Add fingerprints
		//foreach ($encryptkeys as $fingerprint)
		//    if (!$gpg->addencryptkey($fingerprint)) throw new \Exception('There was a problem adding the encryption key, have you added this person as your friend?');
		
		if (!$gpg->addsignkey($user->pgp_privatekey_fingerprint, '')) throw new \Exception('There was a problem adding the signing key, have you set your keypair?','');
		
		$signature = $gpg->sign($returnURL);
		if (!$signature) throw new \Exception('There was a problem signing: ' . $gpg -> geterror());
		
		/*$encrypted = $gpg->encryptsign(json_encode([
		    'profile' => $user->getUrl(),
		    'return_url' => $returnURL
		])); 		 
		
		if (!$encrypted) throw new \Exception('There was a problem encrypting the return data: ' . $gpg -> geterror());
		*/
		
		
		// Render it and trigger a submit back
		$body = $t->__(['signature' => $signature, 'user' => $user->getUrl(), 'return_url' => $returnURL])->draw('openpgpsignin/account/login');
                $t->__(['title' => 'PGP Keys', 'body' => $body])->drawPage();
		
            }

            function postContent() {
                
		
		
		// TODO: Log in via public key
		
		
		
		
	    }

        }

    }
