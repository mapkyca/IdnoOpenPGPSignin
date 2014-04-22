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
	     */
	    private function findPublicKey($url) {
		// TODO
	    }
	    
            function registerPages() {
		
		// PGP Public key endpoint
		\Idno\Core\site()->addPageHandler('/profile/([A-Za-z0-9]+)/publickey\.asc', '\IdnoPlugins\OpenPGPSignin\Pages\PublicKey');
                
		// Extend header to include public key
		\Idno\Core\site()->template()->extendTemplate('shell/head','openpgpsignin/head');
		
		
		// Register an account menu
		\Idno\Core\site()->template()->extendTemplate('account/menu/items', 'openpgpsignin/account/menu');
		\Idno\Core\site()->addPageHandler('account/pgpkeys', '\IdnoPlugins\OpenPGPSignin\Pages\Account');
		
		
		
		
		// Extend private denied page (may require upstream change)
		
		// Listen to friending event to find and attach public key (may require upstream change)
		
		
		// Use openPGP.js to do encrypted message signin?
		
            }
        }
    }
