<?php
require_once 'config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['entry'][0]['changes'][0]['value']['form_id'])) {
    $formId = $input['entry'][0]['changes'][0]['value']['form_id'];
    $leadgenId = $input['entry'][0]['changes'][0]['value']['leadgen_id'];

    $stmt = $conn->prepare("SELECT page_id FROM fb_forms WHERE form_id = ?");
    $stmt->bind_param("s", $formId);
    $stmt->execute();
    $result = $stmt->get_result();
    $form = $result->fetch_assoc();

    if ($form) {
        $pageId = $form['page_id'];

        $stmt = $conn->prepare("SELECT access_token FROM fb_pages WHERE page_id = ?");
        $stmt->bind_param("s", $pageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $page = $result->fetch_assoc();

        if ($page) {
            $accessToken = $page['access_token'];

            try {
                $response = $fb->get('/' . $leadgenId, $accessToken);
                $lead = $response->getDecodedBody();

                $stmt = $conn->prepare("INSERT INTO leads (form_id, lead_id, lead_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lead_data = VALUES(lead_data)");
                $stmt->bind_param("iss", $formId, $leadgenId, json_encode($lead));
                $stmt->execute();
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
        }
    }
}
?>
