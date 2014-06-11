<?php

$session = \Idno\Core\site()->session();
$user = $session->currentUser();

?><a href="javascript:(function(){location.href='<?= \Idno\Core\site()->config()->url; ?>account/pgpkeys/login?u='+encodeURIComponent(location.href);})();" class="btn">Sign in as <?= $user->getHandle(); ?>...</a>