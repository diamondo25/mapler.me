using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A wz property which has a value which is a ushort
	/// </summary>
	public class WzUnsignedShortProperty : IWzImageProperty
	{
		#region Fields
		internal string name;
		internal ushort val;
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
		public override WzPropertyType PropertyType { get { return WzPropertyType.UnsignedShort; } }
		/// <summary>
		/// The name of the property
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		public override void WriteValue(MapleLib.WzLib.Util.WzBinaryWriter writer)
		{
			writer.Write((byte)2);
			writer.Write(Value);
		}
		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.EmptyNamedValuePair("WzUnsignedShort", this.Name, this.Value.ToString()));
		}
		/// <summary>
		/// Disposes the object
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
		public ushort Value { get { return val; } set { val = value; } }
		/// <summary>
		/// Creates a blank WzUnsignedShortProperty
		/// </summary>
		public WzUnsignedShortProperty() { }
		/// <summary>
		/// Creates a WzUnsignedShortProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzUnsignedShortProperty(string name)
		{
			this.name = name;
		}
		/// <summary>
		/// Creates a WzUnsignedShortProperty with the specified name and value
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <param name="value">The value of the property</param>
		public WzUnsignedShortProperty(string name, ushort value)
		{
			this.name = name;
			this.val = value;
		}
		#endregion
	}
}