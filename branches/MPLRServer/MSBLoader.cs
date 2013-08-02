using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.IO;

namespace MPLRServer
{
    class MSBLoader
    {
        public delegate void DHandlePacket(MaplePacket pPacket);
        public DHandlePacket PacketHandler;

        public delegate void DOnDisconnect();
        public DOnDisconnect DisconnectHandler;

        public void Parse(string pFile, bool pMapleSharkFile)
        {
            using (FileStream stream = new FileStream(pFile, FileMode.Open, FileAccess.Read))
            {
                BinaryReader reader = new BinaryReader(stream);
                if (reader.ReadUInt16() != 0x2020)
                {
                    return;
                }

                string ip2 = reader.ReadString(); // Local Endpoint
                ushort port2 = reader.ReadUInt16(); // Port
                string ip = reader.ReadString(); // Remote Endpoint
                ushort port = reader.ReadUInt16(); // Port

                

                byte locale = reader.ReadByte(); // Locale
                ushort version = reader.ReadUInt16(); // Version
                Logger.WriteLine("Emulating socket connection with connection from V{0}", version / 100);


                MaplePacket p;
                if (pMapleSharkFile)
                {
                    if (ip.Contains(":"))
                    {
                        // Shit.
                        ip = "1.2.3.4";
                    }
                    p = new MaplePacket(MaplePacket.CommunicationType.ClientPacket, 0xEE00);
                    p.WriteBool(true);
                    p.WriteString(ip);
                    p.WriteUShort(port);
                    p.SwitchOver();
                    p.Reset(0);
                    PacketHandler(p);
                }

                while (stream.Position < stream.Length)
                {
                    long timestamp = reader.ReadInt64();
                    ushort size = reader.ReadUInt16();
                    ushort opcode = reader.ReadUInt16();
                    bool outbound = reader.ReadBoolean();

                    byte[] buffer = new byte[3 + size];
                    buffer[0] = (byte)(outbound ? MaplePacket.CommunicationType.ClientPacket : MaplePacket.CommunicationType.ServerPacket);
                    Buffer.BlockCopy(BitConverter.GetBytes(opcode), 0, buffer, 1, 2);
                    Buffer.BlockCopy(reader.ReadBytes(size), 0, buffer, 3, size);
                    if (opcode >= 0xEE00) continue; // Ignore!

                    MaplePacket packet = new MaplePacket(buffer);
                    try
                    {
                        if (PacketHandler != null)
                            PacketHandler(packet);
                    }
                    catch (Exception ex)
                    {
                        Logger.WriteLine("Internal Packet Handling Exception");
                        throw new Exception("Internal Packet Handling Exception", ex);
                    }
                }



                if (pMapleSharkFile)
                {
                    p = new MaplePacket(MaplePacket.CommunicationType.ClientPacket, 0xEE00);
                    p.WriteBool(false);
                    p.SwitchOver();
                    p.Reset(0);
                    PacketHandler(p);
                }

            }

            DisconnectHandler();
        }

        

    }
}
