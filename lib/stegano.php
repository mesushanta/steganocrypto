<?php

class Stegano {

	function hidefile($imgfile, $textfile, $key) {
		$binstream = "";
		$recordstream = "";
		$make_odd = Array();
		$extension = strtolower(substr($imgfile['name'],-3));
		$nameofimage = substr($imgfile['name'], 0, -4);
		if($extension=="jpg")
		{
			$createFunc = "ImageCreateFromJPEG";
		} else {
			return "Only .jpg image files are supported";
		}
		$pic = @ImageCreateFromJPEG($imgfile['tmp_name']);
		$attributes = @getImageSize($imgfile['tmp_name']);
		$outpic = @ImageCreateFromJPEG($imgfile['tmp_name']);

		if(!$pic || !$outpic || !$attributes)
		{
			return "cannot create images - maybe GDlib not installed?";
		}

		$message = file_get_contents($textfile['tmp_name']);
		$message = $this->encrypt($message,$key);

		do
		{
		$border = chr(rand(32,127)).chr(rand(32,127)).chr(rand(32,127));
		} while(strpos($message,$border)!==false && strpos($textfile['name'],$border)!==false);

		$message = $border.$textfile['name'].$border.$message.$border;

		if(strlen($message)*8 > ($attributes[0]*$attributes[1])*3)
		{

			ImageDestroy($outpic);
			ImageDestroy($pic);
			return "Cannot fit ".$textfile['name']." in ".$imgfile['name'].".<br />".$textfile['name']." requires mask to contain at least ".(intval((strlen($data)*8)/3)+1)." pixels.<br />Maximum filesize that ".$imgfile['name']." can hide is ".intval((($attributes[0]*$attributes[1])*3)/8)." bytes";
		}

		for($i=0; $i<strlen($message) ; $i++)
		{
			$char = $message{$i};
			$binary = $this->asc2bin($char);
			$binstream .= $binary;
			for($j=0 ; $j<strlen($binary) ; $j++)
			{
				$binpart = $binary{$j};
				if($binpart=="0")
				{
					$oddeven[] = true;
				} else {
					$oddeven[] = false;
				}
			}
		}

		$y=0;
		for($x=0,$i=0;$i<(sizeof($oddeven)-2);$x++,$i+=3) {
			$rgb = ImageColorAt($pic,$x,$y);
			$rgbval = Array();
			$rgbval[] = ($rgb >> 16) & 0xFF;
			$rgbval[] = ($rgb >> 8) & 0xFF;
			$rgbval[] = $rgb & 0xFF;

			for($j=0 ; $j<3; $j++) {
				if($oddeven[$i+$j] === true && $this->is_even($rgbval[$j])) {
					$rgbval[$j]++;
				}
				else if($oddeven[$i+$j] === false && !$this->is_even($rgbval[$j])) {
					$rgbval[$j]--;
				}
			}
			$r = $rgbval[0];
			$g = $rgbval[1];
			$b = $rgbval[2];
			$temp_col = ImageColorAllocate($outpic,$r,$g,$b);
			ImageSetPixel($outpic,$x,$y,$temp_col);

			if($x==($attributes[0]-1))
			{
				$y++;
				$x=-1;
			}
		}

		header("Content-type: image/png");
		header("Content-Disposition: attachment; filename=".$nameofimage.".png");
		ImagePNG($outpic);
		ImageDestroy($outpic);
		ImageDestroy($pic);
		exit();
	}

	function hidemsg($imgfile, $filename, $message, $key) {
		$binstream = "";
		$recordstream = "";
		$make_odd = Array();

		$extension = strtolower(substr($imgfile['name'],-3));
		$nameofimage = substr($imgfile['name'], 0, -4);
		if($extension=="jpg")
		{
			$createFunc = "ImageCreateFromJPEG";
		} else {
			return "Only .jpg image files are supported";
		}

		$pic = @ImageCreateFromJPEG($imgfile['tmp_name']);
		$attributes = @getImageSize($imgfile['tmp_name']);
		$outpic = @ImageCreateFromJPEG($imgfile['tmp_name']);

		if(!$pic || !$outpic || !$attributes)
		{
			return "cannot create images - maybe GDlib not installed?";
		}

		$message = $this->encrypt($message,$key);
		$filename = $filename.".txt";

		do
		{
			$border = chr(rand(0,255)).chr(rand(0,255)).chr(rand(0,255));
		} while(strpos($message,$border)!==false && strpos($textfile['name'],$border)!==false);

		$message = $border.$filename.$border.$message.$border;

		if(strlen($message)*8 > ($attributes[0]*$attributes[1])*3)
		{
			// remove images
			ImageDestroy($outpic);
			ImageDestroy($pic);
			return "Cannot fit ".$hidefile['name']." in ".$maskfile['name'].".<br />".$hidefile['name']." requires mask to contain at least ".(intval((strlen($data)*8)/3)+1)." pixels.<br />Maximum filesize that ".$maskfile['name']." can hide is ".intval((($attributes[0]*$attributes[1])*3)/8)." bytes";
		}

		for($i=0; $i<strlen($message) ; $i++)
		{
			$char = $message{$i};
			$binary = $this->asc2bin($char);

			$binstream .= $binary;

			for($j=0 ; $j<strlen($binary) ; $j++)
			{
				$binpart = $binary{$j};
				if($binpart=="0")
				{
					$oddeven[] = "true";
				} else {
					$oddeven[] = "false";
				}
			}
		}
		$y=0;
		for($x=0,$i=0;$i<(sizeof($oddeven)-2);$x++,$i+=3) {
			$rgb = ImageColorAt($pic,$x,$y);
			$rgbval = Array();
			$rgbval[] = ($rgb >> 16) & 0xFF;
			$rgbval[] = ($rgb >> 8) & 0xFF;
			$rgbval[] = $rgb & 0xFF;

			for($j=0 ; $j<3; $j++) {
				if($oddeven[$i+$j] == "true" && $this->is_even($rgbval[$j])) {
					$rgbval[$j]++;
				}
				else if($oddeven[$i+$j] == "false" && !$this->is_even($rgbval[$j])) {
					$rgbval[$j]--;
				}
			}
			$r = $rgbval[0];
			$g = $rgbval[1];
			$b = $rgbval[2];
			$temp_col = ImageColorAllocate($outpic,$r,$g,$b);
			ImageSetPixel($outpic,$x,$y,$temp_col);

			if($x==($attributes[0]-1))
			{
				$y++;
				$x=-1;
			}
		}

		header("Content-type: image/png");
		header("Content-Disposition: attachment; filename=".$nameofimage.".png");
		ImagePNG($outpic);
		ImageDestroy($outpic);
		ImageDestroy($pic);
		exit();
	}

