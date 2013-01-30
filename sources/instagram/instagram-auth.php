<?php

$client_id = "a0392b4db0da46f49239530d65dab923"; // Your app client id
$client_secret = "38a33559ac454e69b53439fb1fdc2952"; // Your app client secret
$redirect_url = "http://colorlabsproject.com/instagram-auth.php?forward_url=" . $_REQUEST['forward_url'];
$code = $_REQUEST['code'];

$curl_opt = "client_id={$client_id}";
$curl_opt .= "&client_secret={$client_secret}";
$curl_opt .= "&grant_type=authorization_code";
$curl_opt .= "&redirect_uri={$redirect_url}";
$curl_opt .= "&code={$code}";

$ch = curl_init("https://api.instagram.com/oauth/access_token");
curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_opt);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$result = json_decode($result);
$forward_url = base64_decode( $_REQUEST['forward_url'] );

header("Location: {$forward_url}&token={$result->access_token}");
