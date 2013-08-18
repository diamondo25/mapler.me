namespace MapleLib.WzLib
{
	public enum WzPropertyType
	{
		#region Regular
		Null,
		UnsignedShort,
		CompressedInt,
		ByteFloat,
		Double,
		String,
		Extended,
		#endregion

		#region Extended
		SubProperty,
		Canvas,
		Vector,
		Convex,
		Sound,
		UOL,
		#endregion

		#region Png
		PNG
		#endregion
	}
}