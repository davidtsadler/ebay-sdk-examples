<?php
/**
 * Copyright 2014 David T. Sadler
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

function saveAttachment($data)
{
    $tempFilename = tempnam(sys_get_temp_dir(), 'attachment').'.zip';
    $fp = fopen($tempFilename, 'wb');
    if ($fp) {
        fwrite($fp, $data);
        fclose($fp);
        return $tempFilename;
    } else {
        printf("Failed. Cannot open %s to write!\n", $tempFilename);
        return false;
    }
}

function unzipArchive($filename)
{
    printf("Unzipping %s...", $filename);

    $zip = new ZipArchive();
    if ($zip->open($filename)) {
        /**
         * Assume there is only one file in archives from eBay.
         */
        $xml = $zip->getFromIndex(0);
        if ($xml !== false) {
            print("Done\n");
            return $xml;
        } else {
            printf("Failed. No XML found in %s\n", $filename);
            return false;
        }
    } else {
        printf("Failed. Unable to unzip %s\n", $filename);
        return false;
    }
}
