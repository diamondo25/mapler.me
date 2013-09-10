using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class ServerPacketHandlers : IServerPacketHandlers
    {
        private static ServerPacketHandlers _instance;
        public static ServerPacketHandlers Instance { get { return _instance = _instance ?? new ServerPacketHandlers(); } }

        public override void HandleLogin(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte error = pPacket.ReadByte();
            if (error != 0)
            {
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in?");
                }
                return;
            }

            int userid = pPacket.ReadInt();
            pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            pPacket.ReadByte(); // Admin? Has bitflag 5
            string username = pPacket.ReadString(); // Username
            pPacket.ReadByte();
            pPacket.ReadByte();
            DateTime creationtime = DateTime.FromFileTime(pPacket.ReadLong()); // Incorrect :/. Nowhere to be found

            if (pPacket.ReadBool() == false)
                pPacket.ReadString(); // Username, with astriks as protection: 'd**mondo2*'

            pPacket.ReadString(); // wat.
            if (pPacket.ReadBool())
                pPacket.ReadBytes(15); // Every job that can be chosen

            pPacket.ReadInt(); // YYYYMMDDHH, as in character info -.-?


            ParseLogin(pConnection, userid, username, creationtime);
        }
    }
}
