<?php
//require_once __DIR__ . '/vendor/autoload.php';

$fb = new \Facebook\Facebook([
  'app_id' => 'YOUR_APP_ID',
  'app_secret' => 'YOUR_APP_SECRET',
  'default_graph_version' => 'v9.0',
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email', 'pages_read_engagement', 'pages_manage_ads', 'pages_read_engagement', 'pages_manage_posts', 'pages_show_list'];
$loginUrl = $helper->getLoginUrl('https://yourdomain.com/sync_leads.php', $permissions);

header('Location: ' . $loginUrl);
exit;
?>
