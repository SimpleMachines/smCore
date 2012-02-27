/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  SHA-256 implementation in JavaScript | (c) Chris Veness 2002-2010 | www.movable-type.co.uk	*/
/*   - see http://csrc.nist.gov/groups/ST/toolkit/secure_hashing.html							 */
/*		 http://csrc.nist.gov/groups/ST/toolkit/examples.html								   */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

var Sha256 = {
	hash: function(msg, utf8encode)
	{
		utf8encode = (typeof utf8encode == 'undefined') ? true : utf8encode;

		if (utf8encode)
			msg = Utf8.encode(msg);

		var K = [
			0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
			0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
			0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
			0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
			0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
			0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
			0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
			0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
		];

		var H = [0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19];

		msg += String.fromCharCode(0x80);

		var l = msg.length / 4 + 2;
		var N = Math.ceil(l / 16);
		var M = new Array(N);

		for (var i = 0; i < N; i++)
		{
			M[i] = new Array(16);
			for (var j = 0; j < 16; j++)
			{
				M[i][j] = (msg.charCodeAt(i * 64 + j * 4) << 24) | (msg.charCodeAt(i * 64 + j * 4 + 1) << 16) | (msg.charCodeAt(i * 64 + j * 4 + 2) << 8) | (msg.charCodeAt(i * 64 + j * 4 + 3));
			}
		}

		M[N-1][14] = ((msg.length-1)*8) / Math.pow(2, 32);
		M[N-1][14] = Math.floor(M[N-1][14]);
		M[N-1][15] = ((msg.length-1)*8) & 0xffffffff;

		var W = new Array(64);
		var a, b, c, d, e, f, g, h;
		for (var i = 0; i < N; i++)
		{
			for (var t = 0;  t < 16; t++)
				W[t] = M[i][t];

			for (var t = 16; t < 64; t++)
				W[t] = (Sha256.sigma1(W[t - 2]) + W[t - 7] + Sha256.sigma0(W[t - 15]) + W[t - 16]) & 0xffffffff;

			a = H[0];
			b = H[1];
			c = H[2];
			d = H[3];
			e = H[4];
			f = H[5];
			g = H[6];
			h = H[7];

			for (var t = 0; t < 64; t++)
			{
				var T1 = h + Sha256.Sigma1(e) + Sha256.Ch(e, f, g) + K[t] + W[t];
				var T2 = Sha256.Sigma0(a) + Sha256.Maj(a, b, c);
				h = g;
				g = f;
				f = e;
				e = (d + T1) & 0xffffffff;
				d = c;
				c = b;
				b = a;
				a = (T1 + T2) & 0xffffffff;
			}

			H[0] = (H[0] + a) & 0xffffffff;
			H[1] = (H[1] + b) & 0xffffffff; 
			H[2] = (H[2] + c) & 0xffffffff; 
			H[3] = (H[3] + d) & 0xffffffff; 
			H[4] = (H[4] + e) & 0xffffffff;
			H[5] = (H[5] + f) & 0xffffffff;
			H[6] = (H[6] + g) & 0xffffffff; 
			H[7] = (H[7] + h) & 0xffffffff; 
		}

		return Sha256.toHexStr(H[0]) + Sha256.toHexStr(H[1]) + Sha256.toHexStr(H[2]) + Sha256.toHexStr(H[3]) + Sha256.toHexStr(H[4]) + Sha256.toHexStr(H[5]) + Sha256.toHexStr(H[6]) + Sha256.toHexStr(H[7]);
	},

	ROTR: function(n, x)
	{
		return (x >>> n) | (x << (32-n));
	},
	Sigma0: function(x)
	{
		return Sha256.ROTR(2,  x) ^ Sha256.ROTR(13, x) ^ Sha256.ROTR(22, x);
	},
	Sigma1: function(x)
	{
		return Sha256.ROTR(6,  x) ^ Sha256.ROTR(11, x) ^ Sha256.ROTR(25, x);
	},
	sigma0: function(x)
	{
		return Sha256.ROTR(7,  x) ^ Sha256.ROTR(18, x) ^ (x >>> 3);
	},
	sigma1: function(x)
	{
		return Sha256.ROTR(17, x) ^ Sha256.ROTR(19, x) ^ (x >>> 10);
	},
	Ch: function(x, y, z)
	{
		return (x & y) ^ (~x & z);
	},
	Maj: function(x, y, z)
	{
		return (x & y) ^ (x & z) ^ (y & z);
	},

	toHexStr: function(n)
	{
		var s = "", v;
		for (var i = 7; i >= 0; i--)
		{
			v = (n >>> (i * 4)) & 0xf;
			s += v.toString(16);
		}
		return s;
	}
};

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
/*  Utf8 class: encode / decode between multi-byte Unicode characters and UTF-8 multiple		  */
/*			  single-byte character encoding (c) Chris Veness 2002-2010						 */
/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

var Utf8 = {
	encode: function(strUni)
	{
		var strUtf = strUni.replace(
			/[\u0080-\u07ff]/g,
			function(c)
			{ 
				var cc = c.charCodeAt(0);
				return String.fromCharCode(0xc0 | cc >> 6, 0x80 | cc & 0x3f);
			}
		);

		strUtf = strUtf.replace(
			/[\u0800-\uffff]/g,
			function(c)
			{ 
				var cc = c.charCodeAt(0); 
				return String.fromCharCode(0xe0 | cc >> 12, 0x80 | cc >> 6 & 0x3F, 0x80 | cc & 0x3f);
			}
		);
		return strUtf;
	},
	decode: function(strUtf)
	{
		var strUni = strUtf.replace(
			/[\u00e0-\u00ef][\u0080-\u00bf][\u0080-\u00bf]/g,
			function(c)
			{
				var cc = ((c.charCodeAt(0) & 0x0f) << 12) | ((c.charCodeAt(1) & 0x3f) << 6) | ( c.charCodeAt(2) & 0x3f); 
				return String.fromCharCode(cc);
			}
		);

		strUni = strUni.replace(
			/[\u00c0-\u00df][\u0080-\u00bf]/g,
			function(c)
			{
				var cc = (c.charCodeAt(0) & 0x1f) << 6 | c.charCodeAt(1) & 0x3f;
				return String.fromCharCode(cc);
			}
		);
		return strUni;
	}
};