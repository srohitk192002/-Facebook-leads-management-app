<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['fb_access_token'])) {
    header('Location: login.php');
    exit;
}

$accessToken = $_SESSION['fb_access_token'];

$response = $fb->get('/me/accounts', $accessToken);
$pages = $response->getDecodedBody()['data'];

foreach ($pages as $page) {
    $stmt = $conn->prepare("SELECT access_token FROM fb_pages WHERE page_id = ?");
    $stmt->bind_param("s", $page['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $pageData = $result->fetch_assoc();

    if ($pageData) {
        $accessToken = $pageData['access_token'];

        $response = $fb->get('/' . $page['id'] . '/leadgen_forms', $accessToken);
        $forms = $response->getDecodedBody()['data'];

        foreach ($forms as $form) {
            $response = $fb->get('/' . $form['id'] . '/leads?fields=field_data,created_time', $accessToken);
            $leads = $response->getDecodedBody()['data'];

            foreach ($leads as $lead) {
                $leadId = $lead['id'];
                $leadData = json_encode($lead);

                $stmt = $conn->prepare("INSERT INTO leads (form_id, lead_id, lead_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lead_data = VALUES(lead_data)");
                $stmt->bind_param("iss", $form['id'], $leadId, $leadData);
                $stmt->execute();
            }
        }
    }
}

echo "Checked for new leads and synced successfully.";
?>
