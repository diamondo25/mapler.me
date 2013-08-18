using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib.WzProperties
{
	/// <summary>
	/// A property that's value is a string
	/// </summary>
	public class WzUOLProperty : IWzImageProperty
	{
		#region Fields
		internal string name, val;
		internal IWzObject parent;
		internal WzImage imgParent;
        internal IWzObject linkVal;
		#endregion

		#region Inherited Members
		public override object WzValue { get { return LinkValue; } }
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

		public override IWzImageProperty[] WzProperties
		{
			get
			{
                if (LinkValue == null) return null;
                return LinkValue is IWzImageProperty ? ((IWzImageProperty)LinkValue).WzProperties : ((IWzImageProperty)LinkValue).ToSubProperty().WzProperties;
			}
		}

		public override IWzImageProperty this[string name]
		{
			get
			{
                return LinkValue is IWzImageProperty ? ((IWzImageProperty)LinkValue)[name] : LinkValue is WzImage ? ((WzImage)LinkValue)[name] : null;
			}
		}

		public override IWzImageProperty GetFromPath(string path)
		{
            return LinkValue is IWzImageProperty ? ((IWzImageProperty)LinkValue).GetFromPath(path) : LinkValue is WzImage ? ((WzImage)LinkValue).GetFromPath(path) : null;
		}
		/// <summary>
		/// The WzPropertyType of the property
		/// </summary>
		public override WzPropertyType PropertyType { get { return WzPropertyType.UOL; } }

		public override void WriteValue(MapleLib.WzLib.Util.WzBinaryWriter writer)
		{
			writer.WriteStringValue("UOL", 0x73, 0x1B);
			writer.Write((byte)0);
			writer.WriteStringValue(Value, 0, 1);
		}

		public override void ExportXml(StreamWriter writer, int level)
		{
			writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.EmptyNamedValuePair("WzUOL", this.Name, this.Value));
		}

		/// <summary>
		/// Disposes the object
		/// </summary>
		public override void Dispose()
		{
			name = null;
			val = null;
		}
		#endregion

		#region Custom Members
		/// <summary>
		/// The value of the property
		/// </summary>
		public string Value { get { return val; } set { val = value; } }

        public IWzObject LinkValue
        {
            get
            {
                if (linkVal == null)
                {
                    string[] paths = val.Split('/');
                    linkVal = (IWzObject)parent;
                    foreach (string path in paths)
                    {
                        if (path == "..")
                        {
                            linkVal = (IWzObject)linkVal.Parent;
                        }
                        else
                        {
                            if (linkVal is IWzImageProperty)
                                linkVal = ((IWzImageProperty)linkVal)[path];
                            else if (linkVal is WzImage)
                                linkVal = ((WzImage)linkVal)[path];
                            else if (linkVal is WzDirectory)
                                linkVal = ((WzDirectory)linkVal)[path];
                            else if (linkVal == null) // BROKEN ITEMS YAY
                                break;
                            else throw new System.Exception("Invalid linkVal");
                        }
                    }
                }
                if (linkVal == null)
                {
                    System.Console.WriteLine("BROKEN: {0} -> {1}", this.AbsolutePath, val);
                    /*
                    string[] paths = val.Split('/');
                    linkVal = (IWzObject)parent;
                    foreach (string path in paths)
                    {
                        if (path == "..")
                        {
                            linkVal = (IWzObject)linkVal.Parent;
                        }
                        else
                        {
                            if (linkVal is IWzImageProperty)
                                linkVal = ((IWzImageProperty)linkVal)[path];
                            else if (linkVal is WzImage)
                                linkVal = ((WzImage)linkVal)[path];
                            else if (linkVal is WzDirectory)
                                linkVal = ((WzDirectory)linkVal)[path];
                            else if (linkVal == null) // BROKEN ITEMS YAY
                                break;
                            else throw new System.Exception("Invalid linkVal");
                        }
                    }
                    */
                }
                return linkVal;
            }
        }


		/// <summary>
		/// Creates a blank WzUOLProperty
		/// </summary>
		public WzUOLProperty() { }

		/// <summary>
		/// Creates a WzUOLProperty with the specified name
		/// </summary>
		/// <param name="name">The name of the property</param>
		public WzUOLProperty(string name)
		{
			this.name = name;
		}

		/// <summary>
		/// Creates a WzUOLProperty with the specified name and value
		/// </summary>
		/// <param name="name">The name of the property</param>
		/// <param name="value">The value of the property</param>
		public WzUOLProperty(string name, string value)
		{
			this.name = name;
            if (value == null || value == "")
            {
                System.Console.WriteLine("OH MY GAH");
            }
			this.val = value;
		}
		#endregion
	}
}