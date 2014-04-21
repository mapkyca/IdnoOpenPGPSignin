<?php
    $currentpage = \Idno\Core\site()->currentPage();
    if ($currentpage->matchUrl('/profile/([A-Za-z0-9]+)/?')) {
	?>
<link href="<?= $currentpage->currentUrl(); ?>publickey.asc" rel="key" />	
	<?php
    }
    