<?php

   $pitable = array (217, 120, 249, 196, 25, 221, 181, 237, 40, 233, 253, 121, 74, 160, 216, 157,
	            198, 126, 55, 131, 43, 118, 83, 142, 98, 76, 100, 136, 68, 139, 251, 162,
	            23, 154, 89, 245, 135, 179, 79, 19, 97, 69, 109, 141, 9, 129, 125, 50,
	            189, 143, 64, 235, 134, 183, 123, 11, 240, 149, 33, 34, 92, 107, 78, 130,
	            84, 214, 101, 147, 206, 96, 178, 28, 115, 86, 192, 20, 167, 140, 241, 220,
	            18, 117, 202, 31, 59, 190, 228, 209, 66, 61, 212, 48, 163, 60, 182, 38,
	            111, 191, 14, 218, 70, 105, 7, 87, 39, 242, 29, 155, 188, 148, 67, 3,
	            248, 17, 199, 246, 144, 239, 62, 231, 6, 195, 213, 47, 200, 102, 30, 215,
	            8, 232, 234, 222, 128, 82, 238, 247, 132, 170, 114, 172, 53, 77, 106, 42,
 	           150, 26, 210, 113, 90, 21, 73, 116, 75, 159, 208, 94, 4, 24, 164, 236,
	           194, 224, 65, 110, 15, 81, 203, 204, 36, 145, 175, 80, 161, 244, 112, 57,
	           153, 124, 58, 133, 35, 184, 180, 122, 252, 2, 54, 91, 37, 85, 151, 49,
	           45, 93, 250, 152, 227, 138, 146, 174, 5, 223, 41, 16, 103, 108, 186, 201,
	           211, 0, 230, 207, 225, 158, 168, 44, 99, 22, 1, 63, 88, 226, 137, 169,
 	           13, 56, 52, 27, 171, 51, 255, 176, 187, 72, 12, 95, 185, 177, 205, 46,
	           197, 243, 219, 71, 229, 165, 156, 119, 10, 166, 32, 104, 254, 127, 193, 173
   );
   $kunci16 = [];

function ExpandKey($pass) {
global $pitable, $kunci16;

   // kunci yang digunakan panjangnya 128 karakter atau byte
   $panjangkunci=128;
   $panjangpass = strlen($pass);
   $kunci = [];
   $t = strlen($pass);
   $bits = $t * 8;
   $t8 = floor(($bits+7) / 8);
   $tm = 255 >> (($t8 * 8) - $bits);

   for ($i = 0; $i < $panjangpass; $i++) {
        $kunci[$i] = ord(substr($pass, $i, 1));
   }
   for ($i = $panjangpass; $i < $panjangkunci; $i++) {
        $kunci[$i] = $pitable[($kunci[$i-1] + $kunci[$i-$t]) % 256]	;
   }
   $kunci[$panjangkunci-$t8] = $pitable[$kunci[$panjangkunci-$t8] & $tm];

   for ($i=$panjangkunci - 1 - $t8; $i >=0; $i--) {
        $kunci[$i] = $pitable[$kunci[$i+1] ^ $kunci[$i + $t8]];
   }
   $panjangkunci16 = $panjangkunci / 2;
   for ($i=0; $i < $panjangkunci16; $i++) {
         $kunci16[$i] = $kunci[$i*2] * 256 + $kunci[$i*2+1];
   }
}

