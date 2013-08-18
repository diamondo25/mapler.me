using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MapleLib.WzLib
{
	public class WzHeader
	{
		public string Ident { get; set; }
		public string Copyright { get; set; }
		public ulong FSize { get; set; }
		public uint FStart { get; set; }

		public static WzHeader GetDefault()
		{
			WzHeader header = new WzHeader();
			header.Ident = "PKG1";
			header.Copyright = "Package file v1.0 Copyright 2002 Wizet, ZMS";
			header.FStart = 60;
			header.FSize = 0;
			return header;
		}
	}
}