	function recover($maskfile, $key)
	{
		$binstream = "";
		$filename = "";
		$boundary = "";
		$ascii = "";
		$attributes = @getImageSize($maskfile['tmp_name']);
		$pic = @ImageCreateFromPNG($maskfile['tmp_name']);

		if(!$pic || !$attributes)
		{
			return "could not read image";
		}
		$bin_boundary = "";
		for($x=0 ; $x<8 ; $x++)
		{
			$bin_boundary .= $this->rgb2bin(ImageColorAt($pic, $x,0));
		}
		for($i=0 ; $i<strlen($bin_boundary) ; $i+=8)
		{
			$binchunk = substr($bin_boundary,$i,8);
			$boundary .= $this->bin2asc($binchunk);
		}
		$start_x = 8;

		for($y=0 ; $y<$attributes[1] ; $y++)
		{
			for($x=$start_x ; $x<$attributes[0] ; $x++)
			{
				$binstream .= $this->rgb2bin(ImageColorAt($pic, $x,$y));
				if(strlen($binstream)>=8)
				{
					$binchar = substr($binstream,0,8);
					$ascii .= $this->bin2asc($binchar);
					$binstream = substr($binstream,8);
				}
				if(strpos($ascii,$boundary)!==false)
				{
					$ascii = substr($ascii,0,strlen($ascii)-3);

					if(empty($filename))
					{
						$filename = $ascii;
						$ascii = "";
					} else {
						break 2;
					}
				}
			}
			$start_x = 0;
		}

		ImageDestroy($pic);
		$ascii = $this->decrypt($ascii,$key);
		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=".$filename);
		echo $ascii;
		exit();
	}

	private function is_even($num)
	{
		return ($num%2==0);
	}

	private function asc2bin($char)
	{
		return str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT);
	}


	private function bin2asc($bin)
	{
		return chr(bindec($bin));
	}

	private function rgb2bin($rgb) {
		$binstream = "";
		$red = ($rgb >> 16) & 0xFF;
		$green = ($rgb >> 8) & 0xFF;
		$blue = $rgb & 0xFF;

		if($this->is_even($red))
		{
			$binstream .= "1";
		} else {
			$binstream .= "0";
		}
		if($this->is_even($green))
		{
			$binstream .= "1";
		} else {
			$binstream .= "0";
		}
		if($this->is_even($blue))
		{
			$binstream .= "1";
		} else {
			$binstream .= "0";
		}
		return $binstream;
	}

	private function pkcs5_pad($text, $blocksize)
	{
	   $pad = $blocksize - (strlen($text) % $blocksize);
	   return $text . str_repeat(chr($pad), $pad);
	}

	private function pkcs5_unpad($text)
	{
	   $pad = ord($text{strlen($text)-1});
	   if ($pad > strlen($text)) return false;
	   return substr($text, 0, -1 * $pad);
	}

	private function encrypt($input,$ky)
	{
	    $key = $ky;
	    $size = mcrypt_get_block_size(MCRYPT_TRIPLEDES, 'ecb');
	    $input = $this->pkcs5_pad($input, $size);
	    $td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'ecb', '');
	    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	    mcrypt_generic_init($td, $key, $iv);
	    $data = mcrypt_generic($td, $input);
	    mcrypt_generic_deinit($td);
	    mcrypt_module_close($td);
	    $data = base64_encode($data);
	    $data = urlencode($data);
	    return $data;
	}

	private function decrypt($crypt,$ky)
	{

	   $crypt = urldecode($crypt);
	   $crypt = base64_decode($crypt);
	   $key = $ky;
	   $td = mcrypt_module_open (MCRYPT_TRIPLEDES, '', 'ecb', '');
	   $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	   mcrypt_generic_init($td, $key, $iv);
	   $decrypted_data = mdecrypt_generic ($td, $crypt);
	   mcrypt_generic_deinit ($td);
	   mcrypt_module_close ($td);
	   $decrypted_data = $this->pkcs5_unpad($decrypted_data);
	   $decrypted_data = rtrim($decrypted_data);
	   return $decrypted_data;
	}

}

?>
