<?php

    namespace IdnoPlugins\OpenPGPSignin {
        class Main extends \Idno\Common\Plugin {
	    
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

                    $user = $event->data()['following'];

                    if ($user instanceof User) {
                        
			if ($publickey = $this->findPublicKey($user->getURL())) {
			    // We found a public key here, so...
			    
			    
			    // Save it to the keyring
			    $gpg = new \gnupg();
			    $result = $gpg->import($public_key);

			    // Save a signature against the user
			    if ($result && isset($result['fingerprint'])) {
				error_log("Imported public key, with fingerprint {$result['fingerprint']}");

				$user->pgp_publickey_fingerprint = $result['fingerprint'];
				$user->save();
			    }
			    
			}
			
                    }

                });
		
		
		// Extend private denied page (may require upstream change)
		
		
		
		// Use openPGP.js to do encrypted message signin?
		
            }
        }
    }
