using System.Collections.Generic;
using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib
{
	/// <summary>
	/// A class that parses and contains the data of a wz list file
	/// </summary>
	public class WzListFile : IWzFile
	{
		#region Fields
		internal byte[] wzFileBytes;
		internal List<string> listEntries = new List<string>();
		internal string name = "";
		internal byte[] WzIv;
		#endregion

		/// <summary>
		/// Name of the WzListFile
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		/// <summary>
		/// The entries in the list wz file
		/// </summary>
		public string[] WzListEntries { get { return listEntries.ToArray(); } }
		/// <summary>
		/// The WzObjectType of the file
		/// </summary>
		public override WzObjectType ObjectType { get { return WzObjectType.File; } }
		public override IWzObject Parent { get { return null; } internal set { } }
		public override void Dispose()
		{
			wzFileBytes = null;
			name = null;
			listEntries.Clear();
			listEntries = null;
		}

		/// <summary>
		/// Open a wz list file from a file on the disk
		/// </summary>
		/// <param name="filePath">Path to the wz file</param>
		public WzListFile(string filePath, byte[] iv)
		{
			name = Path.GetFileName(filePath);
			WzIv = iv;
			FileStream tmpStrm = File.Open(filePath, FileMode.Open);
			wzFileBytes = new byte[tmpStrm.Length];
			tmpStrm.Read(wzFileBytes, 0, (int)tmpStrm.Length);
			tmpStrm.Close();
		}
		/// <summary>
		/// Open a wz list file from an array of bytes in the memory
		/// </summary>
		/// <param name="fileBytes">The wz file in the memory</param>
		public WzListFile(byte[] fileBytes, byte[] iv)
		{
			wzFileBytes = fileBytes;
			WzIv = iv;
		}

		/// <summary>
		/// Parses the wz list file
		/// </summary>
		public void ParseWzFile()
		{
			//WzTools.CreateWzKey(WzMapleVersion.GMS);//what?
			WzBinaryReader wzParser = new WzBinaryReader(new MemoryStream(wzFileBytes), WzIv);
			while (wzParser.PeekChar() != -1)
			{
				int Len = wzParser.ReadInt32();
				char[] List = new char[Len];
				for (int i = 0; i < Len; i++)
					List[i] = (char)wzParser.ReadInt16();
				wzParser.ReadUInt16();
				string Decrypted = wzParser.DecryptString(List);
				if (wzParser.PeekChar() == -1)
					if (Decrypted[Decrypted.Length - 1] == '/')
						Decrypted = Decrypted.TrimEnd("/".ToCharArray()) + "g"; // Last char should always be a g (.img)
				listEntries.Add(Decrypted);
			}
			wzParser.Close();
		}
		internal void SaveToDisk(string path)
		{

		}
	}
}