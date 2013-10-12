using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.IO;

namespace SimpleMapleServer
{
    public class Packet
    {
        private MemoryStream _memoryStream;
        private BinaryReader _binReader;
        private BinaryWriter _binWriter;

        public Packet(byte[] pData)
        {
            _memoryStream = new MemoryStream(pData);
            _binReader = new BinaryReader(_memoryStream);
        }

        public Packet()
        {
            _memoryStream = new MemoryStream();
            _binWriter = new BinaryWriter(_memoryStream);
        }

        public Packet(ushort pOpcode)
        {
            _memoryStream = new MemoryStream();
            _binWriter = new BinaryWriter(_memoryStream);
            WriteUShort(pOpcode);
        }

        private void LogPacketData(int pLength)
        {
            int curpos = Position + (_binWriter != null ? -pLength : 0);

            byte[] tmp2 = ToArray();
            string tmp = "";
            for (int i = 0; i < pLength; i++)
                tmp += string.Format("{0:X2} ", tmp2[curpos + i]);
            tmp = tmp.TrimEnd();

            //Console.PWrite(" [{0}]", tmp);

            _memoryStream.Position = curpos + (_binWriter != null ? pLength : 0);
        }

        public byte[] ToArray()
        {
            return _memoryStream.ToArray();
        }

        public int Length
        {
            get { return (int)_memoryStream.Length; }
        }

        public int Position
        {
            get { return (int)_memoryStream.Position; }
            set { _memoryStream.Position = value; }
        }

        public void Reset(int pPosition = 0)
        {
            _memoryStream.Position = pPosition;
        }

        public void Skip(int pAmount)
        {
            if (pAmount + _memoryStream.Position > Length)
                throw new Exception("!!! Cannot skip more bytes than there's inside the buffer!");
            //Console.PWrite("[SKIP]");
            LogPacketData(pAmount);
            _memoryStream.Position += pAmount;
        }

        public byte[] ReadLeftoverBytes()
        {
            //Console.PWrite("[LEFTOVER]");
            LogPacketData(Length - Position);
            return ReadBytes(Length - Position);
        }

        public override string ToString()
        {
            string ret = "";
            foreach (byte b in ToArray())
            {
                ret += string.Format("{0:X2} ", b);
            }
            return ret;
        }

        public void SwitchOver()
        {
            if (_binReader != null)
            {
                _binWriter = new BinaryWriter(_memoryStream);
                _binReader = null;
            }
            else
            {
                _binReader = new BinaryReader(_memoryStream);
                _binWriter = null;
            }
        }

        public void WriteBytes(byte[] val)
        {
            _binWriter.Write(val);
            LogPacketData(val.Length);
        }
        public void WriteByte(byte val)
        {
            _binWriter.Write(val);
            LogPacketData(1);
        }
        public void WriteSByte(sbyte val)
        {
            _binWriter.Write(val);
            LogPacketData(1);
        }
        public void WriteBool(bool val)
        {
            WriteByte(val == true ? (byte)1 : (byte)0);
            LogPacketData(1);
        }
        public void WriteShort(short val)
        {
            _binWriter.Write(val);
            LogPacketData(2);
        }
        public void WriteInt(int val)
        {
            _binWriter.Write(val);
            LogPacketData(4);
        }
        public void WriteLong(long val)
        {
            _binWriter.Write(val);
            LogPacketData(8);
        }
        public void WriteUShort(ushort val)
        {
            _binWriter.Write(val);
            LogPacketData(2);
        }
        public void WriteUInt(uint val)
        {
            _binWriter.Write(val);
            LogPacketData(4);
        }
        public void WriteULong(ulong val)
        {
            _binWriter.Write(val);
            LogPacketData(8);
        }
        public void WriteDouble(double val)
        {
            _binWriter.Write(val);
            LogPacketData(8);
        }
        public void WriteFloat(float val)
        {
            _binWriter.Write(val);
            LogPacketData(4);
        }
        public void WriteString(string val)
        {
            WriteShort((short)val.Length);
            LogPacketData(2);
            _binWriter.Write(val.ToCharArray());
            LogPacketData(val.Length);
        }
        public void WriteString(string val, int maxlen)
        {
            var i = 0; 
            for (; i < val.Length & i < maxlen; i++) 
                _binWriter.Write(val[i]); 
            for (; i < maxlen; i++) 
                WriteByte(0);

            LogPacketData(maxlen);
        }

        public void WriteHexString(string pInput)
        {
            pInput = pInput.Replace(" ", "");
            if (pInput.Length % 2 != 0) throw new Exception("Hex String is incorrect (size)");


            for (int i = 0; i < pInput.Length; i += 2)
            {
                WriteByte(byte.Parse(pInput.Substring(i, 2), System.Globalization.NumberStyles.HexNumber));
            }
            //Console.PWrite("[HEX INPUT]");
            LogPacketData(pInput.Length % 2);

        }

        public byte[] ReadBytes(int pLen)
        {
            LogPacketData(1);
            return _binReader.ReadBytes(pLen);
        }
        public bool ReadBool()
        {
            LogPacketData(1);
            return _binReader.ReadByte() != 0;
        }
        public byte ReadByte()
        {
            LogPacketData(1);
            return _binReader.ReadByte();
        }
        public sbyte ReadSByte()
        {
            LogPacketData(1);
            return _binReader.ReadSByte();
        }
        public short ReadShort()
        {
            LogPacketData(2);
            return _binReader.ReadInt16();
        }
        public int ReadInt()
        {
            LogPacketData(4);
            return _binReader.ReadInt32();
        }
        public long ReadLong()
        {
            LogPacketData(8);
            return _binReader.ReadInt64();
        }
        public ushort ReadUShort()
        {
            LogPacketData(2);
            return _binReader.ReadUInt16();
        }
        public uint ReadUInt()
        {
            LogPacketData(4);
            return _binReader.ReadUInt32();
        }
        public ulong ReadULong()
        {
            LogPacketData(8);
            return _binReader.ReadUInt64();
        }
        public double ReadDouble()
        {
            LogPacketData(8);
            return _binReader.ReadDouble();
        }
        public float ReadFloat()
        {
            LogPacketData(4);
            return _binReader.ReadSingle();
        }
        public string ReadString(short pLen = -1)
        {
            if (pLen == -1) LogPacketData(2);
            
            short len = pLen == -1 ? _binReader.ReadInt16() : pLen;
            string ret = "";
            bool end = false;
            
            LogPacketData(len);
            
            for (int i = 0; i < len; i++)
            {
                char character = _binReader.ReadChar();
                if (end || character == '\0')
                {
                    end = true;
                    // Need to fill up!
                }
                else
                {
                    ret += character;
                }
            }

            return ret;

        }

        public void SetBytes(int pPosition, byte[] val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetByte(int pPosition, byte val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetSByte(int pPosition, sbyte val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetBool(int pPosition, bool val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); WriteByte(val == true ? (byte)1 : (byte)0); Reset(tmp); }
        public void SetShort(int pPosition, short val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetInt(int pPosition, int val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetLong(int pPosition, long val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetUShort(int pPosition, ushort val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetUInt(int pPosition, uint val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
        public void SetULong(int pPosition, ulong val) { int tmp = (int)_memoryStream.Position; Reset(pPosition); _binWriter.Write(val); Reset(tmp); }
    }
}
