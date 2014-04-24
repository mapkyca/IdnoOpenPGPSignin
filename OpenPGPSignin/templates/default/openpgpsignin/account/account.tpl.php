<?php
$session = \Idno\Core\site()->session();
$user = $session->currentUser();
?>
<div class="row">

    <div class="span10 offset1">
        <h1>PGP Keys</h1>
<?= $this->draw('account/menu') ?>
    </div>

</div>
<?php 
    if (($user->pgp_public_key) && ($user->pgp_private_key)) {
	?>
<div class="row">
    <div class="span9 offset1  well">
	<p>Now you've saved your keys, you can sign in to other sites (that use this plugin) using your public key. Use this bookmarklet to make this process easier.</p>
	
	<?= $this->draw('openpgpsignin/bookmarklet'); ?>
    </div>
    
</div>
<?php
    }
    ?>
<div class="row">
    <div class="span10 offset1">
        <form id="pgp-keys" action="/account/pgpkeys/" class="form-horizontal" method="post">
	    <input type="hidden" id="pgp-keys-userid" value="<?= $user->getHandle(); ?>@<?= \Idno\Core\site()->config()->host; ?>" />

            <div class="control-group">
                <div class="controls">
                    <p>
                        Paste the ASCII armored version of you PGP key in the boxes below, or save a blank box to generate a new keypair on the server.
                    </p>
                    <div class="control-group">
                        <label class="control-label" for="user_token">Public Key</label>
                        <div class="controls">
                            <textarea id="public_key" name="public_key" class="span4"><?= htmlspecialchars($user->pgp_public_key) ?></textarea>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="app_token">Private Key</label>
                        <div class="controls">
                            <textarea id="private_key" name="private_key" class="span4"><?= htmlspecialchars($user->pgp_private_key) ?></textarea>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <a href="#" id="generate" class="btn btn-danger">Generate...</a> <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>


<?= \Idno\Core\site()->actions()->signForm('/account/pgpkeys/') ?>
	  
        </form>
    </div>
</div>
