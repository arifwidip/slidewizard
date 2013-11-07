<?php

$client_id = "a0392b4db0da46f49239530d65dab923"; // Your app client id
$client_secret = "38a33559ac454e69b53439fb1fdc2952"; // Your app client secret
$redirect_url = "http://colorlabsproject.com/instagram-auth.php?forward_url=" . $_REQUEST['forward_url'];
$code = $_REQUEST['code'];
$forward_url = base64_decode( $_REQUEST['forward_url'] );
?>

<script type="text/javascript">
	var forward_url = '<?php echo $forward_url; ?>',
			hash = window.location.hash,
			access_token = hash.split('#access_token=')[1];

	window.location.href = forward_url + '&token=' + access_token;
</script>
