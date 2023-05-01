var degrees         = Math.PI/180;

/**
 * Function converts from RGB to XYZ to CIELab
 */
function RGBtoCILab(r,g,b) {
	// Ref values for : Observer= 2°, Illuminant= D55
	ref_X = 95.682;
	ref_Y = 100.00;
	ref_Z = 92.149;

	// From http://www.easyrgb.com/index.php?X=MATH&H=02#text2 [RGB->XYZ]
	var_R = ( r / 255 );        //R from 0 to 255
	var_G = ( g / 255 );        //G from 0 to 255
	var_B = ( b / 255 );        //B from 0 to 255

	if ( var_R > 0.04045 ) {
		var_R = Math.pow( ( var_R + 0.055 ) / 1.055 , 2.4);
	} else {
		var_R = var_R / 12.92;
	}
	if ( var_G > 0.04045 ) {
		var_G = Math.pow( ( var_G + 0.055 ) / 1.055 , 2.4);
	} else  {
		var_G = var_G / 12.92;
	}
	if ( var_B > 0.04045 ) {
		var_B = Math.pow( ( var_B + 0.055 ) / 1.055 , 2.4);
	} else {
		var_B = var_B / 12.92;
	}

	var_R = var_R * 100;
	var_G = var_G * 100;
	var_B = var_B * 100;

	//Observer. = 2°, Illuminant = D65
	X = var_R * 0.4124 + var_G * 0.3576 + var_B * 0.1805;
	Y = var_R * 0.2126 + var_G * 0.7152 + var_B * 0.0722;
	Z = var_R * 0.0193 + var_G * 0.1192 + var_B * 0.9505;

	// From http://www.easyrgb.com/index.php?X=MATH&H=02#text2 [XYZ —> CIE-L*ab]
	var_X = X / ref_X;          //ref_X =  95.047   Observer= 2°, Illuminant= D65
	var_Y = Y / ref_Y;          //ref_Y = 100.000
	var_Z = Z / ref_Z;          //ref_Z = 108.883

	if ( var_X > 0.008856 ) var_X = Math.pow(var_X , ( 1/3 ));
	else                    var_X = ( 7.787 * var_X ) + ( 16 / 116 );
	if ( var_Y > 0.008856 ) var_Y = Math.pow(var_Y , ( 1/3 ));
	else                    var_Y = ( 7.787 * var_Y ) + ( 16 / 116 );
	if ( var_Z > 0.008856 ) var_Z = Math.pow(var_Z , ( 1/3 ));
	else                    var_Z = ( 7.787 * var_Z ) + ( 16 / 116 );

	L = ( 116 * var_Y ) - 16;
	a = 500 * ( var_X - var_Y );
	b = 200 * ( var_Y - var_Z );

	return {'L':L, 'a':a,'b':b};
}

function CILabtoRGB(l, a, b) {
	// Ref values for : Observer= 2°, Illuminant= D55
	var ref_X = 95.682;
	var ref_Y = 100.00;
	var ref_Z = 92.149;

	var var_Y = ( parseFloat(l) + 16.0 ) / 116.0;
	var var_X = parseFloat(a) / 500.0 + var_Y;
	var var_Z = var_Y - parseFloat(b) / 200.0;

	if ( Math.pow(var_Y, 3.0) > 0.008856 ) {
		var_Y = Math.pow(var_Y, 3.0);
	} else {
		var_Y = ( var_Y - 16.0 / 116.0 ) / 7.787;
	}
	if ( Math.pow(var_X, 3.0) > 0.008856 ) {
		var_X = Math.pow(var_X, 3.0);
	} else {
		var_X = ( var_X - 16.0 / 116.0 ) / 7.787;
	}
	if ( Math.pow(var_Z, 3.0) > 0.008856 ) {
		var_Z = Math.pow(var_Z, 3.0);
	} else {
		var_Z = ( var_Z - 16.0 / 116.0 ) / 7.787;
	}

	var X = ref_X * var_X;     //ref_X =  95.047     Observer= 2°, Illuminant= D65
	var Y = ref_Y * var_Y;     //ref_Y = 100.000
	var Z = ref_Z * var_Z;     //ref_Z = 108.883

	var_X = X / 100        //X from 0 to  95.047      (Observer = 2°, Illuminant = D65)
	var_Y = Y / 100        //Y from 0 to 100.000
	var_Z = Z / 100        //Z from 0 to 108.883

	var var_R = var_X *  3.2406 + var_Y * -1.5372 + var_Z * -0.4986
	var var_G = var_X * -0.9689 + var_Y *  1.8758 + var_Z *  0.0415
	var var_B = var_X *  0.0557 + var_Y * -0.2040 + var_Z *  1.0570

	if ( var_R > 0.0031308 ) {
		var_R = 1.055 * ( Math.pow(var_R, ( 1 / 2.4 )) ) - 0.055;
	} else {
		var_R = 12.92 * var_R;
	}
	if ( var_G > 0.0031308 ) {
		var_G = 1.055 * ( Math.pow(var_G, ( 1 / 2.4 )) ) - 0.055;
	} else {
		var_G = 12.92 * var_G;
	}
	if ( var_B > 0.0031308 ) {
		var_B = 1.055 * ( Math.pow(var_B, ( 1 / 2.4 )) ) - 0.055;
	} else {
		var_B = 12.92 * var_B;
	}

	R = var_R * 255
	G = var_G * 255
	B = var_B * 255

	return {'r':R, 'g':G,'b':B};
}

