using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A property that contains data for an MP3 file
	/// </summary>
	public class WzSoundProperty : IWzImageProperty
	{
		#region Fields
		internal string name;
		internal byte[] mp3bytes;
		internal IWzObject parent;
		internal WzImage imgParent;
		#endregion

		#region Inherited Members
		public override object WzValue { get { return SoundData; } }
		/// <summary>
		/// The parent of the object
		/// </summary>
		public override IWzObject Parent { get { return parent; } internal set { parent = value; } }
		/// <summary>
		/// The image that this property is contained in
		/// </summary>
		public override WzImage ParentImage { get { return imgParent; } internal set { imgParent = value; } }
		/// <summary>
		/// The name of the property
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		/// <summary>
		/// The WzPropertyType of the property
		/// </summary>
		public override WzPropertyType PropertyType { get { return WzPropertyType.Sound; } }
		public override void WriteValue(WzBinaryWriter writer)
		{
			writer.WriteStringValue("Sound_DX8", 0x73, 0x1B);
			writer.Write((byte)0);
			writer.WriteCompressedInt(SoundData.Length);
			writer.WriteCompressedInt(0);
			writer.Write(SoundData);
		}
		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.EmptyNamedTag("WzSound", this.Name));
		}
		/// <summary>
		/// Disposes the object
		/// </summary>
		public override void Dispose()
		{
			name = null;
			mp3bytes = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The data of the mp3 file
		/// </summary>
		public byte[] SoundData { get { return mp3bytes; } }
		/// <summary>
		/// Creates a blank WzSoundProperty
		/// </summary>
		public WzSoundProperty() { }
		/// <summary>
		/// Creates a WzSoundProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzSoundProperty(string name)
		{
			this.name = name;
		}
		#endregion

		#region Parsing Methods
		internal void ParseSound(WzBinaryReader reader)
		{
			reader.BaseStream.Position++;
			int soundDataLen = reader.ReadCompressedInt();
			reader.ReadCompressedInt();
			mp3bytes = reader.ReadBytes(soundDataLen);
		}
		#endregion
	}
}