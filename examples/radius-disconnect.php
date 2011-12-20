<?php
/*
Copyright (c) 2011, Gabriel Blanchard <gabe@teksavvy.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions 
are met:

1. Redistributions of source code must retain the above copyright 
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright 
   notice, this list of conditions and the following disclaimer in the 
   documentation and/or other materials provided with the distribution.
3. The names of the authors may not be used to endorse or promote products 
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

This code cannot simply be copied and put under the GNU Public License or 
any other GPL-like (LGPL, GPL2) License.

Sends a Radius dynamic Disconnect Request message to a NAS.

*/
<?php

if(!extension_loaded('radius')) {
    if (preg_match('/windows/i', getenv('OS'))) {
        dl('php_radius.dll');
    } else {
        dl('radius.so');
    }
}

$nas = 'localhost';
$radport = 1700;
$sharedsecret = 'testing123';

$res = radius_acct_open();

radius_add_server($res, $nas, $radport, $sharedsecret, 3, 1);

radius_create_request($res, RADIUS_DISCONNECT_REQUEST);

radius_put_string($res, RADIUS_USER_NAME, "username");
radius_put_string($res, RADIUS_ACCT_SESSION_ID, "1234567");

$reply = radius_send_request($res);

switch ($reply) {
	case RADIUS_COA_ACK:
	case RADIUS_DISCONNECT_ACK;
		echo "CoA-ACK\n";
		break;
	case RADIUS_COA_NAK:
	case RADIUS_DISCONNECT_NAK;
		echo "CoA-NAK\n";
		break;
	default:
		echo "Unsupported reply\n";
		exit();
}

while ($resa = radius_get_attr($res)) {

    $data = $resa['data'];
    $value = radius_cvt_int($data);

    switch ($value) {
        case 401:
            echo "Unsupported Attribute\n";
            break;
        case 402:
            echo "Missing Attribute\n";
            break;
        case 403:
            echo "NAS Identification mismatch\n";
            break;
        case 404:
            echo "Invalid Request\n";
            break;
        case 503:
            echo "Session context not found\n";
            break;
        case 506:
            echo "Resources unavailable\n";
            break;
        default:
            echo "Unsupported Error-Cause\n";
    }
}

radius_close($res);

?>
