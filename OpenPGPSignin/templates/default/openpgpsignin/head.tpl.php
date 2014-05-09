<?php
    $currentpage = \Idno\Core\site()->currentPage();
    
    if (is_callable([$currentpage, 'matchUrl']) && $currentpage->matchUrl('/profile/([A-Za-z0-9]+)/?')) {
	$url = $currentpage->currentUrl();
	$url = trim($url, '/ ') . '/';
	
	header("Link: <{$url}publickey.asc>; rel=\"key\"", false);
	?>
<link href="<?= $url; ?>publickey.asc" rel="key" />	
	<?php
    }
    