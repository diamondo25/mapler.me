using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A wz property that extends to a different property
	/// </summary>
	public class WzExtendedProperty : IWzImageProperty
	{
		#region Fields
		internal IWzImageProperty extendedProperty;
		internal int endOfBlock = 0;
		internal uint offset = 0;
		internal string name;
		internal WzBinaryReader reader;
		internal IWzObject parent;
		internal WzImage imgParent;
		#endregion

		#region Inherited Members
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
		public override WzPropertyType PropertyType { get { return WzPropertyType.Extended; } }
		public override void WriteValue(WzBinaryWriter writer)
		{
			writer.Write((byte)9);
			long beforePos = writer.BaseStream.Position;
			writer.Write(0); // Placeholder
			ExtendedProperty.WriteValue(writer);
			int len = (int)(writer.BaseStream.Position - beforePos);
			long newPos = writer.BaseStream.Position;
			writer.BaseStream.Position = beforePos;
			writer.Write(len - 4);
			writer.BaseStream.Position = newPos;
		}
		/// <summary>
		/// Dispose the object
		/// </summary>
		public override void Dispose()
		{
			name = null;
			extendedProperty.Dispose();
			reader = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The property that this WzExtendedProperty extends to
		/// </summary>
		public IWzImageProperty ExtendedProperty { get { return extendedProperty; } set { extendedProperty = value; } }
		/// <summary>
		/// Creates a blank WzEntendedProperty
		/// </summary>
		public WzExtendedProperty() { }
		/// <summary>
		/// Creates a WzExtendedProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzExtendedProperty(string name)
		{
			this.name = name;
		}
		internal WzExtendedProperty(uint offset, string name)
		{
			this.name = name;
			this.offset = offset;
		}
		internal WzExtendedProperty(uint offset, int eob, string name)
		{
			endOfBlock = eob;
			this.name = name;
			this.offset = offset;
		}
		#endregion

		#region Parsing Methods
		/// <summary>
		/// Parses the extended property
		/// </summary>
		/// <param name="reader">The current BinaryReader that's reading the wz file</param>
		internal void ParseExtendedProperty(WzBinaryReader reader)
		{
			this.reader = reader;
			DumpBlock(endOfBlock, name);
		}
		internal void DumpBlock(int endOfBlock, string name)
		{
			switch (reader.ReadByte())
			{
				case 0x1B:
					ExtractMore(endOfBlock, name, reader.ReadStringAtOffset(offset + reader.ReadInt32()));
					return;
				case 0x73:
					ExtractMore(endOfBlock, name, "");
					return;
			}
		}

        internal string ExtractString()
        {
            switch (reader.ReadByte())
            {
                case 0x00:
                case 0x73:
                    return reader.ReadString();
                case 0x01:
                case 0x1B:
                    int moreoffset = reader.ReadInt32();
                    return reader.ReadStringAtOffset(offset + moreoffset);
            }
            return null;
        }

		internal void ExtractMore(int eob, string name, string iname)
		{
			if (iname == "")
				iname = reader.ReadString();
			switch (iname)
			{
				case "Property":
					WzSubProperty subProp = new WzSubProperty(name) { Parent = parent, ParentImage = imgParent };
					reader.BaseStream.Position += 2;
					subProp.AddProperties(IWzImageProperty.ParsePropertyList(offset, reader, subProp, imgParent));
					extendedProperty = subProp;
					break;
				case "Canvas":
					WzCanvasProperty canvasProp = new WzCanvasProperty(name) { Parent = parent, ParentImage = imgParent };
					reader.BaseStream.Position++;
					if (reader.ReadByte() == 1)
					{
						reader.BaseStream.Position += 2;
						canvasProp.AddProperties(IWzImageProperty.ParsePropertyList(offset, reader, canvasProp, imgParent));
					}
					canvasProp.PngProperty = new WzPngProperty(reader) { Parent = canvasProp, ParentImage = imgParent };
					extendedProperty = canvasProp;
					break;
				case "Shape2D#Vector2D":
					WzVectorProperty vecProp = new WzVectorProperty(name) { Parent = parent, ParentImage = imgParent };
					vecProp.X = new WzCompressedIntProperty("X", reader.ReadCompressedInt()) { Parent = vecProp, ParentImage = imgParent };
					vecProp.Y = new WzCompressedIntProperty("Y", reader.ReadCompressedInt()) { Parent = vecProp, ParentImage = imgParent };
					extendedProperty = vecProp;
					break;
				case "Shape2D#Convex2D":
					WzConvexProperty convexProp = new WzConvexProperty(name) { Parent = parent, ParentImage = imgParent };
					int convexEntryCount = reader.ReadCompressedInt();
					for (int i = 0; i < convexEntryCount; i++)
					{
						WzExtendedProperty exProp = new WzExtendedProperty(offset, name) { Parent = convexProp, ParentImage = imgParent };
						exProp.ParseExtendedProperty(reader);
						convexProp.AddProperty(exProp);
					}
					extendedProperty = convexProp;
					break;
				case "Sound_DX8":
					WzSoundProperty soundProp = new WzSoundProperty(name) { Parent = parent, ParentImage = imgParent };
					soundProp.ParseSound(reader);
					extendedProperty = soundProp;
					break;
				case "UOL":
					reader.BaseStream.Position++;
                    extendedProperty = new WzUOLProperty(name, ExtractString()) { Parent = parent, ParentImage = imgParent };
                    /*
                    switch (reader.ReadByte())
					{
						case 0:
							extendedProperty = new WzUOLProperty(name, reader.ReadString()) { Parent = parent, ParentImage = imgParent };
							break;
						case 1:
							extendedProperty = new WzUOLProperty(name, reader.ReadStringAtOffset(offset + reader.ReadInt32())) { Parent = parent, ParentImage = imgParent };
							break;
					}
                    */
					break;
			}
		}
		#endregion
	}
}