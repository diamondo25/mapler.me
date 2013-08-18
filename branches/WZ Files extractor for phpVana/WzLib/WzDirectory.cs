using System;
using System.Collections.Generic;
using System.IO;
using MapleLib.WzLib.Util;

namespace MapleLib.WzLib
{
	/// <summary>
	/// A directory in the wz file, which may contain sub directories or wz images
	/// </summary>
	public class WzDirectory : IWzObject
	{
		#region Fields
		internal List<WzImage> images = new List<WzImage>();
		internal List<WzDirectory> subDirs = new List<WzDirectory>();
		internal WzBinaryReader reader;
		internal uint offset = 0;
		internal string name;
		internal uint hash;
		internal int size, checksum, offsetSize;
		internal byte[] WzIv;
		internal IWzObject parent;
		#endregion

		#region Inherited Members
		/// <summary>  
		/// The parent of the object
		/// </summary>
		public override IWzObject Parent { get { return parent; } internal set { parent = value; } }
		/// <summary>
		/// The name of the directory
		/// </summary>
		public override string Name { get { return name; } set { name = value; } }
		/// <summary>
		/// The WzObjectType of the directory
		/// </summary>
		public override WzObjectType ObjectType { get { return WzObjectType.Directory; } }
		/// <summary>
		/// Disposes the obejct
		/// </summary>
		public override void Dispose()
		{
			name = null;
			reader = null;
            if (images != null)
            {
                foreach (WzImage img in images)
                    img.Dispose();
                foreach (WzDirectory dir in subDirs)
                    dir.Dispose();
                images.Clear();
                subDirs.Clear();
                images = null;
                subDirs = null;
            }
		}
		#endregion

		/// <summary>
		/// The size of the directory in the wz file
		/// </summary>
		public int BlockSize { get { return size; } set { size = value; } }
		/// <summary>
		/// The directory's chceksum
		/// </summary>
		public int Checksum { get { return checksum; } set { checksum = value; } }
		/// <summary>
		/// The wz images contained in the directory
		/// </summary>
		public WzImage[] WzImages { get { return images.ToArray(); } }
		/// <summary>
		/// The sub directories contained in the directory
		/// </summary>
		public WzDirectory[] WzDirectories { get { return subDirs.ToArray(); } }
		/// <summary>
		/// Offset of the folder
		/// </summary>
		public uint Offset { get { return offset; } set { offset = value; } }
		/// <summary>
		/// Returns a WzImage or a WzDirectory with the given name
		/// </summary>
		/// <param name="name">The name of the img or dir to find</param>
		/// <returns>A WzImage or WzDirectory</returns>
		public IWzObject this[string name]
		{
			get
			{
				foreach (WzImage i in images)
					if (i.Name.ToLower() == name.ToLower())
						return i;
				foreach (WzDirectory d in subDirs)
					if (d.Name.ToLower() == name.ToLower())
						return d;
				//throw new KeyNotFoundException("No wz image or directory was found with the specified name");
				return null;
			}
		}



		/// <summary>
		/// Creates a blank WzDirectory
		/// </summary>
		public WzDirectory() { }
		/// <summary>
		/// Creates a WzDirectory with the given name
		/// </summary>
		/// <param name="name">The name of the directory</param>
		public WzDirectory(string name)
		{
			this.name = name;
		}
		/// <summary>
		/// Creates a WzDirectory
		/// </summary>
		/// <param name="reader">The BinaryReader that is currently reading the wz file</param>
		/// <param name="blockStart">The start of the data block</param>
		/// <param name="parentname">The name of the directory</param>
		internal WzDirectory(WzBinaryReader reader, string dirName, uint verHash, byte[] WzIv)
		{
			this.reader = reader;
			this.name = dirName;
			this.hash = verHash;
			this.WzIv = WzIv;
		}

		/// <summary>
		/// Parses the WzDirectory
		/// </summary>
		internal void ParseDirectory()
		{
			int entryCount = reader.ReadCompressedInt();
			for (int i = 0; i < entryCount; i++)
			{
				byte type = reader.ReadByte();
				string fname = null;
				int fsize;
				int checksum;
				uint offset;

				long rememberPos = 0;
				if (type == 2)
				{
					int stringOffset = reader.ReadInt32();
					rememberPos = reader.BaseStream.Position;
					reader.BaseStream.Position = reader.Header.FStart + stringOffset;
					type = reader.ReadByte();
					fname = reader.ReadString();
				}
				else if (type == 3 || type == 4)
				{
					fname = reader.ReadString();
					rememberPos = reader.BaseStream.Position;
				}
				reader.BaseStream.Position = rememberPos;
				fsize = reader.ReadCompressedInt();
				checksum = reader.ReadCompressedInt();
				offset = reader.ReadOffset();
				if (type == 3)
				{
					WzDirectory subDir = new WzDirectory(reader, fname, hash, WzIv);
					subDir.BlockSize = fsize;
					subDir.Checksum = checksum;
					subDir.Offset = offset;
					subDir.Parent = this;
					subDirs.Add(subDir);
				}
				else
				{
					WzImage img = new WzImage(fname, reader);
					img.BlockSize = fsize;
					img.Checksum = checksum;
					img.Offset = offset;
					img.Parent = this;
					images.Add(img);
				}
			}

			foreach (WzDirectory subdir in subDirs)
			{
				reader.BaseStream.Position = subdir.offset;
				subdir.ParseDirectory();
			}
		}

