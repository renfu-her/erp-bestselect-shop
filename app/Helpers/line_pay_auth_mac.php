<?php
    function auth_mac(array $data_array, string $uri, string $url, array $valid)
    {
        $authMacText = $valid['channelSecret'] . $uri . json_encode($data_array) . $valid['nonce'];
        $Authorization = base64_encode(hash_hmac('sha256', $authMacText, $valid['channelSecret'], true));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data_array),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-LINE-ChannelId: ' . $valid['channelId'],
                'X-LINE-Authorization-Nonce: ' . $valid['nonce'],
                'X-LINE-Authorization: ' . $Authorization
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);
    }