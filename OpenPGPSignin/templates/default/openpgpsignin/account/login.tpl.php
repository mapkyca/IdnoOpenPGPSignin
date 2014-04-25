<div class="row">
    <form id="pgp-login" action="<?= $vars['return_url']; ?>" method="post">
	<div class="control-group">
                <div class="controls">
                    <p>
                        Relax, we're logging you in right now...
                    </p>
                    <div class="control-group">
                        <div class="controls">
                            <textarea id="signature" name="signature" class="span4" style="display:none;"><?= htmlspecialchars($vars['signature']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
    </form>
</div>