		internal void SaveImages(BinaryWriter wzWriter, FileStream fs)
		{
			foreach (WzImage img in images)
			{
				fs.Position = img.tempFileStart;
				byte[] buffer = new byte[img.size];
				fs.Read(buffer, 0, img.size);
				wzWriter.Write(buffer);
			}
			foreach (WzDirectory dir in subDirs)
				dir.SaveImages(wzWriter, fs);
		}
		internal int GenerateDataFile(string fileName)
		{
			size = 0;
			int entryCount = subDirs.Count + images.Count;
			if (entryCount == 0)
			{
				offsetSize = 1;
				return (size = 0);
			}
			size = WzTool.GetCompressedIntLength(entryCount);
			offsetSize = WzTool.GetCompressedIntLength(entryCount);

			WzBinaryWriter imgWriter = null;
			MemoryStream memStream = null;
			FileStream fileWrite = new FileStream(fileName, FileMode.Append, FileAccess.Write);
			for (int i = 0; i < images.Count; i++)
			{
				memStream = new MemoryStream();
				imgWriter = new WzBinaryWriter(memStream, this.WzIv);
				images[i].SaveImage(imgWriter);
				images[i].checksum = 0;
				foreach (byte b in memStream.ToArray())
				{
					images[i].checksum += b;
				}
				images[i].tempFileStart = fileWrite.Position;
				fileWrite.Write(memStream.ToArray(), 0, (int)memStream.Length);
				images[i].tempFileEnd = fileWrite.Position;
				memStream.Dispose();
				images[i].UnparseImage();

				int nameLen = WzTool.GetWzObjectValueLength(images[i].name, 4);
				size += nameLen;
				int imgLen = images[i].size;
				size += WzTool.GetCompressedIntLength(imgLen);
				size += imgLen;
				size += WzTool.GetCompressedIntLength(images[i].Checksum);
				size += 4;
				offsetSize += nameLen;
				offsetSize += WzTool.GetCompressedIntLength(imgLen);
				offsetSize += WzTool.GetCompressedIntLength(images[i].Checksum);
				offsetSize += 4;
				imgWriter.Close();
			}
			fileWrite.Close();

			for (int i = 0; i < subDirs.Count; i++)
			{
				int nameLen = WzTool.GetWzObjectValueLength(subDirs[i].name, 3);
				size += nameLen;
				size += subDirs[i].GenerateDataFile(fileName);
				size += WzTool.GetCompressedIntLength(subDirs[i].size);
				size += WzTool.GetCompressedIntLength(subDirs[i].checksum);
				size += 4;
				offsetSize += nameLen;
				offsetSize += WzTool.GetCompressedIntLength(subDirs[i].size);
				offsetSize += WzTool.GetCompressedIntLength(subDirs[i].checksum);
				offsetSize += 4;
			}
			return size;
		}
		internal void SaveDirectory(WzBinaryWriter writer)
		{
			offset = (uint)writer.BaseStream.Position;
			int entryCount = subDirs.Count + images.Count;
			if (entryCount == 0)
			{
				BlockSize = 0;
				return;
			}
			writer.WriteCompressedInt(entryCount);
			foreach (WzImage img in images)
			{
				writer.WriteWzObjectValue(img.name, 4);
				writer.WriteCompressedInt(img.BlockSize);
				writer.WriteCompressedInt(img.Checksum);
				writer.WriteOffset(img.Offset);
			}
			foreach (WzDirectory dir in subDirs)
			{
				writer.WriteWzObjectValue(dir.name, 3);
				writer.WriteCompressedInt(dir.BlockSize);
				writer.WriteCompressedInt(dir.Checksum);
				writer.WriteOffset(dir.Offset);
			}
			foreach (WzDirectory dir in subDirs)
				if (dir.BlockSize > 0)
					dir.SaveDirectory(writer);
				else
					writer.Write((byte)0);
		}
		internal uint GetOffsets(uint curOffset)
		{
			offset = curOffset;
			curOffset += (uint)offsetSize;
			foreach (WzDirectory dir in subDirs)
			{
				curOffset = dir.GetOffsets(curOffset);
			}
			return curOffset;
		}
		internal uint GetImgOffsets(uint curOffset)
		{
			foreach (WzImage img in images)
			{
				img.Offset = curOffset;
				curOffset += (uint)img.BlockSize;
			}
			foreach (WzDirectory dir in subDirs)
			{
				curOffset = dir.GetImgOffsets(curOffset);
			}
			return curOffset;
		}
		internal void ExportXml(StreamWriter writer, bool oneFile, int level, bool isDirectory)
		{
			if (oneFile)
			{
				if (isDirectory)
				{
					writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.OpenNamedTag("WzDirectory", this.name, true));
				}
				foreach (WzDirectory subDir in WzDirectories)
				{
					subDir.ExportXml(writer, oneFile, level + 1, isDirectory);
				}
				foreach (WzImage subImg in WzImages)
				{
					subImg.ExportXml(writer, oneFile, level + 1);
				}
				if (isDirectory)
				{
					writer.WriteLine(XmlUtil.Indentation(level) + XmlUtil.CloseTag("WzDirectory"));
				}
			}
		}
		/// <summary>
		/// Parses the wz images
		/// </summary>
		public void ParseImages()
		{
			foreach (WzImage img in images)
			{
				if (reader.BaseStream.Position != img.Offset)
				{
					reader.BaseStream.Position = img.Offset;
				}
				img.ParseImage();
			}
			foreach (WzDirectory subdir in subDirs)
			{
				if (reader.BaseStream.Position != subdir.Offset)
				{
					reader.BaseStream.Position = subdir.Offset;
				}
				subdir.ParseImages();
			}
		}

