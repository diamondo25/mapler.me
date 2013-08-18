using System.Collections.Generic;
using System.IO;
using System;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace MapleLib.WzLib
{
	/// <summary>
	/// A .img contained in a wz directory
	/// </summary>
	public class WzImage : IWzObject, IPropertyContainer
	{
		//TODO: nest wzproperties in a wzsubproperty inside of WzImage

		#region Fields
		internal bool parsed = false;
		internal string name;
		internal int size, checksum;
		internal uint offset = 0;
		internal WzBinaryReader reader;
		internal List<IWzImageProperty> properties = new List<IWzImageProperty>();
		internal IWzObject parent;
		internal int blockStart = 0;
		internal long tempFileStart = 0;
		internal long tempFileEnd = 0;
		#endregion

		/// <summary>
		/// The parent of the object
		/// </summary>
		public override IWzObject Parent { get { return parent; } internal set { parent = value; } }
		/// <summary>
		/// The name of the image
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		/// <summary>
		/// Is the object parsed
		/// </summary>
		public bool Parsed { get { return parsed; } }
		/// <summary>
		/// The size in the wz file of the image
		/// </summary>
		public int BlockSize { get { return size; } set { size = value; } }
		/// <summary>
		/// The checksum of the image
		/// </summary>
		public int Checksum { get { return checksum; } set { checksum = value; } }
		/// <summary>
		/// The offset of the image
		/// </summary>
		public uint Offset { get { return offset; } set { offset = value; } }
		public int BlockStart { get { return blockStart; } }
		/// <summary>
		/// The properties contained in the image
		/// </summary>
		public IWzImageProperty[] WzProperties
		{
			get
			{
				if (reader != null && !parsed)
				{
					ParseImage();
				}

				List<IWzImageProperty> imgProperties = new List<IWzImageProperty>();
				foreach (IWzImageProperty iwp in properties)
				{
					if (iwp.PropertyType == WzPropertyType.Extended)
					{
						imgProperties.Add(((WzExtendedProperty)iwp).ExtendedProperty);
					}
					else
					{
						imgProperties.Add(iwp);
					}
				}
				return imgProperties.ToArray();
			}
		}

		/// <summary>
		/// Gets a wz property by it's name
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <returns>The wz property with the specified name</returns>
		public IWzImageProperty this[string name]
		{
			get
			{
				if (reader != null) if (!parsed) ParseImage();
				foreach (IWzImageProperty iwp in properties)
					if (iwp.Name.ToLower() == name.ToLower())
						if (iwp.PropertyType == WzPropertyType.Extended)
							return ((WzExtendedProperty)iwp).ExtendedProperty;
						else
							return iwp;
				return null;
			}
		}

		/// <summary>
		/// Gets a WzImageProperty from a path
		/// </summary>
		/// <param name="path">path to object</param>
		/// <returns>the selected WzImageProperty</returns>
		public IWzImageProperty GetFromPath(string path)
		{
			if (reader != null) if (!parsed) ParseImage();

			string[] segments = path.Split(new char[1] { '/' }, System.StringSplitOptions.RemoveEmptyEntries);
			if (segments[0] == "..")
			{
				return null;
			}

			//hack method of adding the properties
			WzSubProperty childProperties = new WzSubProperty();
			childProperties.AddProperties(properties.ToArray());

			IWzImageProperty ret = childProperties;
			for (int x = 0; x < segments.Length; x++)
			{
				bool foundChild = false;
				foreach (IWzImageProperty iwp in ret.WzProperties)
				{
					if (iwp.Name == segments[x])
					{
						if (iwp.PropertyType == WzPropertyType.Extended)
						{
							ret = ((WzExtendedProperty)iwp).ExtendedProperty;
						}
						else
						{
							ret = iwp;
						}
						foundChild = true;
						break;
					}
				}
				if (!foundChild)
				{
					return null;
				}
			}
			return ret;
		}
		/// <summary>
		/// The WzObjectType of the image
		/// </summary>
		public override WzObjectType ObjectType { get { if (reader != null) if (!parsed) ParseImage(); return WzObjectType.Image; } }

		/// <summary>
		/// Creates a blank WzImage
		/// </summary>
		public WzImage() { }
		/// <summary>
		/// Creates a WzImage with the given name
		/// </summary>
		/// <param name="name">The name of the image</param>
		public WzImage(string name)
		{
			this.name = name;
		}
		public WzImage(string name, Stream dataStream, WzMapleVersion mapleVersion)
		{
			this.name = name;
			this.reader = new WzBinaryReader(dataStream, WzTool.GetIvByMapleVersion(mapleVersion));
		}
		internal WzImage(string name, WzBinaryReader reader)
		{
			this.name = name;
			this.reader = reader;
			this.blockStart = (int)reader.BaseStream.Position;
		}

		public override void Dispose()
		{
			name = null;
			reader = null;
			if (properties != null)
			{
				foreach (IWzImageProperty prop in properties)
					prop.Dispose();
				properties.Clear();
				properties = null;
			}
		}


		/// <summary>
		/// Parses the image from the wz filetod
		/// </summary>
		/// <param name="wzReader">The BinaryReader that is currently reading the wz file</param>
		public void ParseImage()
		{
			long originalPos = reader.BaseStream.Position;
			reader.BaseStream.Position = offset;
			byte b = reader.ReadByte();
            string tmp = reader.ReadString();
            ushort tmp2 = reader.ReadUInt16();
			if (b != 0x73 || tmp != "Property" || tmp2 != 0)
				return;
			properties.AddRange(IWzImageProperty.ParsePropertyList(offset, reader, this, this));
			parsed = true;
		}

		public byte[] DataBlock
		{
			get
			{
				byte[] blockData = null;
				if (reader != null && size > 0)
				{
					blockData = reader.ReadBytes(size);
					reader.BaseStream.Position = blockStart;
				}
				return blockData;
			}
		}

		public void UnparseImage()
		{
			parsed = false;
			this.properties = new List<IWzImageProperty>();
		}

		internal void SaveImage(WzBinaryWriter writer)
		{
			if (reader != null && !parsed) ParseImage();
			WzSubProperty imgProp = new WzSubProperty();
			long startPos = writer.BaseStream.Position;
			imgProp.AddProperties(WzProperties);
			imgProp.WriteValue(writer);
			writer.StringCache.Clear();
			size = (int)(writer.BaseStream.Position - startPos);
		}

		public void ExportXml(StreamWriter writer, bool oneFile, int level)
		{
			if (oneFile)
			{
				writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.OpenNamedTag("WzImage", this.name, true));
				IWzImageProperty.DumpPropertyList(writer, level, WzProperties);
				writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.CloseTag("WzImage"));
			}
			else
			{
				throw new Exception("Under Construction");
			}
		}

		/// <summary>
		/// Adds a property to the image
		/// </summary>
		/// <param name="prop">Property to add</param>
		public void AddProperty(IWzImageProperty prop)
		{
			if (reader != null) if (!parsed) ParseImage();
			switch (prop.PropertyType)
			{
				case WzPropertyType.SubProperty:
				case WzPropertyType.Vector:
				case WzPropertyType.UOL:
				case WzPropertyType.Canvas:
				case WzPropertyType.Convex:
				case WzPropertyType.Sound:
					properties.Add(new WzExtendedProperty(prop.Name) { ExtendedProperty = prop });
					return;
				default:
					properties.Add(prop);
					return;
			}
		}
		public void AddProperties(IWzImageProperty[] props)
		{
			foreach (IWzImageProperty prop in props)
			{
				AddProperty(prop);
			}
		}
		/// <summary>
		/// Removes a property by name
		/// </summary>
		/// <param name="name">The name of the property to remove</param>
		public void RemoveProperty(string name)
		{
			if (reader != null) if (!parsed) ParseImage();
			for (int i = 0; i < properties.Count; i++)
				if (properties[i].Name.ToLower() == name.ToLower())
					properties.RemoveAt(i);
		}
		public void ClearProperties()
		{
			properties.Clear();
		}
	}
}