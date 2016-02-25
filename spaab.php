<?php
/*
Copyright (c) 2016 Profitbyte AB, Linus Kimselius

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class SPAAB {
	/*
	 * Fill string with specified amount of zeroes
	 */
	public function zero($str, $length)
	{
		$j = $length-strlen($str);
		if ($j > 0)
		{
			for ($i=0;$i<$j;$i++)
			{
				$str = "0" . $str;
			}
		}
	
		return $str;
	}
	
	/*
	 * Convert from SPCS to SPAAB file format
	 */
	public function convert(
		$str, 
		$kundnr,
		$transkod,
		$inkomstkalla,
		$avtalsnr,
		$year, 
		$month, 
		$period
	)
	{
		if (!$this->validateTranskod($transkod))
			return false;

		if (!$this->validateInkomstkalla($inkomstkalla))
			return false;
			
		if (!$this->validateAvtalsnr($avtalsnr))
			return false;
			
		if (!$this->validateKundnr($kundnr))
			return false;
			
		if (strlen($year) != 4)
			return false;
			
		if (strlen($month) != 2)
			return false;
			
		if (strlen($period) != 2)
			return false;

		if (
			strlen($avtalsnr) != 9
			||
			!is_numeric($avtalsnr)
		)
			return false;
			
		if (!$str)
			return false;

		// Split the string into lines
		$lines = split("\n", $str);
		
		// Define our return string
		$retStr = "";
		
		$validLines = 1;
		foreach ($lines as $line) {
			
			// Split the lines to values
			$vals = split("\t", $line);
			
			// We require all of theese fields to proceed and that they are correct
			if (
				isset($vals[0]) 
				&& 
				isset($vals[1]) 
				&& 
				$vals[0] 
				&& 
				$vals[1]
				&& 
				Pnum::check($vals[0])
			)
			{
				$persnr = str_replace("-", "", $vals[0]);
				$inkomst = $this->zero(substr($vals[1], 0, strpos($vals[1],",")), 9);

				$retStr .= $transkod . $inkomstkalla . $kundnr . $persnr . $year . $month . $period . $inkomst . '+' . $avtalsnr . "\n";
				$validLines++;

			}
		}
		
		// Return false if more then 90 percent of lines are invalid...
		if ($validLines < (count($lines) * 0.9))
			return false;

		return $retStr;
	}
	
	/****** Functions for validation *******/
	
	/*
	 * Validate transaction code.
	 * Should be 5 digits
	 */
	public function validateTranskod($str)
	{
		if (strlen($str) == 5)
			return true;

		return false;
	}
	
	/*
	 * Validate income source.
	 * Should be 2 digits
	 */
	public function validateInkomstkalla($str)
	{
		if (strlen($str) == 2 && is_numeric($str))
			return true;

		return false;
	}
	
	/*
	 * Validate customer number.
	 * Should be 6 digits
	 */
	public function validateKundnr($str)
	{
		if (strlen($str) == 6 && is_numeric($str))
			return true;

		return false;
	}
	
	/*
	 * Validate agreement number.
	 * Should be 6 digits
	 */
	public function validateAvtalsnr($str)
	{
		if (strlen($str) == 9 && is_numeric($str))
			return true;

		return false;
	}
}
