<?php

    namespace IdnoPlugins\OpenPGPSignin {
        class Main extends \Idno\Common\Plugin {
	    
	    private function getFingerprintsFromKeyinfo($key_info, array &$fingerprints) {
		foreach ($key_info as $info) {
		    // Fingerprint found, so add it
		    if (isset($info['fingerprint'])) {
			$fingerprints[] = $info['fingerprint'];
		    }
		    // If there are subkeys
		    if (isset($info['subkeys']))
			$this->getFingerprintsFromKeyinfo ($info['subkeys'], $fingerprints);
		}
	    }
	    
	    /**
	     * Retrieve a user associated with a PGP key fingerprint.
	     * @return \Idno\Entities\User
	     */
	    private function getUserByKeyInfo(array $key_info) {
		$fingerprints = [];
		$this->getFingerprintsFromKeyinfo($key_info, $fingerprints); // Find fingerprints from keys and subkeys
		
		foreach ($fingerprints as $fingerprint) {
		    error_log("Looking for users identified with $fingerprint");
		    
		    if ($result = \Idno\Core\site()->db()->getObjects('Idno\\Entities\\User', array('pgp_publickey_fingerprint' => $fingerprint), null, 1)) {
			foreach ($result as $row) {
			    return $row;
			}
		    }
		    if ($result = \Idno\Core\site()->db()->getObjects('Idno\\Entities\\RemoteUser', array('pgp_publickey_fingerprint' => $fingerprint), null, 1)) {
			foreach ($result as $row) {
			    return $row;
			}
		    }
		}

                return false;
	    }
	    
	    /**
	     * When given a profile page, it will attempt to find the appropriate public key data.
	     * 
	     * First it'll look for a <link href="......" rel="key"> in the header, or a Link: <url>; rel="key" in the header.
	     * Failing that it'll look for a class="key" block on the page.
	     * 
	     * @param type $url
	     * @return string|false Will return the new key's fingerprint or FALSE
	     */
	    private function findPublicKey($url) {
		
		if ($page = file_get_contents($url)) {
		    
		    $endpoint_url = null;
		    
		    
		    // Get headers from request
		    $headers = $http_response_header;
		    
		    
		    // See if we have an endpoint in header
		    foreach ($headers as $header) {
			if ((preg_match('~<(https?://[^>]+)>; rel="key"~', $header, $match)) && (!$endpoint_url)) {
			    $endpoint_url = $match[1];
			    error_log("Found endpoint URL <{$endpoint_url}> in HTTP header");
			}
		    }
		    
		    // Nope, so see if we have it in meta
		    if (!$endpoint_url) {
			if (preg_match('/<link href="([^"]+)" rel="key" ?\/?>/i', $page, $match)) {
			    $endpoint_url = $match[1];
			    error_log("Found endpoint URL <{$endpoint_url}> in meta");
			}
		    }
		    
		    // Still nope, see if we've linked to it in page
		    if (!$endpoint_url) {
			if (preg_match('/<a href="([^"]+)" rel="key" ?\/?>/i', $page, $match)) {
			    $endpoint_url = $match[1];
			    error_log("Found endpoint URL <{$endpoint_url}> in a link on the page");
			}
		    }
		    if (!$endpoint_url) {
			if (preg_match('/<a rel="key" href="([^"]+)" ?\/?>/i', $page, $match)) {
			    $endpoint_url = $match[1];
			    error_log("Found endpoint URL <{$endpoint_url}> in a link on the page");
			}
		    }
		    
		    $key = null;
		    if ($endpoint_url) {
			// Yes, we have an endpoint URL, go get the key data
			error_log("Retrieving key data...");
			$key = trim(file_get_contents($endpoint_url));
		    }
		    
		    // If no key data, try and find key data within a classed block on the page
		    if (!$key) {
			if (preg_match('/<[^\s]+ class="[^"]*key[^"]*">([^<]*)/im', $page, $match)) {
			    error_log("Still no key data, looking on the page...");
			    $key = $match[1];
			}
		    }
		    
		    // We have some key data, try and use it!
		    if ($key) {
			error_log("Some key data was found... $key");
			return $key;
			
		    }
		    
		    
		    error_log("No key data found :(");
		}
		else
		    error_log("Could not load $url");
		
		return false;
	    }
	    
            function registerPages() {
		
		// PGP Public key endpoint
		\Idno\Core\site()->addPageHandler('/profile/([A-Za-z0-9]+)/publickey\.asc', '\IdnoPlugins\OpenPGPSignin\Pages\PublicKey');
                
		// Extend header to include public key
		\Idno\Core\site()->template()->extendTemplate('shell/head','openpgpsignin/head');
		
		
		// Register an account menu
		\Idno\Core\site()->template()->extendTemplate('account/menu/items', 'openpgpsignin/account/menu');
		\Idno\Core\site()->addPageHandler('account/pgpkeys', '\IdnoPlugins\OpenPGPSignin\Pages\Account');
		
		// Login endpoint
		\Idno\Core\site()->addPageHandler('account/pgpkeys/login', '\IdnoPlugins\OpenPGPSignin\Pages\Login');
		
		// When a friend is added, we want to retrieve their keys
		\Idno\Core\site()->addEventHook('follow', function(\Idno\Core\Event $event) {

		    $user = $event->data()['user'];
                    $following = $event->data()['following'];

                    if (($user instanceof \Idno\Entities\User) && ($following instanceof \Idno\Entities\User)) {
                        
			if ($publickey = $this->findPublicKey($following->getURL())) {
			    // We found a public key here, so...
			    
			    
			    // Save it to the keyring
			    $gpg = new \gnupg();
			    $result = $gpg->import($publickey);

			    // Save a signature against the user
			    if ($result && isset($result['fingerprint'])) {
				error_log("Imported public key, with fingerprint {$result['fingerprint']}");

				// Save against following user
				
				$following->pgp_publickey_fingerprint = $result['fingerprint'];
				$following->save();
				
			    } else {
				error_log("Key data could not be imported");
			    }
			    
			}
			
                    }

                });
		
		// Signature specified on any page, grab it and save it
		if (isset($_REQUEST['signature'])) {
		    error_log("Ooo... we have a signature, saving in session for later...");
		    $_SESSION['_PGP_SIGNATURE'] = $_REQUEST['signature'];
		    error_log("Signature is: {$_SESSION['_PGP_SIGNATURE']}");
		}
		
		try {

		    // Log user in based on their signature (if there is no logged in user, and signature present in session
		    if (isset($_SESSION['_PGP_SIGNATURE']) && (!\Idno\Core\site()->session()->currentUser())) {

			$signature = $_SESSION['_PGP_SIGNATURE'];

			$user_id = null;
			$request_url = null;
			if (preg_match_all("/(https?:\/\/[^\s]+)/", $signature, $matches, PREG_SET_ORDER)) {
				$user_id = $matches[0][0];
				$request_url = $matches[1][0]; 
			}
			
			// Check that this is not sent to the wrong site
			if (!$request_url) throw new \Exception ("Request URL missing from signature");
			if (!\Idno\Common\Entity::isLocalUUID($request_url)) throw new \Exception ("Sorry, you're requesting a URL which does not belong to this site!");
			
			// Now, we check the timestamp to check for Replay attacks
			$now = time();
			if (preg_match('/([0-9]{4}-?[0-9]{2}-?[0-9]{2}T[0-9]{2}:?[0-9]{2}:?[0-9]{2}[+-Z]?([0-9]{2,4}:?([0-9]{2})?)?)/',$signature, $matches))
			    $timestamp = strtotime($matches[0]);
			
			if (!$timestamp) throw new \Exception("No timestamp was found in signature! Make sure you include a timestamp in ISO8601 format");
			if (($timestamp < $now - 5) || ($timestamp > $now + 5)) // 5 seconds grace either way
			    throw new \Exception ("Sorry, you could not be logged in because the timestamp was wrong. Check your computer's clock is correct, and ideally connect to an internet time server!");
			
			// TODO: Check for double logins and log both out / store timestamp for a few seconds...
			
			if ($user_id) {

			    $gpg = new \gnupg();

			    //$signature = substr($signature, strpos($signature, '-----BEGIN PGP SIGNATURE-----')); // GPG verify won't take the full sig, so only return the appropriate bit

			    if ($info = $gpg->verify($signature, false)) {

				if (isset($info[0]))
				    $info = $info[0];
				
				if ($info['summary']==4)
				    throw new \Exception('Sorry, the signature appears to be invalid'); // Not sure, but I think summary of 4 is an invalid signature, no docs, but seems to be from observation

				error_log("Signature verified as : " . print_r($info, true));

				// Get some key info
				$key_info = $gpg->keyinfo($info['fingerprint']);

				// Get user
				if ($user = $this->getUserByKeyInfo($key_info))
				{
				    // Check nonce, make sure this isn't a replay
				    $nonce = md5($user_id.$request_url.$timestamp);
			
				    // Pull nonces from user or create
				    $nonces = unserialize($user->ops_nonces);
				    if ((!$nonces) || (!is_array($nonces)))
					$nonces = [];
			
				    // Check nonce in list, exception if exist
				    if (isset($nonces[$nonce])) throw new \Exception('Sorry, I\'ve seen this login before. Refresh your page, clear your caches, chant three times and try again...');
			
				    // Add nonce to list, sort by timestamp, then delete old ones (5 mins)
				    $nonces[$nonce] = $timestamp;
			
				    // Remove old nonces
				    $cutoff = time()-300; // 5minutes
				    $tmp_n = [];
				    foreach ($nonces as $n => $t) {
					if ($t > $cutoff)
					    $tmp_n[$n] = $t;
				    }
				    $nonces = $tmp_n;
			    
				    $user->ops_nonces = serialize($nonces); 
				    $user->save();
				    
				    // Got a user, log them in!
				    error_log("{$info['fingerprint']} matches user {$user->title}");

				    \Idno\Core\site()->session()->addMessage("Welcome {$user->title}!");

				    \Idno\Core\site()->session()->logUserOn($user);
				}
				else 
				    throw new \Exception ("Fingerprint {$info['fingerprint']} does not match an known user!");

			    } else
				throw new \Exception ("Problem verifying your signature: " . $gpg->geterror());

			}
			else 
			    throw new \Exception("No profile link found in signature, aborting.");
		    }
		
		} catch (\Exception $e) {
		    // RegisterPages doesn't have a default exception handler, so lets do something with error messages
		    \Idno\Core\site()->session()->addMessage($e->getMessage(), 'alert-danger');
		}
		
		// Check following in canview (valid user, still logged in, but no longer following)
		\Idno\Core\site()->addEventHook('canView', function(\Idno\Core\Event $event) {
		    
		    // What object are we talking about?
		    $object = $event->data['object'];

		    // Get owner of object
		    $owner = $object->getOwner();

		    if ($owner->isFollowing(\Idno\Core\site()->session()->currentUser()))
			return true;
		    else
			\Idno\Core\site()->session()->addMessage("Sorry, this user doesn't follow you...", 'alert-danger');
		    
		    return false;
		    
		});
		
		// Hook in and extend the canView architecture, checking signatures
		/*\Idno\Core\site()->addEventHook('canView', function(\Idno\Core\Event $event) {
		    
		    if ($_SESSION['_PGP_SIGNATURE']) {
			
			$signature = $_SESSION['_PGP_SIGNATURE'];
			
			$user_id = null;
			if (preg_match("/(https?:\/\/[^\s]+)/", $sig, $matches))
				$user_id = $matches[1];
			
			if ($user_id) {
				
			    // What object are we talking about?
			    $object = $event->data['object'];

			    // Get owner of object
			    $owner = $object->getOwner();

			    // See if $user_id is in my following list as either a uuid or a profile url
			    $remote_user = NULL;
			    foreach ($owner->getFollowingArray() as $uuid => $data){
				if ((trim($uuid, ' /') == trim($user_id, ' /')) || (trim($data['url'], ' /') == trim($user_id, ' /')))
				{
				    // We found a following user, 
				    $remote_user = \Idno\Common\Entity::getByUUID($uuid);
				}
			    }

			    if ($remote_user) {

				if ($fingerprint = $remote_user->pgp_publickey_fingerprint) {

				    $gpg = new \gnupg();

				    $signature = substr($signature, strpos($signature, '-----BEGIN PGP SIGNATURE-----')); // GPG verify won't take the full sig, so only return the appropriate bit

				    if ($info = $gpg->verify($signature, false)) {

					if ($info['fingerprint'] == $fingerprint)
					    $event->setResponse(true);
					else 
					    throw new \Exception ("Fingerprint {$info['fingerprint']} does not match $fingerprint");

				    } else
					throw new \Exception ("Problem verifying your signature: " . $gpg->geterror());

				} else
				    throw new \Exception ("Problem verifying your signature, no fingerprint found!");


			    } else	
				throw new \Exception ("Sorry, this user doesn't follow you...");
				
			}
			else 
			    throw new \Exception("No link found in signature, aborting.");
			
		    }
		});*/
            }
        }
    }
