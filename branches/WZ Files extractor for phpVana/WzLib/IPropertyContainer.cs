using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MapleLib.WzLib
{
	public interface IPropertyContainer
	{
		void AddProperty(IWzImageProperty prop);
		void AddProperties(IWzImageProperty[] props);
		void RemoveProperty(string name);
		void ClearProperties();
	}
}