		internal void SetHash(uint newHash)
		{
			this.hash = newHash;
			foreach (WzDirectory dir in subDirs)
				dir.SetHash(newHash);
		}

		/// <summary>
		/// Adds a WzImage to the list of wz images
		/// </summary>
		/// <param name="img">The WzImage to add</param>
		public void AddImage(WzImage img)
		{
			images.Add(img);
		}
		/// <summary>
		/// Adds a WzDirectory to the list of sub directories
		/// </summary>
		/// <param name="dir">The WzDirectory to add</param>
		public void AddDirectory(WzDirectory dir)
		{
			subDirs.Add(dir);
		}
		/// <summary>
		/// Clears the list of images
		/// </summary>
		public void ClearImages()
		{
			images.Clear();
		}
		/// <summary>
		/// Clears the list of sub directories
		/// </summary>
		public void ClearDirectories()
		{
			subDirs.Clear();
		}
		/// <summary>
		/// Gets an image in the list of images by it's name
		/// </summary>
		/// <param name="name">The name of the image</param>
		/// <returns>The wz image that has the specified name or null if none was found</returns>
		public WzImage GetImageByName(string name)
		{
			foreach (WzImage wzI in images)
				if (wzI.Name.ToLower() == name.ToLower())
					return wzI;
			return null;
		}
		/// <summary>
		/// Gets a sub directory in the list of directories by it's name
		/// </summary>
		/// <param name="name">The name of the directory</param>
		/// <returns>The wz directory that has the specified name or null if none was found</returns>
		public WzDirectory GetDirectoryByName(string name)
		{
			foreach (WzDirectory dir in subDirs)
				if (dir.Name.ToLower() == name.ToLower())
					return dir;
			return null;
		}
		/// <summary>
		/// Gets all child images of a WzDirectory
		/// </summary>
		/// <returns></returns>
		public WzImage[] GetChildImages()
		{
			List<WzImage> imgFiles = new List<WzImage>();
			imgFiles.AddRange(images);
			foreach (WzDirectory subDir in subDirs)
			{
				imgFiles.AddRange(subDir.images);
			}
			return imgFiles.ToArray();
		}
		/// <summary>
		/// Removes an image from the list with the specified name
		/// </summary>
		/// <param name="name">The name of the image to remove</param>
		public void RemoveImage(string name)
		{
			for (int i = 0; i < images.Count; i++)
				if (images[i].Name.ToLower() == name.ToLower())
					images.RemoveAt(i);
		}
		/// <summary>
		/// Removes a sub directory from the list with the specified name
		/// </summary>
		/// <param name="name">The name of the sub directory to remove</param>
		public void RemoveDirectory(string name)
		{
			for (int i = 0; i < subDirs.Count; i++)
				if (subDirs[i].Name.ToLower() == name.ToLower())
					subDirs.RemoveAt(i);
		}

	}
}