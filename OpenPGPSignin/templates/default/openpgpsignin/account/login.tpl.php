<div class="row">
    <form id="pgp-login" action="<?= $vars['return_url']; ?>" method="post">
	<div class="control-group">
                <div class="controls">
                    <p>
                        Relax, we're logging you in right now...
                    </p>
                    <div class="control-group">
                        <div class="controls">
                            <textarea id="data" name="data" class="span4" style="display:none;"><?= htmlspecialchars($vars['data']); ?></textarea>
                        </div>
                    </div>
		    <input type="hidden" name="user" value="<?= $vars['user']; ?>" />
                </div>
            </div>
    </form>
</div>