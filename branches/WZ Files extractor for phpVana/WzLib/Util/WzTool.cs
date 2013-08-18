using System;
using System.Collections;
using System.IO;
using MapleLib.MapleCryptoLib;

namespace MapleLib.WzLib.Util
{
	public class WzTool
	{

		public static Hashtable StringCache = new Hashtable();

		public static UInt32 RotateLeft(UInt32 x, byte n)
		{
			return (UInt32)(((x) << (n)) | ((x) >> (32 - (n))));
		}

		public static UInt32 RotateRight(UInt32 x, byte n)
		{
			return (UInt32)(((x) >> (n)) | ((x) << (32 - (n))));
		}

		public static int GetCompressedIntLength(int i)
		{
			if (i > 127 || i < -127)
				return 5;
			return 1;
		}

		public static int GetEncodedStringLength(string s)
		{
			int len = 0;
			if (string.IsNullOrEmpty(s))
				return 1;
			bool unicode = false;
			foreach (char c in s)
				if (c > 255)
					unicode = true;
			if (unicode)
			{
				if (s.Length > 126)
					len += 5;
				else
					len += 1;
				len += s.Length * 2;
			}
			else
			{
				if (s.Length > 127)
					len += 5;
				else
					len += 1;
				len += s.Length;
			}
			return len;
		}

		public static int GetWzObjectValueLength(string s, byte type)
		{
			string storeName = type + "_" + s;
			if (s.Length > 4 && StringCache.ContainsKey(storeName))
			{
				return 5;
			}
			else
			{
				StringCache[storeName] = 1;
				return 1 + GetEncodedStringLength(s);
			}
		}

		public static T StringToEnum<T>(string name)
		{
			try
			{
				return (T)Enum.Parse(typeof(T), name);
			}
			catch
			{
				return default(T);
			}
		}

		public static byte[] GetIvByMapleVersion(WzMapleVersion ver)
		{
			switch (ver)
			{
				case WzMapleVersion.BMS:
					return CryptoConstants.WZ_MSEAIV;//?
				case WzMapleVersion.EMS:
					return CryptoConstants.WZ_MSEAIV;//?
				case WzMapleVersion.GMS:
					return CryptoConstants.WZ_GMSIV;
				case WzMapleVersion.CLASSIC:
				default:
					return new byte[4];
			}
		}
	}
}