function enkrip_RC2($teks) {
global $kunci16;

   $atemp = [];
   print $teks . "\n";
   for ($j=0; $j < 4; $j++) {
        $atemp[$j] = ord(substr($teks, $j*2, 1)) * 256 + ord(substr($teks, $j*2+1, 1));
   }
    // lakukan mixround 5 kali
    $j = 0;
    for ($i=0; $i < 5; $i++) {
         $x = ($atemp[0] + ($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]) % 65536;
         $j++;
         $atemp[0] = (($x << 1) | ($x >> 15)) % 65536;
         $x = ($atemp[1] + ($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]) % 65536;
         $j++;
         $atemp[1] = (($x << 2) | ($x >> 14)) % 65536;
         $x = ($atemp[2] + ($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]) % 65536;
         $j++;
         $atemp[2] = (($x << 3) | ($x >> 13)) % 65536;
         $x = ($atemp[3] + ($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]) % 65536;
         $j++;
         $atemp[3] = (($x << 5) | ($x >> 11)) % 65536;
    }

    // lakukan mashround 1 kali
    $atemp[0] += $kunci16[$atemp[3] & 63];   $atemp[0] = $atemp[0] % 65536;
    $atemp[1] += $kunci16[$atemp[0] & 63];   $atemp[1] = $atemp[1] % 65536;
    $atemp[2] += $kunci16[$atemp[1] & 63];   $atemp[2] = $atemp[2] % 65536;
    $atemp[3] += $kunci16[$atemp[2] & 63];   $atemp[3] = $atemp[3] % 65536;


    // lakukan mixround 6 kali
    for ($i=0; $i < 6; $i++) {
        $x = ($atemp[0] + ($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[0] = (($x << 1) | ($x >> 15)) % 65536;
        $x = ($atemp[1] + ($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[1] = (($x << 2) | ($x >> 14)) % 65536;
        $x = ($atemp[2] + ($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[2] = (($x << 3) | ($x >> 13)) % 65536;
        $x = ($atemp[3] + ($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[3] = (($x << 5) | ($x >> 11)) % 65536;
    }
    // lakukan mashround 1 kali
    $atemp[0] += $kunci16[$atemp[3] & 63];    $atemp[0] = $atemp[0] % 65536;
    $atemp[1] += $kunci16[$atemp[0] & 63];    $atemp[1] = $atemp[1] % 65536;
    $atemp[2] += $kunci16[$atemp[1] & 63];    $atemp[2] = $atemp[2] % 65536;
    $atemp[3] += $kunci16[$atemp[2] & 63];    $atemp[3] = $atemp[3] % 65536;

    // lakukan mixround 5 kali
    for ($i=0; $i < 5; $i++) {
        $x = ($atemp[0] + ($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[0] = (($x << 1) | ($x >> 15)) % 65536;
        $x = ($atemp[1] + ($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[1] = (($x << 2) | ($x >> 14)) % 65536;
        $x = ($atemp[2] + ($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[2] = (($x << 3) | ($x >> 13)) % 65536;
        $x = ($atemp[3] + ($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]) % 65536;
        $j++;
        $atemp[3] = (($x << 5) | ($x >> 11)) % 65536;
    }

    // hasil enkripsi dijadikan karakter
    $xhasil = "";
    for ($i=0; $i < 4; $i++) {
        $xhasil .= chr($atemp[$i] / 256) . chr($atemp[$i] %256);
    }
    return $xhasil;
}

function RC2_Enkripsi($teks, $pass) {
global $kunci16;

   $hasil = "";
   $temp = "";
   if ($pass == "") return "";

   ExpandKey($pass);  // melakukan expansi kunci menjadi 128 byte atau 1024-bit
   // plainteks dipilah-pilah menjadi 8 karakter atau byte (1 blok 8 karakter atau byte)

   $j = ceil(strlen($teks) / 8);  
   for ($i = 0; $i < $j; $i++) {
        if (strlen($teks) >= 8) {
           $temp = substr($teks, 0, 8);
        } else {
           $temp = $teks . substr(chr(0).chr(0).chr(0), 0, 4-strlen($teks));
        }
        $hasil .= enkrip_RC2($temp);
        $teks = substr($teks, 8);  // karakter plainteks yang sudah dienkrip, dihilangkan dari plainteks
   }
   return $hasil;
}   

function dekrip_RC2($teks) {
global $kunci16;

   $atemp = [];
   for ($j=0; $j < 4; $j++) {
        $atemp[$j] = ord(substr($teks, $j*2, 1)) * 256 + ord(substr($teks, $j*2+1, 1));
   }

    // lakukan rmixround 5 kali
    $j = 63;
    for ($i=0; $i < 5; $i++) {
        $x = (($atemp[3] << 11) | ($atemp[3] >> 5)) % 65536;
		$atemp[3] = $x - (($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]);
		if ($atemp[3] < 0) $atemp[3] = ($atemp[3] + 4*65536) % 65536;
			
		$j--;
		$x = (($atemp[2] << 13) | ($atemp[2] >> 3)) % 65536;
		$atemp[2] = $x - (($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]);
		if ($atemp[2] < 0) $atemp[2] = ($atemp[2] + 4*65536) % 65536;

		$j--;
		$x = (($atemp[1] << 14) | ($atemp[1] >> 2)) % 65536;
		$atemp[1] = $x - (($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]);
		if ($atemp[1] < 0) $atemp[1] = ($atemp[1] + 4*65536) % 65536;

		$j--;
		$x = (($atemp[0] << 15) | ($atemp[0] >> 1)) % 65536;
		$atemp[0] = $x - (($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]);
		if ($atemp[0] < 0) $atemp[0] = ($atemp[0] + 4*65536) % 65536;
		$j--;	 
	}    

    // lakukan rmashround 1 kali
    $atemp[3] = ($atemp[3] - $kunci16[$atemp[2] & 63] + 65536) % 65536;
    $atemp[2] = ($atemp[2] - $kunci16[$atemp[1] & 63] + 65536) % 65536;
    $atemp[1] = ($atemp[1] - $kunci16[$atemp[0] & 63] + 65536) % 65536;
    $atemp[0] = ($atemp[0] - $kunci16[$atemp[3] & 63] + 65536) % 65536;

    // lakukan rmixround 6 kali
    for ($i=0; $i < 6; $i++) {
        $x = (($atemp[3] << 11) | ($atemp[3] >> 5)) % 65536;
		$atemp[3] = $x - (($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]);
		if ($atemp[3] < 0) $atemp[3] = ($atemp[3] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[2] << 13) | ($atemp[2] >> 3)) % 65536;
		$atemp[2] = $x - (($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]);
		if ($atemp[2] < 0) $atemp[2] = ($atemp[2] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[1] << 14) | ($atemp[1] >> 2)) % 65536;
		$atemp[1] = $x - (($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]);
		if ($atemp[1] < 0) $atemp[1] = ($atemp[1] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[0] << 15) | ($atemp[0] >> 1)) % 65536;
		$atemp[0] = $x - (($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]);
		if ($atemp[0] < 0) $atemp[0] = ($atemp[0] + 4*65536) % 65536;
		$j--;	 
   }

    // lakukan rmashround 1 kali
    $atemp[3] = ($atemp[3] - $kunci16[$atemp[2] & 63] + 65536) % 65536;
    $atemp[2] = ($atemp[2] - $kunci16[$atemp[1] & 63] + 65536) % 65536;
    $atemp[1] = ($atemp[1] - $kunci16[$atemp[0] & 63] + 65536) % 65536;
    $atemp[0] = ($atemp[0] - $kunci16[$atemp[3] & 63] + 65536) % 65536;

    // lakukan mixround 5 kali
    for ($i=0; $i < 5; $i++) {
        $x = (($atemp[3] << 11) | ($atemp[3] >> 5)) % 65536;
		$atemp[3] = $x - (($atemp[0] & (~$atemp[2])) + ($atemp[1] & $atemp[2]) + $kunci16[$j]);
		if ($atemp[3] < 0) $atemp[3] = ($atemp[3] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[2] << 13) | ($atemp[2] >> 3)) % 65536;
		$atemp[2] = $x - (($atemp[3] & (~$atemp[1])) + ($atemp[0] & $atemp[1]) + $kunci16[$j]);
		if ($atemp[2] < 0) $atemp[2] = ($atemp[2] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[1] << 14) | ($atemp[1] >> 2)) % 65536;
		$atemp[1] = $x - (($atemp[2] & (~$atemp[0])) + ($atemp[3] & $atemp[0]) + $kunci16[$j]);
		if ($atemp[1] < 0) $atemp[1] = ($atemp[1] + 4*65536) % 65536;
		$j--;
		$x = (($atemp[0] << 15) | ($atemp[0] >> 1)) % 65536;
		$atemp[0] = $x - (($atemp[1] & (~$atemp[3])) + ($atemp[2] & $atemp[3]) + $kunci16[$j]);
		if ($atemp[0] < 0) $atemp[0] = ($atemp[0] + 4*65536) % 65536;
		$j--;	     
	}
    // hasil enkripsi dijadikan karakter
    $xhasil = "";
    for ($i=0; $i < 4; $i++) {
        $xhasil .= chr($atemp[$i] / 256) . chr($atemp[$i] %256);
    }
    return $xhasil;
}

function RC2_Dekripsi($cteks, $pass) {
global $kunci16;

   $hasil = "";
   $temp = "";
   if ($pass == "") return "";
   ExpandKey($pass);  // melakukan expansi kunci menjadi 128 byte atau 1024-bit
   // plainteks dipilah-pilah menjadi 8 karakter atau byte (1 blok 8 karakter atau byte)
   $j = ceil(strlen($cteks) / 8);  
   for ($i = 0; $i < $j; $i++) {
       $temp = substr($cteks, 0, 8);
       $hasil .= dekrip_RC2($temp);
       $cteks = substr($cteks, 8);  // karakter plainteks yang sudah dienkrip, dihilangkan dari plainteks
   }
   return $hasil;
}   
?>