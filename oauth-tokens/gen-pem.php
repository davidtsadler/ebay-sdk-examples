<?php
/**
 * Copyright 2017 David T. Sadler
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

function genSelfSignedCertificate()
{
    if (file_exists(__DIR__.'/server.pem')) {
        return;
    }
    $uniq = uniqid();
    $certificateData = [
        'countryName'            => 'US',
        'stateOrProvinceName'    => $uniq,
        'localityName'           => $uniq,
        'organizationName'       => "$uniq.com",
        'organizationalUnitName' => $uniq,
        'commonName'             => $uniq,
        'emailAddress'           => "$uniq@example.com"
    ];
    $privateKey = openssl_pkey_new();
    $certificate = openssl_csr_new($certificateData, $privateKey);
    $certificate = openssl_csr_sign($certificate, null, $privateKey, 365);
    $pem_passphrase = 'abracadabra';
    $pem = [];
    openssl_x509_export($certificate, $pem[0]);
    openssl_pkey_export($privateKey, $pem[1], $pem_passphrase);
    $pem = implode($pem);
    $pemfile = __DIR__.'/server.pem';
    file_put_contents($pemfile, $pem);
}