function rgbToHex(RGB) {return '#'+toHex(RGB.r)+toHex(RGB.g)+toHex(RGB.b)}
function toHex(n) {
	var hexChars = "0123456789ABCDEF";
	n = parseInt(n,10);
	if (isNaN(n)) return "00";
	n = Math.max(0,Math.min(n,255));
	return hexChars.charAt((n-n%16)/16) + hexChars.charAt(n%16);
}

/**
 * Function returns CIE-H° value
 */
function CieLab2Hue( var_a, var_b )
{
	// From http://www.easyrgb.com/index.php?X=DELT&H=05#text5 [Delta E 2000]
	var_bias = 0
	if ( var_a >= 0 && var_b == 0 ) return 0;
	if ( var_a <  0 && var_b == 0 ) return 180;
	if ( var_a == 0 && var_b >  0 ) return 90;
	if ( var_a == 0 && var_b <  0 ) return 270;
	if ( var_a >  0 && var_b >  0 ) var_bias = 0;
	if ( var_a <  0               ) var_bias = 180;
	if ( var_a >  0 && var_b <  0 ) var_bias = 360;
	return ( degrees * Math.atan( var_b / var_a ) + var_bias );
}

/**
 * Function calculates Delte 2000 between 2 CIELab values
 */
function deltaE(l1, a1, b1, l2, a2, b2) {
	// From http://www.easyrgb.com/index.php?X=DELT&H=05#text5 [Delta E 2000]

	//Set weighting factors to 1
	var k_L = 1.0;
	var k_C = 1.0;
	var k_H = 1.0;
	var lab1L = l1;
	var lab1A = a1;
	var lab1B = b1;
	var lab2L = l2;
	var lab2A = a2;
	var lab2B = b2;



	//Calculate Cprime1, Cprime2, Cabbar
	var c_star_1_ab = Math.sqrt(lab1A * lab1A + lab1B * lab1B);
	var c_star_2_ab = Math.sqrt(lab2A * lab2A + lab2B * lab2B);
	var c_star_average_ab = (c_star_1_ab + c_star_2_ab) / 2;

	var c_star_average_ab_pot7 = c_star_average_ab * c_star_average_ab * c_star_average_ab;
	c_star_average_ab_pot7 *= c_star_average_ab_pot7 * c_star_average_ab;

	var G = 0.5 * (1 - Math.sqrt(c_star_average_ab_pot7 / (c_star_average_ab_pot7 + 6103515625))); //25^7
	var a1_prime = (1 + G) * lab1A;
	var a2_prime = (1 + G) * lab2A;

	var C_prime_1 = Math.sqrt(a1_prime * a1_prime + lab1B * lab1B);
	var C_prime_2 = Math.sqrt(a2_prime * a2_prime + lab2B * lab2B);
	//Angles in Degree.
	var h_prime_1 = ((Math.atan2(lab1B, a1_prime) / degrees) + 360) % 360;
	var h_prime_2 = ((Math.atan2(lab2B, a2_prime) / degrees) + 360) % 360;

	var delta_L_prime = lab2L - lab1L;
	var delta_C_prime = C_prime_2 - C_prime_1;

	var h_bar = Math.abs(h_prime_1 - h_prime_2);
	var delta_h_prime;
	if (C_prime_1 * C_prime_2 == 0) delta_h_prime = 0;
	else
	{
		if (h_bar <= 180)
		{
			delta_h_prime = h_prime_2 - h_prime_1;
		}
		else if (h_bar > 180 && h_prime_2 <= h_prime_1)
		{
			delta_h_prime = h_prime_2 - h_prime_1 + 360.0;
		}
		else
		{
			delta_h_prime = h_prime_2 - h_prime_1 - 360.0;
		}
	}
	var delta_H_prime = 2 * Math.sqrt(C_prime_1 * C_prime_2) * Math.sin(delta_h_prime * Math.PI / 360);

	// Calculate CIEDE2000
	var L_prime_average = (lab1L + lab2L) / 2;
	var C_prime_average = (C_prime_1 + C_prime_2) / 2;

	//Calculate h_prime_average

	var h_prime_average;
	if (C_prime_1 * C_prime_2 == 0) h_prime_average = 0;
	else
	{
		if (h_bar <= 180)
		{
			h_prime_average = (h_prime_1 + h_prime_2) / 2;
		}
		else if (h_bar > 180 && (h_prime_1 + h_prime_2) < 360)
		{
			h_prime_average = (h_prime_1 + h_prime_2 + 360) / 2;
		}
		else
		{
			h_prime_average = (h_prime_1 + h_prime_2 - 360) / 2;
		}
	}
	var L_prime_average_minus_50_square = (L_prime_average - 50);
	L_prime_average_minus_50_square *= L_prime_average_minus_50_square;

	var S_L = 1 + ((.015 * L_prime_average_minus_50_square) / Math.sqrt(20 + L_prime_average_minus_50_square));
	var S_C = 1 + .045 * C_prime_average;
	var T = 1
		- .17 * Math.cos(DegToRad(h_prime_average - 30))
		+ .24 * Math.cos(DegToRad(h_prime_average * 2))
		+ .32 * Math.cos(DegToRad(h_prime_average * 3 + 6))
		- .2 * Math.cos(DegToRad(h_prime_average * 4 - 63));
	var S_H = 1 + .015 * T * C_prime_average;
	var h_prime_average_minus_275_div_25_square = (h_prime_average - 275) / (25);
	h_prime_average_minus_275_div_25_square *= h_prime_average_minus_275_div_25_square;
	var delta_theta = 30 * Math.exp(-h_prime_average_minus_275_div_25_square);

	var C_prime_average_pot_7 = C_prime_average * C_prime_average * C_prime_average;
	C_prime_average_pot_7 *= C_prime_average_pot_7 * C_prime_average;
	var R_C = 2 * Math.sqrt(C_prime_average_pot_7 / (C_prime_average_pot_7 + 6103515625));

	var R_T = -Math.sin(DegToRad(2 * delta_theta)) * R_C;

	var delta_L_prime_div_k_L_S_L = delta_L_prime / (S_L * k_L);
	var delta_C_prime_div_k_C_S_C = delta_C_prime / (S_C * k_C);
	var delta_H_prime_div_k_H_S_H = delta_H_prime / (S_H * k_H);

	var CIEDE2000 = Math.sqrt(
		delta_L_prime_div_k_L_S_L * delta_L_prime_div_k_L_S_L
		+ delta_C_prime_div_k_C_S_C * delta_C_prime_div_k_C_S_C
		+ delta_H_prime_div_k_H_S_H * delta_H_prime_div_k_H_S_H
		+ R_T * delta_C_prime_div_k_C_S_C * delta_H_prime_div_k_H_S_H
		);

	return CIEDE2000;
}

/**
 * Converts degress to Radians
 */
function DegToRad(deg) {
	return deg * degrees;
}
