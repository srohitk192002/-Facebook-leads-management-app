<?php
require_once 'config/config.php';

$fb = new \Facebook\Facebook([
  'app_id' => 'YOUR_APP_ID',
  'app_secret' => 'YOUR_APP_SECRET',
  'default_graph_version' => 'v9.0',
]);

$helper = $fb->getRedirectLoginHelper();
$accessToken = $helper->getAccessToken();

if ($accessToken) {
    $response = $fb->get('/me?fields=id,name,email', $accessToken);
    $user = $response->getGraphUser();

    $stmt = $conn->prepare("INSERT INTO users (name, email, fb_access_token) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE fb_access_token = VALUES(fb_access_token)");
    $stmt->bind_param("sss", $user['name'], $user['email'], $accessToken);
    $stmt->execute();

    $_SESSION['fb_access_token'] = (string) $accessToken;
    $_SESSION['user_id'] = $user['id'];
}

header('Location: index.php');
exit;
?>
<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['fb_access_token'])) {
    header('Location: login.php');
    exit;
}

$accessToken = $_SESSION['fb_access_token'];

try {
    $response = $fb->get('/me/accounts', $accessToken);
    $pages = $response->getDecodedBody()['data'];

    foreach ($pages as $page) {
        $stmt = $conn->prepare("INSERT INTO fb_pages (user_id, page_id, page_name, access_token) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE page_name = VALUES(page_name), access_token = VALUES(access_token)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $page['id'], $page['name'], $page['access_token']);
        $stmt->execute();

        $response = $fb->get('/' . $page['id'] . '/leadgen_forms', $page['access_token']);
        $forms = $response->getDecodedBody()['data'];

        foreach ($forms as $form) {
            $stmt = $conn->prepare("INSERT INTO fb_forms (page_id, form_id, form_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE form_name = VALUES(form_name)");
            $stmt->bind_param("iss", $page['id'], $form['id'], $form['name']);
            $stmt->execute();
        }
    }

    echo "Leads synced successfully.";
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
?>
<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['fb_access_token'])) {
    header('Location: login.php');
    exit;
}

$accessToken = $_SESSION['fb_access_token'];
$formIds = $_POST['form_ids'] ?? [];

if (!empty($formIds)) {
    foreach ($formIds as $formId) {
        $response = $fb->get('/' . $formId . '/leads?fields=field_data,created_time', $accessToken);
        $leads = $response->getDecodedBody()['data'];

        foreach ($leads as $lead) {
            $leadId = $lead['id'];
            $leadData = json_encode($lead);

            $stmt = $conn->prepare("INSERT INTO leads (form_id, lead_id, lead_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lead_data = VALUES(lead_data)");
            $stmt->bind_param("iss", $formId, $leadId, $leadData);
            $stmt->execute();
        }
    }
    echo "Old leads fetched and updated successfully.";
}
?>
