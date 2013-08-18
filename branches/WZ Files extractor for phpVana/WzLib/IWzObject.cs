using System;

namespace MapleLib.WzLib
{
	/// <summary>
	/// An interface for wz objects
	/// </summary>
	public abstract class IWzObject : IDisposable
	{

		public abstract void Dispose();

		/// <summary>
		/// The name of the object
		/// </summary>
		public abstract string Name { get; set; }
		/// <summary>
		/// The WzObjectType of the object
		/// </summary>
		public abstract WzObjectType ObjectType { get; }
		/// <summary>
		/// Returns the parent object
		/// </summary>
		public abstract IWzObject Parent { get; internal set; }
	}
}