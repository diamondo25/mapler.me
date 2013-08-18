using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A property that is stored in the wz file with a signed byte and possibly followed by an int. If the 
	/// signed byte is equal to -128, the value is is the int that follows, else the value is the byte.
	/// </summary>
	public class WzCompressedIntProperty : IWzImageProperty
	{
		#region Fields
		internal string name;
		internal int val;
		internal IWzObject parent;
		internal WzImage imgParent;
		#endregion

		#region Inherited Members
		public override object WzValue { get { return Value; } }
		/// <summary>
		/// The parent of the object
		/// </summary>
		public override IWzObject Parent { get { return parent; } internal set { parent = value; } }
		/// <summary>
		/// The image that this property is contained in
		/// </summary>
		public override WzImage ParentImage { get { return imgParent; } internal set { imgParent = value; } }
		/// <summary>
		/// The WzPropertyType of the property
		/// </summary>
		public override WzPropertyType PropertyType { get { return WzPropertyType.CompressedInt; } }
		/// <summary>
		/// The name of the property
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		public override void WriteValue(MapleLib.WzLib.Util.WzBinaryWriter writer)
		{
			writer.Write((byte)3);
			writer.WriteCompressedInt(Value);
		}
		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.EmptyNamedValuePair("WzCompressedInt", this.Name, this.Value.ToString()));
		}
		/// <summary>
		/// Dispose the object
		/// </summary>
		public override void Dispose()
		{
			name = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The value of the property
		/// </summary>
		public int Value { get { return val; } set { val = value; } }
		/// <summary>
		/// Creates a blank WzCompressedIntProperty
		/// </summary>
		public WzCompressedIntProperty() { }
		/// <summary>
		/// Creates a WzCompressedIntProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzCompressedIntProperty(string name)
		{
			this.name = name;
		}
		/// <summary>
		/// Creates a WzCompressedIntProperty with the specified name and value
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <param name="value">The value of the property</param>
		public WzCompressedIntProperty(string name, int value)
		{
			this.name = name;
			this.val = value;
		}
		#endregion
	}
}