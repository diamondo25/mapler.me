using System.Collections.Generic;
using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A property that contains several WzExtendedPropertys
	/// </summary>
	public class WzConvexProperty : IWzImageProperty
	{
		#region Fields
		internal List<WzExtendedProperty> properties = new List<WzExtendedProperty>();
		internal string name;
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
		/// The WzPropertyType of the property
		/// </summary>
		public override WzPropertyType PropertyType { get { return WzPropertyType.Convex; } }
		/// <summary>
		/// The properties contained in the property
		/// </summary>
		public override IWzImageProperty[] WzProperties
		{
			get
			{
				List<IWzImageProperty> imgProperties = new List<IWzImageProperty>();
				foreach (WzExtendedProperty exProp in properties)
				{
					imgProperties.Add(exProp.ExtendedProperty);
				}
				return imgProperties.ToArray();
			}
		}
		/// <summary>
		/// The name of this property
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		/// <summary>
		/// Gets a wz property by it's name
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <returns>The wz property with the specified name</returns>
		public override IWzImageProperty this[string name]
		{
			get
            {
                if (name == "..")
                {
                    return (IWzImageProperty)Parent;
                }
				foreach (WzExtendedProperty iwp in properties)
					if (iwp.Name.ToLower() == name.ToLower())
						return iwp.ExtendedProperty;
				//throw new KeyNotFoundException("A wz property with the specified name was not found");
				return null;
			}
		}
		/// <summary>
		/// Gets a wz property by a path name
		/// </summary>
		/// <param name="path">path to property</param>
		/// <returns>the wz property with the specified name</returns>
		public override IWzImageProperty GetFromPath(string path)
		{
			string[] segments = path.Split(new char[1] { '/' }, System.StringSplitOptions.RemoveEmptyEntries);
			if (segments[0] == "..")
			{
				return ((IWzImageProperty)Parent)[path.Substring(name.IndexOf('/') + 1)];
			}
			IWzImageProperty ret = this;
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
		public override void WriteValue(MapleLib.WzLib.Util.WzBinaryWriter writer)
		{
			writer.WriteStringValue("Shape2D#Convex2D", 0x73, 0x1B);
			writer.WriteCompressedInt(ExtendedProperties.Length);
			for (int i = 0; i < ExtendedProperties.Length; i++)
			{
				ExtendedProperties[i].ExtendedProperty.WriteValue(writer);
			}
		}
		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.OpenNamedTag("WzConvex", this.Name, true));
			IWzImageProperty.DumpPropertyList(writer, level, WzProperties);
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.CloseTag("WzConvex"));
		}
		public override void Dispose()
		{
			name = null;
			foreach (WzExtendedProperty exProp in properties)
				exProp.Dispose();
			properties.Clear();
			properties = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The WzExtendedPropertys contained in this WzConvexProperty
		/// </summary>
		public WzExtendedProperty[] ExtendedProperties { get { return properties.ToArray(); } }
		/// <summary>
		/// Creates a blank WzConvexProperty
		/// </summary>
		public WzConvexProperty() { }
		/// <summary>
		/// Creates a WzConvexProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzConvexProperty(string name)
		{
			this.name = name;
		}
		/// <summary>
		/// Adds a WzExtendedProperty to the list of properties
		/// </summary>
		/// <param name="prop">The property to add</param>
		public void AddProperty(WzExtendedProperty prop)
		{
			properties.Add(prop);
		}
		public void ClearProperties()
		{
			properties.Clear();
		}

		#endregion
	}
}