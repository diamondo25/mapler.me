using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class ClientPacketHandlers
    {
        public const byte LatestLocale = 0x08;
        public const ushort LatestMajorVersion = 130; // Packet changes do not occur per minor version

        public static void HandleVersion(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte locale = pPacket.ReadByte();
            ushort version = pPacket.ReadUShort();
            ushort subversion = pPacket.ReadUShort();

            pConnection.Logger_WriteLine("Client data: {0} {1}.{2}", locale, version, subversion);
            pConnection.MapleVersion = version;
            pConnection.CharData = null; // Back to the LoginServer!!!

            if (locale != LatestLocale || version != LatestMajorVersion)
            {
                pConnection.Logger_WriteLine("MapleStory client of user is outdated/incorrect. Disconnect.");
                pConnection.Disconnect();
            }

        }
    }
}
