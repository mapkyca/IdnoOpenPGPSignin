/**
 * Account form javascript
 */
$(document).ready(function(){
   
   $('#generate').click(function(event){
	var openpgp = window.openpgp;
	
	if (($('#public_key').val() == "") && ($('#private_key').val() == "")) { 
	    
	    key = openpgp.generateKeyPair(1, 2048, $('#pgp-keys-userid').val(), '');
	    
	    $('#public_key').val(key.publicKeyArmored);
	    $('#private_key').val(key.privateKeyArmored);
	}
   });
   
});